<?php

namespace Streams\Ui\Input;

use Streams\Ui\Support\Component;

class Input extends Component
{

    /**
     * Initialize the prototype.
     *
     * @param array $attributes
     * @return $this
     */
    protected function initializePrototype(array $attributes)
    {
        return parent::initializePrototype(array_merge([
            'template' => 'ui::input/input',
            'component' => 'input',
            'classes' => [],
            'type' => 'text',
            'placeholder' => null,
            'field' => null,
        ], $attributes));
    }

    public function attributes(array $attributes = [])
    {
        return parent::attributes(array_merge([
            'id' => $this->id ?: $this->name() . '-input',
            'name' => $this->name(),
            'placeholder' => $this->placeholder,
            'required' => $this->field->hasRule('required'),
            'readonly' => $this->field->readonly ? 'readonly' : null,
            'disabled' => $this->field->disabled ? 'disabled' : null,
            'pattern' => trim($this->field->pattern ?: $this->field->getRuleParameter('regex'), "//"),
            'type' => $this->type,
            'value' => $this->value,
        ], $attributes));
    }

    public function label()
    {
        return $this->label ?: $this->field->name();
    }

    public function name()
    {
        return $this->name ?: ($this->prefix . $this->field->handle);
    }
}
