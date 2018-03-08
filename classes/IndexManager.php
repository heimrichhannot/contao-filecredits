<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FileCredit;


use Contao\System;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Haste\Util\Url;

class IndexManager
{
    /**
     * Pages with content (200)
     * @var array
     */
    protected $goodPages = [];

    public function run()
    {
        @ini_set('max_execution_time', 0);

        $pages = FileCredit::findAllFileCreditPages();

        $this->goodPages = $pages;

        // collect $this->indexPages
        $this->crawl($pages);

        // crawl non error (404, 500) pages
        $this->crawl($this->goodPages, true);

        System::log('Successfully indexed file credits on ' . count($pages) . ' pages.', __METHOD__, TL_CRON);
    }

    /**
     * Crawl given pages
     * @param array $pages
     */
    protected function crawl(array $pages = [], bool $index = false)
    {
        // do not verify ssl certificates
        $client = new Client(['verify' => false]);

        $requests = function ($pages) use ($client, $index) {

            if (null === $pages) {
                return;
            }

            foreach ($pages as $url) {
                yield new Request('GET', $index ? $url : Url::removeQueryString([FileCredit::REQUEST_INDEX_PARAM], $url));
            }
        };

        $pool = new Pool($client, $requests($pages), [
            'concurrency' => 5,
            'fulfilled'   => function (Response $response, $index) {
                if ($response->getStatusCode() != 200) {
                    unset($this->goodPages[$index]);
                }
            },
            'rejected'    => function ($reason, $index) {
                unset($this->goodPages[$index]);
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }
}