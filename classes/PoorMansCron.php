<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FileCredit;


use Contao\Config;

class PoorMansCron
{
    public function minutely()
    {
        $this->indexCredits('minutely');
    }

    public function hourly()
    {
        $this->indexCredits('hourly');
    }

    public function daily()
    {
        $this->indexCredits('daily');
    }

    public function weekly()
    {
        $this->indexCredits('weekly');
    }

    public function monthly()
    {
        $this->indexCredits('monthly');
    }

    /**
     * Index file credits based on poor man cron jobs.
     *
     * @param string $interval
     */
    private function indexCredits($interval)
    {
        if(Config::get('fileCreditsDisablePoorMansCron')){
            return;
        }

        $manager = new IndexManager();
        $manager->run();
    }
}
