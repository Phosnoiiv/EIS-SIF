<?php
namespace EverISay\SIF\V1\AS\Data;

final class SuyoooLiveExtendDataProvider implements LiveExtendDataProviderInterface {
    function __construct(
        private readonly string $path,
    ) {}

    public function get(int $liveDifficultyId): ?LiveExtendData {
        $file = "{$this->path}/mapdb/$liveDifficultyId.json";
        if (!file_exists($file)) return null;
        $data = json_decode(file_get_contents($file), true);
        $return = new LiveExtendData(self::class);
        $return->noteCount = count($data['notes']);
        $return->waves = array_map(fn($x) => [
            'start' => $x['range_note_ids'][0] + 1,
            'finish' => $x['range_note_ids'][1] + 1,
            'voltage' => $x['reward_voltage'],
            'damage' => $x['penalty_damage'],
        ], $data['appeal_chances']);
        return $return;
    }
}
