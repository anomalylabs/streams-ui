<?php

namespace Streams\Ui\Form\Component\Field\Workflows\Fields;

use Streams\Ui\Form\FormBuilder;

/**
 * Class PopulateFields
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class PopulateFields
{

    /**
     * Handle the step.
     *
     * @param FormBuilder $builder
     */
    public function handle(FormBuilder $builder)
    {
        if (!$entry = $builder->instance->entry) {
            return;
        }

        $builder->instance->fields->each(function($field) use ($entry) {
            $field->value = $entry->{$field->handle} ?? null;
        });
    }
}
