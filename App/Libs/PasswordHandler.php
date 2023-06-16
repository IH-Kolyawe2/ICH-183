<?php

namespace App\Libs;

use App\Config;
use Exception;
use App\Libs\PasswordHashers\IPasswordHasher;
use App\Libs\PasswordHashers\BCryptPasswordHasher;
use App\Libs\PasswordHashers\Sha1PasswordHasher;
use App\Libs\PasswordHashers\Md5PasswordHasher;
use App\Libs\PasswordHashers\PlaintextHasher;

class PasswordHandler
{
    private array $passwordHashers;

    public function __construct()
    {
        /* 
         * Handle password life cylce.
         * For example, password life cycle started with plaintext hashing,
         *  following Md5 hashing, then Sha1Hash and finally BCrypt hashing.
         * The most recent hash algo is placed at the begining of the collection.
         */
        $this->passwordHashers = [
            new BcryptPasswordHasher(Config::BCRYPT_COST),
            new Sha1PasswordHasher(),
            new Md5PasswordHasher(),
            new PlaintextHasher(),
        ];

        // Ensure passwordhashers are defined.
        if (empty($this->passwordHashers)) {
            throw new Exception('PasswordHashers should be initialized.');
        }
    }

    public function hash(string $value): string
    {
        $passwordHasher = $this->getMostRecentHasher($value);

        return $passwordHasher->hash($value);
    }

    public function verify(string $value, string $hash): bool|null
    {
        $passwordHasher = $this->finPasswordHasher($hash);

        return $passwordHasher->verify($value, $hash);
    }

    public function checkForUpdate(string $hash): bool
    {
        $passwordHasher = $this->getMostRecentHasher($hash);

        return $passwordHasher->checkForUpdate($hash);
    }
    
    private function getMostRecentHasher($value): IPasswordHasher
    {
        return $this->passwordHashers[0];
    }

    private function finPasswordHasher($hash): IPasswordHasher
    {
        foreach($this->passwordHashers as $passwordHasher) {
            if($passwordHasher->canHandle($hash)){
                return $passwordHasher;
            }
        }

        throw new Exception('Unable to find a password handle to handle value');
    }
}