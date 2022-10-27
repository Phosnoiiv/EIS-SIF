<?php
namespace EverISay\SIF\V1\AS\Data;

class LiveExtendData {
    function __construct(
        public readonly string $provider,
    ) {}

    /**
     * Total number of real notes. Fake notes for ACs should have been excluded.
     */
    public int $noteCount;

    /**
     * Array of Appeal Chance data. Each element should contain the following keys:
     * 'start', 'finish', 'voltage', 'damage'.
     */
    public array $waves;
}
