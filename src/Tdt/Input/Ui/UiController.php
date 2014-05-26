<?php

/**
 * @copyright (C) 2011, 2014 by OKFN Belgium vzw/asbl
 * @license AGPLv3
 * @author Michiel Vancoillie <michiel@okfn.be>
 */
namespace Tdt\Input\Ui;

use Tdt\Input\Controllers\InputController;

class UiController extends \Controller
{

    /**
     * Request handeling
     */
    public static function handle($uri)
    {
        switch ($uri) {
            case 'jobs':

                // Get list of jobs
                $jobs = \Job::all();

                return \View::make('input::ui.jobs.list')
                            ->with('title', 'Jobs management |co The Datatank')
                            ->with('jobs', $jobs);

                break;
            case (preg_match('/^jobs\/delete/i', $uri) ? true : false):

                // Delete a job

                // Get the id
                $id = str_replace('jobs/delete/', '', $uri);

                if (is_numeric($id)) {
                    $job = \Job::find($id);
                    if (!empty($job)) {
                        $job->delete();
                    }

                    return \Redirect::to('api/admin/jobs');
                } else {
                    return false;
                }

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
                'permission' => 'tdt.input.view',
                'icon' => 'fa-wrench',
                'priority' => 40
                ),
        );
    }
}
