<?php

declare(strict_types=1);

namespace RaphaelVoisin\Ringover\Api;

class Result
{
    private $data;

    protected function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public static function fromResponseData($data): self
    {
        return new self($data);
    }
}
