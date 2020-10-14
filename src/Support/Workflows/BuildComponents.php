<?php

namespace Streams\Ui\Support\Workflows;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Streams\Ui\Support\Builder;

/**
 * Class BuildComponents
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class BuildComponents
{

    /**
     * Hand the step.
     *
     * @param Builder $builder
     * @param string $component
     */
    public function handle(Builder $builder, $component = null)
    {
        $singular = Str::singular($component);

        $parent = $builder;

        $parentSegment = Str::studly($builder->component);
        $componentSegment = Str::studly($singular);

        $fallback = "Streams\Ui\\{$parentSegment}\Component\\{$componentSegment}\\{$componentSegment}Builder";

        $parent->instance->{$component} = [];

        foreach ($builder->{$component} as $parameters) {

            $parameters[$parent->component] = $parent;
            $parameters['stream'] = Arr::get($parameters, 'stream', $parent->stream);

            $builder = Arr::pull($parameters, 'builder', Arr::get($parent->builders, $component, $fallback));

            $instance = (new $builder($parameters))->build()->instance;

            $parent->instance->{$component}->put($instance->handle, $instance);
        }
    }
}
