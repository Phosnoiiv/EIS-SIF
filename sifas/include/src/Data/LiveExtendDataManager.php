<?php
namespace EverISay\SIF\V1\AS\Data;

class LiveExtendDataManager {
    /**
     * @param LiveExtendDataProviderInterface[] $providers
     */
    function __construct(
        private array $providers,
    ) {}

    /** @var LiveExtendData[] */
    private array $dataStorage;
    public function get(int $liveDifficultyId): ?LiveExtendData {
        return $this->dataStorage[$liveDifficultyId] ??= $this->fetch($liveDifficultyId);
    }
    private function fetch(int $liveDifficultyId): ?LiveExtendData {
        foreach ($this->providers as $provider) {
            if (null === ($data = $provider->get($liveDifficultyId))) continue;
            return $data;
        }
        return null;
    }
}
