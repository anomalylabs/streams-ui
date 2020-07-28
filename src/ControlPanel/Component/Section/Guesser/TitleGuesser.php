<?php

namespace Anomaly\Streams\Ui\ControlPanel\Component\Section\Guesser;

use Anomaly\Streams\Platform\Addon\Module\ModuleCollection;
use Anomaly\Streams\Platform\Ui\ControlPanel\ControlPanelBuilder;

/**
 * Class TitleGuesser
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class TitleGuesser
{

    /**
     * Guess the sections title.
     *
     * @param ControlPanelBuilder $builder
     */
    public static function guess(ControlPanelBuilder $builder)
    {
        if (!$module = app('module.collection')->active()) {
            return;
        }

        $sections = $builder->getSections();

        foreach ($sections as &$section) {

            // If title is set then skip it.
            if (isset($section['title'])) {
                continue;
            }

            $title = $module->getNamespace('section.' . $section['slug'] . '.title');

            if (!isset($section['title']) && trans()->has($title)) {
                $section['title'] = $title;
            }

            $title = $module->getNamespace('addon.section.' . $section['slug']);

            if (!isset($section['title']) && trans()->has($title)) {
                $section['title'] = $title;
            }

            if (!isset($section['title']) && config('streams.system.lazy_translations')) {
                $section['title'] = ucwords(humanize($section['slug']));
            }

            if (!isset($section['title'])) {
                $section['title'] = $title;
            }
        }

        $builder->setSections($sections);
    }
}