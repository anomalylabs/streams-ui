<?php

namespace Streams\Ui\Form\Component\Action;

use Streams\Ui\Support\Builder;
use Streams\Ui\Form\Component\Action\Action;
use Streams\Ui\Support\Workflows\BuildComponent;

/**
 * Class ActionBuilder
 *
 * @link    http://pyrocms.com/
 * @author  PyroCMS, Inc. <support@pyrocms.com>
 * @author  Ryan Thompson <ryan@pyrocms.com>
 */
class ActionBuilder extends Builder
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
            'parent' => null,

            'assets' => [],

            'component' => 'action',

            'action' => Action::class,

            'workflows' => [
                'build' => BuildComponent::class,
            ],
        ], $attributes));
    }
}
