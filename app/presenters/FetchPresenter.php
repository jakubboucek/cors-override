<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\HttpFetcher;
use Nette\Application\UI\Presenter;

class FetchPresenter extends Presenter
{

    /**
     * @var HttpFetcher
     */
    private $fetcher;


    public function __construct(HttpFetcher $fetcher)
    {

        $this->fetcher = $fetcher;
    }

    public function renderRun(string $url, ?string $token): void
    {

        $request = $this->getHttpRequest();
        $response = $this->getHttpResponse();

        $origin = $request->getHeader('origin');
        $headers = $request->getHeaders();

        if ($origin !== null) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }

        $content = $this->fetcher->fetch($url);

        $this->sendJson([
            'content' => $content,
        ]);
    }
}
