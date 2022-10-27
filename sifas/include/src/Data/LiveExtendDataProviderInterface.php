<?php
namespace EverISay\SIF\V1\AS\Data;

interface LiveExtendDataProviderInterface {
    public function get(int $liveDifficultyId): ?LiveExtendData;
}
