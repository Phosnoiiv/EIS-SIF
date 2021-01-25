<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class Limit {
    const TYPE_GALLERY = 1;
    static $CapacityNames = [
        self::TYPE_GALLERY => '下载次数',
    ];

    public $max;
    public $current;
    public $recoverAmount;
    public $recoverPeriod;
    public $nextRecoverTime;
    private $type;
    private $userIdentity;

    function __construct($limitType, $userIdentity) {
        global $config;
        $this->type = $limitType;
        $this->userIdentity = $userIdentity;
        $sql = "SELECT * FROM s_limit WHERE type=$limitType";
        $dbLimit = DB::lt_query('running.s3db', $sql)->fetchArray(SQLITE3_ASSOC);
        if (!$dbLimit)
            throw new \Exception('Invalid argument');
        $this->max = $dbLimit['max_amount'];
        $this->recoverAmount = $dbLimit['recover_amount'];
        $this->recoverPeriod = $dbLimit['recover_period'];
        $time = time();
        $maxRecoverTime = ceil($this->max / $this->recoverAmount) * $this->recoverPeriod;
        $sqlTable = $config['sqlite_running_prefix'] . '_limit_log';
        $sql = "SELECT COUNT(*) AS c,MIN(time) AS m FROM $sqlTable WHERE type=$limitType AND user='$userIdentity' AND time>=$time-$maxRecoverTime";
        $dbStatus = DB::lt_query('running.s3db', $sql)->fetchArray(SQLITE3_ASSOC);
        $this->current = $this->max - $dbStatus['c'];
        $this->nextRecoverTime = $dbStatus['m'] + $this->recoverPeriod;
    }

    function record($info) {
        global $config;
        DB::ltInsert('running.s3db', $config['sqlite_running_prefix'] . '_limit_log', [
            'type' => $this->type,
            'user' => $this->userIdentity,
            'time' => time(),
            'info' => $info,
        ]);
    }
}
