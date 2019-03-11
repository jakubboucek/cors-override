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
     * @var string
     */
    private $contentType;


    /**
     * @var string|null
     */
    private $redirectUrl;


    public function __construct(?string $content, int $code, string $contentType, ?string $redirectUrl = null)
    {
        $this->content = $content;
        $this->code = $code;
        $this->contentType = $contentType;
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
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }


    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}
