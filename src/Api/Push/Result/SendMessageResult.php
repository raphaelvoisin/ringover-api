<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover\Api\Push\Result;

use RaphaelVoisin\Ringover\Exception\InvalidJsonResponseDataException;

class SendMessageResult
{
    /**
     * @var int
     */
    private $messageId;

    /**
     * @var int
     */
    private $convId;

    protected function __construct(
        int $messageId,
        int $convId
    ) {
        $this->messageId = $messageId;
        $this->convId = $convId;
    }

    public function getMessageId(): int
    {
        return $this->messageId;
    }

    public function getConvId(): int
    {
        return $this->convId;
    }

    public static function fromResponseData($data): self
    {
        if (!\is_array($data)) {
            throw new InvalidJsonResponseDataException(sprintf('Data is not an array : %s', print_r($data, true)));
        }

        if (!isset($data['message_id']) || !\is_int($data['message_id'])) {
            throw new InvalidJsonResponseDataException('message_id key not found or not an int');
        }

        if (!isset($data['conv_id']) || !\is_int($data['conv_id'])) {
            throw new InvalidJsonResponseDataException('conv_id key not found or not an int');
        }

        return new self(
            $data['message_id'],
            $data['conv_id']
        );
    }
}
