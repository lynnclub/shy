<?php

namespace Shy\Core;

use Shy\Core\Contracts\Config as ConfigContract;

class Config extends MemoryCache implements ConfigContract
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * Config constructor.
     *
     * @param string $dir
     */
    public function __construct($dir = '')
    {
        $env = $_SERVER['ENVIRONMENT'] ?? 'local';

        $this->dir = !empty($dir) && is_dir($dir)
            ? $dir
            : dirname(dirname(__DIR__)) . '/config/' . $env . DIRECTORY_SEPARATOR;
    }

    /**
     * Load config file to cache
     *
     * @param string $filename
     *
     * @throws \Shy\Core\Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return bool|array
     */
    public function load(string $filename)
    {
        if ($this->has($filename)) {
            return $this->get($filename);
        }

        $configFile = $this->dir . $filename . '.php';
        if ($config = require_file($configFile)) {
            $this->set($filename, $config);

            return $this->get($filename);
        }
    }

    /**
     * Find key in config file cache
     *
     * @param string $key
     * @param string $filename
     *
     * @throws Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return string|array|null
     */
    public function find(string $key, string $filename = 'app')
    {
        if ($this->has($filename)) {
            $array = $this->get($filename);
        } else {
            $array = $this->load($filename);
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        return null;
    }

}
