<?php
/**
 * Config
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\core;

use RuntimeException;

class config
{
    private $config_path = __DIR__ . '/../../config/';

    private $config = [];

    public function get($key, $file = 'app')
    {
        if (!isset($this->config[$file])) {
            $this->config[$file] = require_config($this->config_path . $file);
        }
        if (isset($this->config[$file][$key])) {
            return $this->config[$file][$key];
        } else {
            throw new RuntimeException('Config file:' . $this->config_path . $file . '.php Key \'' . $key . '\' not set.');
        }
    }

    public function getAll($file = 'app')
    {
        if (!isset($this->config[$file])) {
            $this->config[$file] = require_config($this->config_path . $file);
        }
        if ($this->config[$file]) {
            return $this->config[$file];
        } else {
            throw new RuntimeException('Config file:' . $this->config_path . $file . '.php not set.');
        }
    }
}
