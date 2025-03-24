<?php
namespace App\Helpers;

use Core\Request;

class RateLimit {
    private $maxRequests;
    private $period;
    private $storageFile;

    public function __construct($maxRequests = 60, $period = 60) {
        $this->maxRequests = $maxRequests;
        $this->period = $period;
        $this->storageFile = sys_get_temp_dir() . '/rate_limit.json';
    }

    public function check() {
        $ip = Request::getIp();
        $data = $this->loadData();
        $now = time();
        
        $data = array_filter($data, function($record) use ($now) {
            return ($now - $record['time']) < $this->period;
        });
        
        $requests = array_filter($data, function($record) use ($ip) {
            return $record['ip'] === $ip;
        });
        
        if (count($requests) >= $this->maxRequests) {
            return false;
        }
        
        $data[] = ['ip' => $ip, 'time' => $now];
        $this->saveData($data);
        
        return true;
    }

    private function loadData() {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->storageFile), true) ?? [];
    }

    private function saveData($data) {
        file_put_contents($this->storageFile, json_encode($data), LOCK_EX);
    }
}
