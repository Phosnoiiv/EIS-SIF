<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class Dict {
    private $dict = [];
    private $reverseDict = [];
    private $reverseEnabled;
    function __construct($enableReverse = false) {
        $this->reverseEnabled = $enableReverse;
    }
    function set($value) {
        if (($key = $this->find($value)) !== false)
            return $key;
        $this->dict[] = $value;
        $key = array_key_last($this->dict);
        if ($this->reverseEnabled) {
            $this->reverseDict[$value] = $key;
        }
        return $key;
    }
    function get($key) {
        return $this->dict[$key] ?? null;
    }
    function getAll() {
        return $this->dict;
    }
    function find($value) {
        if ($this->reverseEnabled) {
            if (array_key_exists($value, $this->reverseDict))
                return $this->reverseDict[$value];
        } else {
            if (($key = array_search($value, $this->dict)) !== false)
                return $key;
        }
        return false;
    }
}
