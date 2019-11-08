<?php

namespace Shy\Core;

use Shy\Core\Cache\Memory;
use Shy\Core\Contracts\Config as ConfigContract;
use Shy\Core\Exceptions\Cache\InvalidArgumentException;

class Config extends Memory implements ConfigContract
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * Config constructor.
     *
     * @param string $dir
     *
     * @throws Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function __construct($dir = '')
    {
        $env = getenv('SHY_ENV');
        if (empty($env)) {
            $env = 'develop';
        }
        $this->set('SHY_ENV', $env);

        $this->dir = !empty($dir) && is_dir($dir)
            ? $dir
            : dirname(dirname(__DIR__)) . '/config/' . $env . DIRECTORY_SEPARATOR;

        $cacheFile = $this->find('path.cache') . 'app/config.cache';

        if (is_cli()) {
            $isCacheOn = false;
        } else {
            $isCacheOn = $this->find('app.cache');
        }

        parent::__construct($cacheFile, $isCacheOn);
    }

    /**
     * Load config file to cache
     *
     * @param string $filename
     *
     * @throws \Shy\Core\Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return array|null
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
     *
     * @throws Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return string|array|null
     */
    public function find(string $key)
    {
        $keyLevels = explode('.', $key);
        if (empty($keyLevels[0])) {
            throw new InvalidArgumentException('No configuration file specified.');
        }

        $config = $this->load(array_shift($keyLevels));
        if (empty($keyLevels)) {
            return $config;
        } elseif (is_array($config)) {
            return get_array_key($keyLevels, $config);
        }
    }

}
