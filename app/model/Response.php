<?php
declare(strict_types=1);

namespace App\Model;

class Response
{
    /**
     * @var string|null
     */
    private $content;
    /**
     * @var int
     */
    private $code;
    /**
     * @var string|null
     */
    private $redirectUrl;


    public function __construct(?string $content, int $code, ?string $redirectUrl)
    {
        $this->content = $content;
        $this->code = $code;
        $this->redirectUrl = $redirectUrl;
    }


    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }


    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }


    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
