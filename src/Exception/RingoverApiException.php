<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover\Exception;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RingoverApiException extends \RuntimeException
{
    /**
     * @var RequestException
     */
    private $guzzleRequestException;

    public function __construct(
        $message,
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $previous = null
    ) {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            return $body->rewind();
        }

        $this->guzzleRequestException = RequestException::create($request, $response, $previous);

        parent::__construct($message . ' - ' . $this->guzzleRequestException->getMessage(), $this->guzzleRequestException->getCode(), $previous);
    }
}
