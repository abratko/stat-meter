<?php

namespace App\PresentationLayer\TokenValidator;

use Symfony\Component\HttpFoundation\Request;

class TokenValidator implements TokenValidatorInterface
{
    public function isTokenValid(string $token, Request $request): bool
    {
        return true;
    }
}
