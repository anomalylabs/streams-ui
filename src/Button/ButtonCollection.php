<?php

namespace Anomaly\Streams\Ui\Button;

use Illuminate\View\View;
use Illuminate\Support\Collection;
use Anomaly\Streams\Ui\Button\Button;

/**
 * Class ButtonCollection
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class ButtonCollection extends Collection
{

    /**
     * Render the buttons.
     * 
     * @return View
     */
    public function render()
    {
        return view('ui::ui/buttons/buttons', ['buttons' => $this]);
    }

    /**
     * Render the actions.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }
}
