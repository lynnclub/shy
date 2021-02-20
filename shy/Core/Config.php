<?php

namespace Shy\Core;

use Shy\Core\Cache\Memory;
use Shy\Core\Contracts\Config as ConfigContract;
use Shy\Core\Exceptions\Cache\InvalidArgumentException;
use Exception;

class Config extends Memory implements ConfigContract
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    protected $envDir;

    /**
     * Config constructor.
     *
     * @param string $dir
     * @param string $env
     * @param string $cacheDir
     *
     * @throws Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function __construct(string $dir, string $env, string $cacheDir)
    {
        $envDir = $dir . DIRECTORY_SEPARATOR . $env . DIRECTORY_SEPARATOR;

        if (is_dir($envDir)) {
            $this->dir = $dir . DIRECTORY_SEPARATOR;
            $this->envDir = $envDir;
        } else {
            throw new Exception('Config dir is not exist.');
        }

        parent::__construct($cacheDir, $this->find('app.cache'));
    }

    /**
     * Load config file to cache
     *
     * @param string $file
     *
     * @throws \Shy\Core\Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return array|false
     */
    public function load(string $file)
    {
        if ($this->has($file)) {
            return $this->get($file);
        }

        if (file_exists($file)) {
            if ($config = require "$file") {
                $this->set($file, $config);

                return $this->get($file);
            }
        }

        return false;
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

        $filename = array_shift($keyLevels);
        $configItem = null;

        if ($config = $this->load($this->envDir . $filename . '.php')) {
            $configItem = $this->getConfig($config, $keyLevels);
        }
        if (is_null($configItem)) {
            if ($config = $this->load($this->dir . $filename . '.php')) {
                $configItem = $this->getConfig($config, $keyLevels);
            }
        }

        return $configItem;
    }

    /**
     * Get config
     *
     * @param $config
     * @param array $keyLevels
     * @return array|string|null
     */
    protected function getConfig($config, $keyLevels)
    {
        $configItem = null;
        if (is_array($config)) {
            if (empty($keyLevels)) {
                $configItem = $config;
            } else {
                $configItem = get_array_key($keyLevels, $config);
            }
        }

        return $configItem;
    }
}
