<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover\Api\Push;

use RaphaelVoisin\Ringover\Api\Api;
use RaphaelVoisin\Ringover\Api\Push\Result\SendMessageResult;
use RaphaelVoisin\Ringover\Client;
use RaphaelVoisin\Ringover\Exception\ErrorInRequestException;
use RaphaelVoisin\Ringover\Exception\InvalidJsonResponseDataException;
use RaphaelVoisin\Ringover\Exception\PaymentRequiredException;
use RaphaelVoisin\Ringover\Exception\UnauthorizedException;
use RaphaelVoisin\Ringover\Helpers;

class Push implements Api
{
    private const API_ENDPOINT = 'https://public-api.ringover.com/v2/push/sms';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function sendMessage(
        string $fromNumber,
        string $toNumber,
        string $content,
        bool $archivedAuto = false
    ): SendMessageResult {
        $payload = [
            'archived_auto' => $archivedAuto,
            'from_number' => $fromNumber,
            'to_number' => $toNumber,
            'content' => $content
        ];

        $request = $this->client->buildRequest(
            self::API_ENDPOINT,
            $payload
        );

        $response = $this->client->sendRequest($request);

        switch ($response->getStatusCode()) {
            case 200:
            case 202: // Undocumented
                try {
                    return SendMessageResult::fromResponseData(Helpers::getJsonResponseData($response));
                } catch (InvalidJsonResponseDataException $e) {
                    throw new ErrorInRequestException('Error in request', $request, $response, $e);
                }
            case 401:
                try {
                    $data = Helpers::getJsonResponseData($response);
                } catch (InvalidJsonResponseDataException $e) {
                    throw new ErrorInRequestException('Error in request', $request, $response, $e);
                }
                if ($data === 'Read only') {
                    throw new UnauthorizedException(
                        \sprintf('The number %s is not registered in your account, you cannot send a message using it as sender', $fromNumber),
                        $request,
                        $response
                    );
                }
                if ($data === 'It is not your number') {
                    throw new UnauthorizedException(
                        \sprintf('The number %s is registered in your account but you are not allowed to use it as sender with this API key', $fromNumber),
                        $request,
                        $response
                    );
                }

                throw Helpers::getUnknownResponseException($request, $response);
            case 402:
                throw PaymentRequiredException::create($request, $response);
            default:
                throw Helpers::getUnknownResponseException($request, $response);
        }
    }
}
