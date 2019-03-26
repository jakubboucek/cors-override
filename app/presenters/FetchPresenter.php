<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\HttpFetcher;
use App\Model\TokenAuthenticator;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Tracy\Debugger;

class FetchPresenter extends Presenter
{

    /**
     * @var HttpFetcher
     */
    private $fetcher;
    /**
     * @var TokenAuthenticator
     */
    private $authenticator;


    public function __construct(HttpFetcher $fetcher, TokenAuthenticator $authenticator)
    {
        parent::__construct();
        $this->fetcher = $fetcher;
        $this->authenticator = $authenticator;
    }


    public function renderRun(string $url, string $format, ?string $token, bool $followRedirect = false): void
    {
        $userId = null;
        if ($token !== null) {
            $userId = $this->authenticator->authorize($token);

            if ($userId === null) {
                throw new ForbiddenRequestException('Invalid token');
            }
        }

        Debugger::log("Fetch URL \"$url\", userId: \"$userId\"");

        $request = $this->getHttpRequest();
        $response = $this->getHttpResponse();

        $userAgent = $request->getHeader('User-Agent');

        $origin = $request->getHeader('origin');

        if ($origin !== null) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }


        if ($userAgent !== null) {
            $this->fetcher->setUserAgent($userAgent);
        }
        $content = $this->fetcher->fetch($url, $followRedirect);

        if ($format === 'json') {
            $this->sendJsonResponse($url, $followRedirect, $content, $userId);
        } else {
            $this->sendHtmlResponse($url, $followRedirect, $content, $userId);
        }
    }


    /**
     * @param string $url
     * @param bool $followRedirect
     * @param \App\Model\Response $content
     * @param string|null $userId
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function sendHtmlResponse(
        string $url,
        bool $followRedirect,
        \App\Model\Response $content,
        ?string $userId
    ): void {
        $httpResponse = $this->getHttpResponse();

        $httpResponse->setCode($content->getCode());
        $httpResponse->setContentType($content->getContentType());

        if ($content->getRedirectUrl() !== null) {
            $parameters = $this->getHttpRequest()->getUrl()->queryParameters;
            $parameters['url'] = $content->getRedirectUrl();

            $redirectUrl = $this->link('//this', $parameters);
            $httpResponse->addHeader('Location', $redirectUrl);
        }

        $this->sendResponse(new TextResponse($content->getContent()));
    }


    /**
     * @param string $url
     * @param bool $followRedirect
     * @param \App\Model\Response $content
     * @param string|null $userId
     * @throws \Nette\Application\AbortException
     */
    protected function sendJsonResponse(
        string $url,
        bool $followRedirect,
        \App\Model\Response $content,
        ?string $userId
    ): void {
        $this->sendJson([
            'response' => [
                'url' => $content->getUrl(),
                'code' => $content->getCode(),
                'content' => $content->getContent(),
                'contentType' => $content->getContentType(),
                'redirectUrl' => $content->getRedirectUrl(),
                'redirectCount' => $content->getRedirectCount(),
            ],
            'request' => [
                'url' => $url,
                'method' => 'GET',
            ],
            'followRedirects' => $followRedirect,
            'userId' => $userId,
        ]);
    }
}
