<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Application\ForbiddenRequestException;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class HttpFetcher
{
    private $userAgent = 'jakubbpucek/cors-override (dev)';
    private $cache;


    public function __construct(IStorage $storage)
    {
        $this->cache = new Cache($storage, __CLASS__);
    }


    /**
     * @param string $url
     * @return Response
     * @throws ForbiddenRequestException
     */
    public function fetch(string $url): Response
    {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            throw new ForbiddenRequestException("Security issue, URL has no valid scheme: $url");
        }

        $cacheKey = $url . $this->userAgent;

        /** @var Response $response */
        $response = $this->cache->load($cacheKey, function (&$dependencies) use ($url) {
            $dependencies = [
                Cache::EXPIRE => '15 minutes',
            ];
            return $this->getResponse($url);
        });

        return $response;
    }


    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->userAgent;
    }


    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }


    /**
     * @param string $url
     * @return Response
     */
    protected function getResponse(string $url): Response
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $redirectUrl = curl_getinfo($curl, CURLINFO_REDIRECT_URL);

        curl_close($curl);

        if ($response === false) {
            $response = null;
        }

        if ($redirectUrl === false) {
            $redirectUrl = null;
        }


        return new Response($response, $httpcode, $redirectUrl);
    }


}
