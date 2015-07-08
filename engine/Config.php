<?php

namespace IPBParser\Engine;

class Config {

    private $config;

    public function __construct($config){
        $this->config = $config;
    }

    public function __get($key){
        return (isset($this->config[$key]) ? $this->config[$key] : null);
    }

    public function __set($key, $value) {
        $this->config[$key] = $value;
    }
}