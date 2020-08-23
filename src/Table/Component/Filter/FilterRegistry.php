<?php namespace Anomaly\Streams\Ui\Table\Component\Filter;

use Illuminate\Support\Arr;
use Anomaly\Streams\Ui\Table\Component\Filter\Type\FieldFilter;
use Anomaly\Streams\Ui\Table\Component\Filter\Type\InputFilter;
use Anomaly\Streams\Ui\Table\Component\Filter\Type\SearchFilter;
use Anomaly\Streams\Ui\Table\Component\Filter\Type\SelectFilter;
use Anomaly\Streams\Ui\Table\Component\Filter\Type\DatetimeFilter;

/**
 * Class FilterRegistry
 *
 * @link    http://pyrocms.com/
 * @author  PyroCMS, Inc. <support@pyrocms.com>
 * @author  Ryan Thompson <ryan@pyrocms.com>
 */
class FilterRegistry
{

    /**
     * Available filters.
     *
     * @var array
     */
    protected $filters = [
        'search'     => [
            'handle'        => 'search',
            'filter'      => SearchFilter::class,
            'placeholder' => 'ui::message.search',
        ],
    ];

    /**
     * Get a filter.
     *
     * @param  $filter
     * @return array
     */
    public function get($filter)
    {
        return Arr::get($this->filters, $filter);
    }

    /**
     * Register a filter.
     *
     * @param        $filter
     * @param  array $parameters
     * @return $this
     */
    public function register($filter, array $parameters)
    {
        Arr::set($this->filters, $filter, $parameters);

        return $this;
    }
}
