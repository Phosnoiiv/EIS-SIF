<?php
namespace EverISay\SIF\V1\AS\Data;

use EIS\Lab\SIFAS\DB;

final class V1LiveExtendDataProvider implements LiveExtendDataProviderInterface {
    public function get(int $liveDifficultyId): ?LiveExtendData {
        $sql = "SELECT * FROM live_difficulty WHERE id=$liveDifficultyId";
        $col = [['i','ignore'],['i','note_count']];
        $dLiveDifficulty = DB::ltSelect('cache.s3db', $sql, $col, '');
        if (empty($dLiveDifficulty)) return null;
        $dLiveDifficulty = $dLiveDifficulty[0];
        if (!empty($dLiveDifficulty[0]) || empty($dLiveDifficulty[1])) return null;
        $return = new LiveExtendData(self::class);
        $return->noteCount = $dLiveDifficulty[1];
        $sql = "SELECT * FROM live_Wave WHERE live_difficulty=$liveDifficultyId";
        $col = [['i','start'],['i','finish'],['i','voltage'],['i','damage']];
        $dWaves = DB::ltSelect('cache.s3db', $sql, $col, '');
        $return->waves = array_map(fn($d) => array_combine(['start', 'finish', 'voltage', 'damage'], $d), $dWaves);
        return $return;
    }
}
