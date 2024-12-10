<?php

declare(strict_types=1);

namespace Mirakl\Mci\Model\Image;

use GuzzleHttp;
use Mirakl\Mci\Helper\Config as MciConfigHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Downloader
{
    public const HEADER_MIRAKL_IMAGE_URL = 'X-Mirakl-Image-Url';

    /**
     * @var MciConfigHelper
     */
    protected $mciConfigHelper;

    /**
     * @param MciConfigHelper $mciConfigHelper
     */
    public function __construct(MciConfigHelper $mciConfigHelper)
    {
        $this->mciConfigHelper = $mciConfigHelper;
    }

    /**
     * @param string $url
     * @return resource|false
     */
    public function download($url)
    {
        $opts = [
            'http' => [
                'method'           => 'GET',
                'ignore_errors'    => true,
                'timeout'          => $this->mciConfigHelper->getImagesImportTimeout(),
                'protocol_version' => $this->mciConfigHelper->getImagesImportProtocolVersion(),
            ],
        ];

        if ($headers = $this->mciConfigHelper->getImagesImportHeaders()) {
            $opts['http']['header'] = $headers;
        }

        set_error_handler(function ($errno, $errstr) {
            if ($errno == E_WARNING) {
                throw new \ErrorException('Download error: ' . $errstr);
            }
        });

        $resource = fopen($url, 'r', false, stream_context_create($opts));

        restore_error_handler();

        return $resource;
    }

    /**
     * @return GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        $stack = GuzzleHttp\HandlerStack::create();

        $stack->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);
                return $promise->then(
                    function (ResponseInterface $response) use ($request) {
                        return $response->withHeader(self::HEADER_MIRAKL_IMAGE_URL, (string) $request->getUri());
                    }
                );
            };
        });

        $headers = [];
        foreach ($this->mciConfigHelper->getImagesImportHeaders() as $header) {
            [$name, $value] = array_map('trim', explode(':', $header));
            if (!strlen($name) || !$value) {
                continue;
            }
            if (!isset($headers[$name])) {
                $headers[$name] = [$value];
            } else {
                $headers[$name][] = $value;
            }
        }

        $config = [
            'handler' => $stack,
            'headers' => $headers,
        ];

        return new GuzzleHttp\Client($config);
    }

    /**
     * @param GuzzleHttp\Client $client
     * @param string[]          $urls
     * @param callable          $onFulfilled
     * @param callable          $onRejected
     */
    public function downloadMultiple(
        GuzzleHttp\Client $client,
        array $urls,
        callable $onFulfilled,
        callable $onRejected
    ) {
        $requests = function () use ($urls) {
            foreach ($urls as $key => $url) {
                yield $key => new GuzzleHttp\Psr7\Request('GET', $url);
            }
        };

        $pool = new GuzzleHttp\Pool($client, $requests(), [
            'fulfilled' => $onFulfilled,
            'rejected'  => $onRejected,
        ]);

        $pool->promise()->wait();
    }
}
