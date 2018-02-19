<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FileCredit;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class IndexManager
{
    public function run()
    {
        @ini_set('max_execution_time', 0);

        // do not verify ssl certificates
        $client = new Client(['verify' => false]);

        $requests = function ($pages) use ($client) {

            if (null === $pages) {
                return;
            }

            foreach ($pages as $url) {
                yield new Request('GET', $url);
            }
        };

        $pages = FileCredit::findAllFileCreditPages();

        $pool = new Pool($client, $requests($pages), [
            'concurrency' => 5,
            'fulfilled'   => function ($response, $index) {
            },
            'rejected'    => function ($reason, $index) {
                if ($reason instanceof RequestException) {
                    \System::log('Filecredit-index: unable to index files for url: ' . $reason->getRequest()->getUri() . ' with message: ' . $reason->getMessage() . "\n", __METHOD__, TL_ERROR);
                }
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }
}