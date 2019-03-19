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
     * @param bool $followRedirect
     * @return Response
     * @throws ForbiddenRequestException
     */
    public function fetch(string $url, bool $followRedirect = false): Response
    {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            throw new ForbiddenRequestException("Security issue, URL has no valid scheme: $url");
        }

        $cacheKey = $url . $this->userAgent;

        /** @var Response $response */
        $response = $this->cache->load($cacheKey, function (&$dependencies) use ($url, $followRedirect) {
            $dependencies = [
                Cache::EXPIRE => '15 minutes',
            ];
            return $this->getResponse($url, $followRedirect);
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
     * @param bool $followRedirect
     * @return Response
     */
    protected function getResponse(string $url, bool $followRedirect = false): Response
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => $followRedirect,
            CURLOPT_MAXREDIRS => $followRedirect ? 3 : 0,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);

        $content = curl_exec($curl);
        $effetciveUrl = $followRedirect ? curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) : $url;
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $redirectUrl = curl_getinfo($curl, CURLINFO_REDIRECT_URL);
        $redirectCount = curl_getinfo($curl, CURLINFO_REDIRECT_COUNT);

        curl_close($curl);

        if ($content === false) {
            $content = null;
        }

        if ($contentType === false) {
            $contentType = 'text/plain';
        }

        if ($redirectUrl === false) {
            $redirectUrl = null;
        }


        return new Response($effetciveUrl, $content, $httpCode, $contentType, $redirectUrl, $redirectCount);
    }


}
