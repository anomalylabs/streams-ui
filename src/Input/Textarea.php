<?php

namespace Streams\Ui\Input;

use Illuminate\Support\Arr;

class Textarea extends Input
{

    /**
     * Initialize the prototype.
     *
     * @param array $attributes
     * @return $this
     */
    protected function initializePrototypeAttributes(array $attributes)
    {
        return parent::initializePrototypeAttributes(array_merge([
            'template' => 'ui::input/textarea',
            'type' => null,
            'config' => [
                'rows' => 10,
            ]
        ], $attributes));
    }

    public function load($value)
    {
        if (is_array($value)) {
            $value = implode("\n", $value);
        }

        return parent::load($value);
    }

    public function value()
    {
        $value = parent::value();

        $type = $this->field->type();

        if (
            is_a($type, \Streams\Core\Field\Type\Arr::class)
            || is_subclass_of($type, \Streams\Core\Field\Type\Arr::class)
            ) {
            return explode("\n", $value);
        }

        return $value;
    }

    /**
     * Return the HTML attributes array.
     *
     * @param array $attributes
     * @return array
     */
    public function attributes(array $attributes = [])
    {
        return parent::attributes(array_merge([
            'rows' => Arr::get($this->field->config, 'rows', 10)
        ], $attributes));
    }
}
