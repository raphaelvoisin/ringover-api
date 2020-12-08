<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PaymentRequiredException extends RingoverApiException
{
    public static function create(
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $previous = null
    ): self {
        return new self(
            'Payment required',
            $request,
            $response,
            $previous
        );
    }
}
