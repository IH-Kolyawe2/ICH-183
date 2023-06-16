<?php

namespace App\Libs\PasswordHashers;

class BCryptPasswordHasher implements IPasswordHasher
{

    private int $cost;
    private string $pattern;

    public function __construct(int $cost)
    {
        $this->cost = $cost;
        $this->pattern = '/^\$2\w\$(?<cost>\d{2})\$.{53}$/i';
    }

    public function canHandle(string $hash): bool
    {
        return preg_match($this->pattern, $hash, $matches) === 1;
    }

    public function hash(string $value): string
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $this->cost]);

        return $hash;
    }

    public function verify(string $value, string $hash): bool|null
    {
        if(preg_match($this->pattern, $hash, $matches) === false) {
            return null;
        }

        return password_verify($value, $hash);
    }

    public function checkForUpdate(string $hash): bool
    {
        if(preg_match($this->pattern, $hash, $matches) !== 1) {
            return true;
        }

        $actualCost = (int)$matches['cost'];

        return $actualCost !== $this->cost;
    }
}