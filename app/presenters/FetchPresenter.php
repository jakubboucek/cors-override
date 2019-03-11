<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\HttpFetcher;
use App\Model\TokenAuthenticator;
use Nette\Application\ForbiddenRequestException;
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


    public function renderRun(string $url, ?string $token): void
    {
        $user = null;
        if ($token !== null) {
            $user = $this->authenticator->authorize($token);
            if ($user === null) {
                throw new ForbiddenRequestException('Invalid token');
            }
        }

        Debugger::log("Fetch URL \"$url\"");

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
        $content = $this->fetcher->fetch($url);

        $this->sendJson([
            'content' => $content,
            'userId' => $user,
        ]);
    }
}
