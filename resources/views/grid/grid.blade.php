{{-- {{ asset_add("scripts.js", "ui::js/grid/grid.js") }}

{% block content %}

    <div class="container-fluid">

        {% if not grid.items.empty() %}
            <ul class="grid grid--sortable sortable row">
                {% for item in grid.items %}
                    <li data-id="{{ item.id }}">
                        <div class="col-lg-{{ grid.options.columns ?: 4 }}">
                            <div class="{{ item.class }}">
                                <a href="{{ item.href }}">
                                    {{ item.icon ? icon(item.icon, 'icon') }}
                                    {{ item.value }}
                                </a>
                            </div>
                        </div>
                    </li>
                {% endfor %}
            </ul>
        {% else %}
            <div class="panel">
                <div class="panel-body">
                    {{ trans(grid.options.get('no_results_message', 'ui::message.no_results')) }}
                </div>
            </div>
        {% endif %}

    </div>

{% endblock %} --}}
Yo dawg.. I'm a grid!
