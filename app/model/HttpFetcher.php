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
     * @return false|string
     * @throws ForbiddenRequestException
     */
    public function fetch(string $url)
    {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            throw new ForbiddenRequestException("Security issue, URL has no valid scheme: $url");
        }

        $cacheKey = $url . $this->userAgent;

        $response = $this->cache->load($cacheKey, function (&$dependencies)use ($url)  {
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
     * @return bool|string
     */
    protected function getResponse(string $url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $this->userAgent
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


}
