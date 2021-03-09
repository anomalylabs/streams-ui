<?php

namespace Streams\Ui\Support;

use Illuminate\Support\Arr;
use Streams\Core\Support\Workflow;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Traits\Macroable;
use Streams\Core\Support\Facades\Streams;
use Streams\Core\Support\Traits\Prototype;
use Streams\Core\Support\Traits\FiresCallbacks;
use Streams\Ui\ControlPanel\ControlPanelBuilder;
use Illuminate\Support\Facades\View as ViewFacade;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

/**
 * Class Builder
 * 
 * Builders build component (UI objects) instances.
 * 
 * Intended to be instantiated like:
 * 
 * $builder = new FormBuilder($attributes);
 * 
 * Available Methods
 * 
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class Builder
{
    use Macroable;
    use Prototype;
    use FiresCallbacks;

    public function build(): Component
    {
        if ($this->instance instanceof Component) {
            return $this->instance;
        }

        $this->fire('ready', ['builder' => $this]);

        $workflow = $this->workflow('build');

        $this->fire('building', [
            'builder' => $this,
            'workflow' => $workflow
        ]);

        $workflow->process([
            'builder' => $this,
            'workflow' => $workflow
        ]);

        $this->fire('built', ['builder' => $this]);

        return $this->instance;
    }

    public function make()
    {
        if (is_string($this->stream)) {
            $this->stream = Streams::make($this->stream);
        }

        if (is_object($this->{$this->component})) {
            return $this->{$this->component};
        }

        $abstract = $this->getPrototypeAttribute($this->component);

        $this->{$this->component} = new $abstract;

        $this->{$this->component}->stream = $this->stream;
        $this->{$this->component}->handle = $this->handle;

        //$this->{$this->component}->repository = $this->repository();

        return $this->{$this->component};
    }

    public function response(): HttpFoundationResponse
    {
        $this->build();

        if ($this->response) {
            return $this->response;
        }

        if (Request::method() == 'POST') {
            $this->instance->post();
        }
        
        if ($this->instance->response) {
            return $this->instance->response;
        }

        if (!$this->async && Request::ajax()) {
            return Response::view($this->render());
        }

        if ($this->async == true && Request::ajax()) {
            return Response::json($this);
        }

        if (ViewFacade::shared('cp') || Arr::get($this->options, 'cp_enabled') === true) {

            // @todo this needs work
            // control panel builder
            if (!ViewFacade::shared('cp')) {
                ViewFacade::share('cp', (new ControlPanelBuilder())->build());
            }
            
            return Response::view('ui::cp', ['content' => $this->render()]);
        }

        return Response::view('ui::ui', ['content' => $this->render()]);
    }

    public function render()
    {
        $this->build();

        return $this->instance->render();
    }

    public function json()
    {
        $this->build();

        return Response::json($this->instance->toJson());
    }


    protected function workflow($name): Workflow
    {
        $workflow = Arr::get($this->workflows, $name, $name);

        if (!class_exists($workflow)) {
            $workflow = $this->workflow;
        }

        if (!$workflow) {
            return (new Workflow($this->steps ?: []))
                ->setPrototypeAttribute('name', $name)
                ->passThrough($this);
        }

        return (new $workflow)
            ->setPrototypeAttribute('name', $name)
            ->passThrough($this);
    }

    protected function loadInstanceWith($key, $input, $abstract)
    {
        return array_map(function ($attributes) use ($key, $abstract) {

            $abstract = Arr::pull($attributes, 'abstract', $abstract);

            $this->instance->{$key}->put($attributes['handle'], new $abstract($attributes));
        }, $input);
    }

    public function __get($key)
    {
        if ($key == 'instance') {
            $key  = $this->__prototype['attributes']['component'];
        }

        return $this->getPrototypeAttribute($key);
    }

    public function __set($key, $value)
    {
        if ($key == 'instance') {
            $key  = $this->__prototype['attributes']['component'];
        }

        $this->setPrototypeAttribute($key, $value);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->instance, $method)) {
            return call_user_func_array([$this->instance, $method], $parameters);
        }

        throw new \Exception("Method [{$method}] does not exist.");
    }
}
