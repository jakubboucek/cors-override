<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Security\Passwords;
use Tracy\Debugger;

class TokenAuthenticator
{
    /**
     * @var Passwords
     */
    private $hasher;


    /**
     * @param Passwords $hasher
     */
    public function __construct(Passwords $hasher)
    {
        $this->hasher = $hasher;
    }


    /**
     * @return array
     */
    protected function getValidTokens(): array
    {
        return [
            'aBHt7S' => '$2y$10$VXLNUfGATvI2qBxMVWfHQeOo09t16kvg.LZ8zFYM8n7HVLRQTfUDy', // Techheaven.org Event FB scrapper
        ];
    }


    /**
     * @param string $token
     * @return string|null
     */
    public function authorize(string $token): ?string
    {
        if ($this->isValidTokenFormat($token) && $this->isValidHash($token)) {
            return $this->parseTokenId($token);
        }

        return null;
    }


    /**
     * @param string $token
     * @return bool
     */
    protected function isValidHash(string $token): bool
    {
        $id = $this->parseTokenId($token);
        $hash = $this->getHashForId($id);
        $secret = $this->parseTokenHash($token);
        return is_string($hash) && $this->verifyTokenHash($secret, $hash);
    }


    /**
     * @param string $id
     * @return mixed|null
     */
    protected function getHashForId(string $id)
    {
        $hashes = $this->getValidTokens();
        return $hashes[$id] ?? null;
    }


    /**
     * @param string $token
     * @return bool
     */
    protected function isValidTokenFormat(string $token): bool
    {
        return $this->parseTokenVersion($token) === 'v1' &&
            is_string($this->parseTokenId($token)) &&
            is_string($this->parseTokenHash($token));
    }


    /**
     * @param string $token
     * @return string|null
     */
    protected function parseTokenVersion(string $token): ?string
    {
        $parts = explode('.', $token);
        return $parts[0] ?? null;
    }


    /**
     * @param string $token
     * @return string|null
     */
    protected function parseTokenId(string $token): ?string
    {
        $parts = explode('.', $token);
        return $parts[1] ?? null;
    }


    /**
     * @param string $token
     * @return string|null
     */
    protected function parseTokenHash(string $token): ?string
    {
        $parts = explode('.', $token);
        return $parts[2] ?? null;
    }


    /**
     * @param string $tokenSecret
     * @return string
     * @throws \Nette\InvalidStateException
     */
    protected function hashTokenSecret(string $tokenSecret): string
    {
        return $this->hasher->hash($tokenSecret);
    }


    /**
     * @param string $tokenSecret
     * @param string $hash
     * @return bool
     */
    protected function verifyTokenHash(string $tokenSecret, string $hash): bool
    {
        return $this->hasher->verify($tokenSecret, $hash);
    }
}
