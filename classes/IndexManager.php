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
    public function run()
    {
        @ini_set('max_execution_time', 0);

        $pages = FileCredit::findAllFileCreditPages();

        $this->crawl($pages);

        System::log('Successfully indexed file credits on ' . count($pages) . ' pages.', __METHOD__, TL_CRON);
    }

    /**
     * Crawl given pages
     * @param array $pages
     * @param bool $index
     */
    protected function crawl(array $pages = [])
    {
        // do not verify ssl certificates
        $client = new Client(['headers' => ['User-Agent' => 'Contao filecredits crawler'], 'verify' => false]);

        $requests = function ($pages) use ($client) {

            if (null === $pages) {
                return;
            }

            foreach ($pages as $url) {
                yield new Request('GET', $url);
            }
        };

        $pool = new Pool($client, $requests($pages), [
            'concurrency' => 5,
            'fulfilled'   => function (Response $response, $index) use ($pages, $client) {
                if ($response->getStatusCode() != 200) {
                    // deindex file credits if page returned an error
                    $request = new Request('GET', Url::addQueryString(FileCredit::REQUEST_DEINDEX_PARAM . '=1', $pages[$index]));
                    $client->send($request);
                }
            },
            'rejected'    => function ($reason, $index) use ($pages, $client){
                // deindex file credits if page returned an error
                $request = new Request('GET', Url::addQueryString(FileCredit::REQUEST_DEINDEX_PARAM . '=1', $pages[$index]));
                $client->send($request);
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }
}