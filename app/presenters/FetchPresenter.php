<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Presenter;

class FetchPresenter extends Presenter
{

    public function renderRun(string $url): void
    {

        $request = $this->getHttpRequest();
        $response = $this->getHttpResponse();

        $origin = $request->getHeader('origin');
        $headers = $request->getHeaders();

        if ($origin !== null) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }

        $this->sendJson([
            'content' => 'Hello world',
            'origin' => $origin,
            'headers' => $headers,
        ]);
    }
}
