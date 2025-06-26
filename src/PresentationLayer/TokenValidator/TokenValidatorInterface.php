<?php

namespace App\PresentationLayer\TokenValidator;

use Symfony\Component\HttpFoundation\Request;

interface TokenValidatorInterface
{
    public function isTokenValid(string $token, Request $request): bool;
}
