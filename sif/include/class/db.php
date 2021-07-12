<?php
namespace EIS\Lab\SIF;
use \mysqli;
use \SQLite3;
if (!defined('EIS_ENV'))
    exit;

trait DBTrait {
    protected static $myCon;
    protected static $ltCon;
}

abstract class DBBase {
    protected abstract static function getConfigSheet();

    private static $ltWritableDBs = [];

    private static function myConnect() {
        global $config;
        $con = new mysqli(
            $config['mysql_host'],
            $config['mysql_username'],
            $config['mysql_passwd'],
            $config[static::getConfigSheet()]['mysql_dbname'],
            $config['mysql_port']
        );
        if ($con->connect_errno) {
            Basic::exit('Connect error ' . $con->connect_errno);
        }
        static::$myCon = $con;
    }
    private static function myCheck() {
        if (!static::$myCon) {
            self::myConnect();
        }
    }
    static function my_query($query) {
        self::myCheck();
        return static::$myCon->query($query);
    }
    static function mySelect($query, $columns, $key, $options = []) {
        $dbResult = self::my_query($query);
        if (!empty($options['z'])) {
            $result = [null];
        }
        while ($dbRow = $dbResult->fetch_assoc()) {
            $row = [];
            foreach ($columns as $column) {
                $value = $dbRow[$column[1]];
                switch ($column[0]) {
                    case 'i':
                        $value = intval($value);
                        break;
                    case 'd':
                        $value = floatval($value);
                        break;
                    case 'T':
                        $value = SIF::toDatestamp($value, $column[2] ?? 0);
                        break;
                    case 't':
                        $value = empty($value) ? 0 : SIF::toTimestamp($value, $column[2]);
                        break;
                    case 's':
                        if (is_null($value) && isset($column[2])) {
                            $value = $column[2];
                        }
                        break;
                }
                $row[] = $value;
            }
            if (!empty($options['s'])) {
                $row = $row[0];
            }
            if (empty($key)) {
                $result[] = $row;
            } else if (!empty($options['k'])) {
                $result[$dbRow[$key]][$dbRow[$options['k']]] = $row;
            } else if (!empty($options['m'])) {
                $result[$dbRow[$key]][] = $row;
            } else {
                $result[$dbRow[$key]] = $row;
            }
        }
        return $result;
    }
    static function myInsert($table, $params) {
        self::myCheck();
        $columns = implode(',', array_map(function($a) {return '`' . $a . '`';}, array_keys($params)));
        $values = implode(',', array_map(function($a) {
            switch ($a[0]) {
                case 'i':
                    return intval($a[1]);
                case 'd':
                    return floatval($a[1]);
                case 's':
                    return "'" . static::$myCon->real_escape_string($a[1]) . "'";
            }
        }, array_values($params)));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        return self::my_query($sql);
    }

    private static function ltConnect($db, $writable) {
        global $config;
        $con = new SQLite3($config[static::getConfigSheet()]['sqlite_dir'] . '/' . $db, $writable ? SQLITE3_OPEN_READWRITE : SQLITE3_OPEN_READONLY);
        $con->busyTimeout(10000);
        static::$ltCon[$db] = $con;
    }
    private static function lt_check($db, $writable = false) {
        if ($writable && !in_array($db, self::$ltWritableDBs)) {
            if (!empty(static::$ltCon[$db])) {
                static::$ltCon[$db]->close();
                static::$ltCon[$db] = null;
            }
            self::$ltWritableDBs[] = $db;
        }
        if (!empty(static::$ltCon[$db]))
            return;
        self::ltConnect($db, $writable);
    }
    static function ltAttach($main, $attach, $name) {
        global $config;
        self::lt_check($main);
        $db = $config[static::getConfigSheet()]['sqlite_dir'] . '/' . $attach;
        return static::$ltCon[$main]->exec("ATTACH DATABASE '$db' AS '$name'");
    }
    static function lt_query($db, $query) {
        self::lt_check($db);
        return static::$ltCon[$db]->query($query);
    }
    static function ltSelect($db, $query, $columns, $key, $options = []) {
        $dbResult = self::lt_query($db, $query);
        if (!empty($options['z'])) {
            $result = [null];
        }
        while ($dbRow = $dbResult->fetchArray(SQLITE3_ASSOC)) {
            $row = [];
            foreach ($columns as $column) {
                $value = $dbRow[$column[1]];
                switch ($column[0]) {
                    case 't':
                        $value = empty($value) ? 0 : SIF::toTimestamp($value, $column[2]);
                        break;
                    case 'i':
                    case 's':
                        if (is_null($value) && isset($column[2])) {
                            $value = $column[2];
                        }
                        break;
                }
                $row[] = $value;
            }
            if (!empty($options['s'])) {
                $row = $row[0];
            }
            if (empty($key)) {
                $result[] = $row;
            } else if (!empty($options['k'])) {
                $result[$dbRow[$key]][$dbRow[$options['k']]] = $row;
            } else if (!empty($options['m'])) {
                $result[$dbRow[$key]][] = $row;
            } else {
                $result[$dbRow[$key]] = $row;
            }
        }
        return $result ?? [];
    }
    static function ltParamQuery($db, $query, $params) {
        $writable = preg_match('/^(INSERT|UPDATE|DELETE)/', $query);
        self::lt_check($db, $writable);
        $stmt = static::$ltCon[$db]->prepare($query);
        foreach ($params as $param => $config) {
            if (is_array($config)) {
                $stmt->bindValue($param, $config[0], $config[1]);
            } else {
                $stmt->bindValue($param, $config);
            }
        }
        return $stmt->execute();
    }
    static function ltInsert($db, $table, $params) {
        $columns = implode(',', array_map(function($a) {return '`' . $a . '`';}, array_keys($params)));
        $values = implode(',', array_map(function($a) {return ':' . $a;}, array_keys($params)));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        return self::ltParamQuery($db, $sql, $params);
    }

    static function ltSQLTimeIn(string $column1, string $column2): string {
        return "$column1<=datetime('now','localtime') AND ($column2 IS NULL OR $column2>=datetime('now','localtime'))";
    }
}

class DB extends DBBase {
    use DBTrait;
    protected static function getConfigSheet() {
        return CONFIG_SHEET_SIF;
    }
}
