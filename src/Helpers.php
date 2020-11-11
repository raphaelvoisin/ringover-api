<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover;

use GuzzleHttp\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RaphaelVoisin\Ringover\Api\Result;
use RaphaelVoisin\Ringover\Exception\ErrorInRequestException;
use RaphaelVoisin\Ringover\Exception\InternalServerErrorException;
use RaphaelVoisin\Ringover\Exception\InvalidJsonResponseDataException;
use RaphaelVoisin\Ringover\Exception\RingoverApiException;
use RaphaelVoisin\Ringover\Exception\UnauthorizedException;
use RaphaelVoisin\Ringover\Exception\UnknownResponseCodeException;

class Helpers
{
    public static function getUnknownResponseException(ResponseInterface $response): RingoverApiException
    {
        $responseCode = $response->getStatusCode();
        $codeLevel = (int) floor($response->getStatusCode() / 100);

        switch (true) {
            case $codeLevel === 4:
                if ($responseCode === 401) {
                    if ($response->getBody()->__toString() === '') {
                        return new UnauthorizedException('API key may be invalid');
                    }

                    return new UnauthorizedException();
                }

                return new ErrorInRequestException();
            case $codeLevel === 5:
                return new InternalServerErrorException();
            default:
                return new UnknownResponseCodeException('Unknown response code : ' . $response->getStatusCode());
        }
    }

    public static function handleBasicResponse(ResponseInterface $response): Result
    {
        return Result::fromResponseData(self::getJsonResponseData($response));
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
