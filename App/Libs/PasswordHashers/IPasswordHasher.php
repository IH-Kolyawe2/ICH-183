<?php

namespace App\Libs\PasswordHashers;

interface IPasswordHasher
{
    function canHandle(string $hash): bool;
    function hash(string $value): string;
    function verify(string $value, string $hash): bool|null;
    function checkForUpdate(string $hash): bool;
}