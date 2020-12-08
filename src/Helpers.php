<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover;

use GuzzleHttp\Exception\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RaphaelVoisin\Ringover\Exception\ErrorInRequestException;
use RaphaelVoisin\Ringover\Exception\InternalServerErrorException;
use RaphaelVoisin\Ringover\Exception\InvalidJsonResponseDataException;
use RaphaelVoisin\Ringover\Exception\RingoverApiException;
use RaphaelVoisin\Ringover\Exception\UnauthorizedException;
use RaphaelVoisin\Ringover\Exception\UnknownResponseCodeException;

class Helpers
{
    public static function getUnknownResponseException(RequestInterface $request, ResponseInterface $response): RingoverApiException
    {
        $responseCode = $response->getStatusCode();
        $codeLevel = (int) floor($response->getStatusCode() / 100);

        switch (true) {
            case $codeLevel === 4:
                if ($responseCode === 401) {
                    if ($response->getBody()->__toString() === '') {
                        return new UnauthorizedException('API key may be invalid', $request, $response);
                    }

                    return new UnauthorizedException('Not authorized', $request, $response);
                }

                return new ErrorInRequestException('Error in request', $request, $response);
            case $codeLevel === 5:
                return new InternalServerErrorException('Ringover server error', $request, $response);
            default:
                return new UnknownResponseCodeException('Unknown response code : ' . $response->getStatusCode(), $request, $response);
        }
    }

    public static function getJsonResponseData(ResponseInterface $response)
    {
        $responseBody = $response->getBody()->__toString();

        try {
            return \GuzzleHttp\json_decode($responseBody, true);
        } catch (InvalidArgumentException $e) {
            throw new InvalidJsonResponseDataException();
        }
    }
}
