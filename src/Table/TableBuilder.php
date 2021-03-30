<?php

namespace Streams\Ui\Table;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Streams\Core\Repository\Contract\RepositoryInterface;
use Streams\Core\Stream\Stream;
use Streams\Ui\Button\Button;
use Streams\Ui\Support\Builder;
use Streams\Ui\Support\Normalizer;
use Streams\Ui\Support\Value;
use Streams\Ui\Table\Component\Action\Action;
use Streams\Ui\Table\Component\Action\ActionRegistry;
use Streams\Ui\Table\Component\Button\ButtonRegistry;
use Streams\Ui\Table\Component\Column\Column;
use Streams\Ui\Table\Component\Filter\Filter;
use Streams\Ui\Table\Component\Filter\FilterRegistry;
use Streams\Ui\Table\Component\Row\Row;
use Streams\Ui\Table\Component\View\View;
use Streams\Ui\Table\Component\View\ViewHandler;
use Streams\Ui\Table\Component\View\ViewRegistry;

class TableBuilder extends Builder
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
            'stream' => null,
            'entries' => null,
            'repository' => null,

            'views' => [],
            'assets' => [],
            'filters' => [],
            'columns' => [],
            'headers' => [],
            'buttons' => [],
            'actions' => [],

            'options' => [
                'cp_enabled' => false,
            ],

            'component' => 'table',
            'table' => Table::class,

            'steps' => [
                'make_table' => [$this, 'make'],

                'make_views' => [$this, 'makeViews'],
                'detect_view' => [$this, 'detectView'],
                'apply_view' => [$this, 'applyView'],

                'authorize_table' => [$this, 'authorizeTable'],

                'make_filters' => [$this, 'makeFilters'],
                'detect_filters' => [$this, 'detectFilters'],
                'query_entries' => [$this, 'queryEntries'],

                'make_actions' => [$this, 'makeActions'],
                'make_buttons' => [$this, 'makeButtons'],
                'make_columns' => [$this, 'makeColumns'],
                'make_rows' => [$this, 'makeRows'],
            ],
        ], $attributes));
    }

    /**
     * Return the repository.
     *
     * @return RepositoryInterface
     */
    public function repository()
    {
        if ($this->repository instanceof RepositoryInterface) {
            return $this->repository;
        }

        /**
         * Default to configured.
         */
        if ($this->repository) {
            return $this->repository = App::make($this->repository, [
                'builder' => $this,
            ]);
        }

        /**
         * Fallback for Streams.
         */
        if (!$this->repository && $this->stream instanceof Stream) {
            return $this->repository = $this->stream->repository();
        }

        return null;
    }

    public function makeViews()
    {
        $this->make();

        if ($this->instance->views->isNotEmpty()) {
            return $this->instance->views;
        }

        $views = $this->views;

        $views = Normalizer::fillWithKey($views, 'handle');
        $views = Normalizer::fillWithKey($views, 'view');
        $views = Normalizer::attributes($views);

        $registry = app(ViewRegistry::class);

        foreach ($views as &$attributes) {
            if ($registered = $registry->get(Arr::pull($attributes, 'view'))) {
                $attributes = array_replace_recursive($registered, $attributes);
            }
        }

        $this->loadInstanceWith('views', $views, View::class);

        $this->views = $views;

        return $this->instance->views;
    }

    public function detectView()
    {
        if ($this->instance->views->active()) {
            return;
        }

        if ($view = $this->instance->views->findByHandle(Request::get($this->instance->options->get('prefix') . 'view'))) {
            $view->active = true;
        }

        if (!$view && $view = $this->instance->views->first()) {
            $view->active = true;
        }
    }

    public function applyView(ViewHandler $handler)
    {
        if (!$active = $this->instance->views->active()) {
            return;
        }

        // Nothing to do.
        if (!$active) {
            return;
        }

        if ($active->filters) {
            $this->filters = $active->filters;
        }

        if ($active->columns) {
            $this->columns = $active->columns;
        }

        if ($active->buttons) {
            $this->buttons = $active->buttons;
        }

        if ($active->actions) {
            $this->actions = $active->actions;
        }

        if ($active->options) {
            $this->options = $active->options;
        }

        if ($active->entries) {
            $this->entries = $active->entries;
        }

        $handler->handle($this, $active);
    }

    public function authorizeTable(TableAuthorizer $authorizer)
    {
        $authorizer->authorize($this);
    }

    public function makeFilters()
    {
        $this->make();

        if ($this->instance->filters->isNotEmpty()) {
            return $this->instance->filters;
        }

        $filters = $this->filters;
        $stream = $this->stream;

        $filters = Normalizer::fillWithKey($filters, 'handle');
        $filters = Normalizer::fillWithKey($filters, 'filter');
        $filters = Normalizer::fillWithKey($filters, 'field');
        $filters = Normalizer::fillWithKey($filters, 'name');
        $filters = Normalizer::attributes($filters);

        $registry = app(FilterRegistry::class);

        foreach ($filters as &$attributes) {

            if ($registered = $registry->get(Arr::pull($attributes, 'filter'))) {
                $attributes = array_replace_recursive($registered, $attributes);
            }
        }

        if ($stream) {

            foreach ($filters as &$filter) {

                if (!isset($filter['field'])) {
                    continue;
                }

                $filter['field'] = $stream->fields->{$filter['field']};
            }
        }

        $this->loadInstanceWith('filters', $filters, Filter::class);

        if ($first = $this->instance->filters->first()) {
            $first->setPrototypeAttribute('attributes.data-keymap', 'f');
        }

        $this->filters = $filters;

        return $this->instance->filters;
    }

    public function detectFilters()
    {
        $this->instance->filters->each(function ($filter) {
            $filter->active = Request::has($this->instance->prefix(/*'filter_' .*/ $filter->handle));
        });
    }

    public function queryEntries()
    {
        if (!$this->repository()) {
            return;
        }

        /**
         * Start Query
         */
        $this->criteria = $this->repository()->newCriteria();

        /**
         * Filter Query
         */
        foreach ($this->instance->filters->active() as $filter) {

            /*
            * If the handler is a callable string or Closure
            * then call it using the IoC container.
            */
            $query = $filter->query ?: [$filter, 'query'];

            App::call($query, [
                'builder' => $this,
                'filter' => $filter,
            ], 'handle');
        }

        /**
         * Order query
         */
        if ($name = $this->request('order_by')) {
            $this->criteria->orderBy($name, $this->request('sort', 'asc'));
        }

        if (!$name && $this->instance->options->has('order_by')) {
            foreach ($this->instance->options->get('order_by') as $name => $sort) {
                $this->criteria->orderBy($name, $sort);
            }
        }

        /**
         * Finish query
         */
        $this->instance->options['total_results'] = $this->criteria->count();

        /**
         * @todo This terminology and parameters need reviewed/revisited.
         */
        if ($this->instance->options->get('paginate', true)) {

            $this->instance->pagination = $this->criteria->paginate([
                'page_name' => $this->instance->options->get('prefix') . 'page',
                'limit_name' => $this->instance->options->get('limit') . 'limit',
                'total_results' => $this->instance->options->get('total_results'),
            ]);

            $this->instance->entries = $this->instance->pagination->getCollection();
        }
    }

    public function makeActions()
    {
        $this->make();

        if ($this->instance->actions->isNotEmpty()) {
            return $this->instance->actions;
        }

        $actions = $this->actions;

        $actions = Normalizer::normalize($actions, 'handle');
        $actions = Normalizer::fillWithKey($actions, 'handle');
        $actions = Normalizer::fillWithAttribute($actions, 'action', 'handle');

        $registry = app(ActionRegistry::class);

        foreach ($actions as &$attributes) {

            if ($registered = $registry->get(Arr::pull($attributes, 'action'))) {
                $attributes = array_replace_recursive($registered, $attributes);
            }

            if (!isset($attributes['text'])) {
                $attributes['text'] = Str::title(Str::humanize($attributes['handle']));
            }
        }

        $actions = Normalizer::attributes($actions);
        $actions = Normalizer::fillWithAttribute($actions, 'value', 'handle');
        $actions = Normalizer::fillWithValue($actions, 'attributes.type', 'submit');

        $this->loadInstanceWith('actions', $actions, Action::class);

        $this->actions = $actions;

        return $this->instance->actions;
    }

    public function makeButtons()
    {
        $this->make();

        if ($this->instance->buttons->isNotEmpty()) {
            return $this->instance->buttons;
        }

        $stream = $this->stream;
        $buttons = $this->buttons;

        $buttons = Normalizer::normalize($buttons);
        $buttons = Normalizer::fillWithKey($buttons, 'handle');
        $buttons = Normalizer::fillWithAttribute($buttons, 'button', 'handle');

        $registry = app(ButtonRegistry::class);

        foreach ($buttons as &$attributes) {

            if ($registered = $registry->get(Arr::pull($attributes, 'button'))) {
                $attributes = array_replace_recursive($registered, $attributes);
            }
        }

        $buttons = Normalizer::attributes($buttons);
        $buttons = Normalizer::dropdown($buttons);

        foreach ($buttons as &$button) {

            if (
                isset($button['handle'])
                && !isset($button['attributes']['href'])
            ) {
                $button['attributes']['href'] = URL::current() . '/{entry.id}/' . $button['handle'];
            }
        }

        $this->loadInstanceWith('buttons', $buttons, Button::class);

        $this->buttons = $buttons;

        return $this->instance->buttons;
    }

    public function makeColumns()
    {
        $this->make();

        if ($this->instance->columns->isNotEmpty()) {
            return $this->instance->columns;
        }

        $stream = $this->stream;
        $columns = $this->columns;

        if (!$columns && $this->stream) {
            $columns = $this->stream->fields->keys()->take(3)->all();
        }

        $columns = Normalizer::normalize($columns);
        $columns = Normalizer::fillWithKey($columns, 'handle');
        $columns = Normalizer::fillWithAttribute($columns, 'value', 'handle');

        $columns = Normalizer::attributes($columns);

        foreach ($columns as &$attributes) {

            $handle = Arr::get($attributes, 'handle');
            $field = Arr::get($attributes, 'field', $handle);

            $field = $stream->fields->get($field);

            if (!isset($attributes['field']) && $field) {
                $attributes['field'] = $field->handle;
            }

            if (!array_key_exists('heading', $attributes) && $field) {
                $attributes['heading'] = $field->name();
            }

            if (!array_key_exists('sortable', $attributes) && $field) {
                $attributes['sortable'] = true;
            }

            if ($field && Request::get($this->instance->prefix('order_by')) == $field->handle) {
                $attributes['direction'] = Request::get('sort');
            }
        }

        $this->loadInstanceWith('columns', $columns, Column::class);

        $this->columns = $columns;

        return $this->instance->actions;
    }

    public function makeRows()
    {
        $this->make();

        if ($this->instance->rows->isNotEmpty()) {
            return $this->instance->rows;
        }

        $this->rows = $rows = $this->instance->entries->map(function ($entry) {
            return [
                'handle' => $entry->id,
                'key' => $entry->id,

                'entry' => $entry,
                'table' => $this->instance,

                'columns' => $this->instance->columns->map(function ($column) {
                    return clone ($column);
                }),
                'buttons' => $this->instance->buttons->map(function ($button) {
                    return clone ($button);
                }),
            ];
        })->all();

        $rows = Normalizer::attributes($rows);

        $this->loadInstanceWith('rows', $rows, Row::class);

        $this->instance->rows->each(function ($row) {

            // Load Columns
            foreach ($this->instance->columns as $key => $column) {

                $clone = clone ($column);

                $clone->value = Value::make($clone->value, $row->entry);

                $row->columns->put($key, $clone);
            }

            // Load Buttons
            foreach ($this->instance->buttons as $button) {

                $clone = clone ($button);

                $clone->setPrototypeAttributes(Arr::parse($button->getPrototypeAttributes(), [
                    'entry' => $row->entry,
                    'stream' => $this->stream,
                ]));

                $row->buttons->put($clone->handle, $clone);
            }
        });

        $this->rows = $rows;

        return $this->instance->rows;
    }
}
