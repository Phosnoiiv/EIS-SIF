<?php
namespace EverISay\SIF\V1\AS\Archive;

class SazEntry {
    function __construct(
        public readonly string $requestBody,
        public readonly string $responseBody,
    ) {}

    public function decodeJsonRequest(): mixed {
        return json_decode($this->requestBody, true);
    }

    public function decodeJsonResponse(): mixed {
        return json_decode($this->responseBody, true);
    }
}
