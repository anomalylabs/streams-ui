<?php

namespace Streams\Ui\Table\Component\View;

use Streams\Ui\Support\Component;

/**
 * Class View
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class View extends Component
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
            'handle' => null,
            'text' => null,
            'icon' => null,
            'label' => null,
            'query' => null,
            'prefix' => null,
            'actions' => null,
            'buttons' => null,
            'columns' => null,
            'entries' => null,
            'filters' => null,
            'handler' => null,
            'options' => null,
            'active' => false,
            'attributes' => [],
            'context' => 'danger',

            'query' => ViewQuery::class,
            'handler' => ViewHandler::class,
        ], $attributes));
    }
}
