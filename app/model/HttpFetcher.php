<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Application\BadRequestException;

class HttpFetcher
{
    public function fetch(string $url)
    {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            throw new BadRequestException("Security issue, URL has no valid scheme: $url", 403);
        }

        $reponse = file_get_contents($url);

        return $reponse;
    }


}
