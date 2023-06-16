<?php

namespace App\Libs\PasswordHashers;

class Md5PasswordHasher implements IPasswordHasher
{
    private $pattern;

    public function __construct()
    {
        $this->pattern = '/^\w{40}$/i';
    }

    public function canHandle(string $hash): bool
    {
        return preg_match($this->pattern, $hash, $matches) === 1;
    }

    public function hash(string $value): string
    {
        $hash = md5($value);

        return $hash;
    }

    public function verify(string $value, string $hash): bool|null
    {
        if(preg_match($this->pattern, $hash, $matches) === false) {
            return null;
        }

        return $this->hash($value) === $hash;
    }

    public function checkForUpdate(string $hash): bool
    {
        if (preg_match($this->pattern, $hash, $matches) !== 1) {
            return true;
        }

        return false;
    }
}