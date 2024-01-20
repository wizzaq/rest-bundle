<?php

namespace Wizzaq\RestBundle\Protocol;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ProtocolInterface
{
    public function processRequest(Request $request): void;

    public function processResponse($response, Request $request): Response;

    public function processException(\Throwable $exception): Response;
}
