<?php

/**
 * @copyright (C) 2011, 2014 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Michiel Vancoillie <michiel@okfn.be>
 */
namespace Tdt\Input\Ui;

class UiController extends \Controller
{

    /**
     * Request handeling
     */
    public static function handle($uri)
    {
        switch ($uri) {
            case 'jobs':
                return \View::make('input::ui.jobs.list')
                            ->with('title', 'Jobs management | The Datatank')
                            ->with('jobs', 'test');

                break;
        }

        return false;
    }

    /**
     * Define menu items
     */
    public static function menu()
    {
        return array(
            array(
                'title' => 'Jobs',
                'slug' => 'jobs',
                'permission' => null,
                'icon' => 'fa-wrench',
                'priority' => 40
                ),
        );
    }
}
