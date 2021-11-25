<?php

namespace Shy\Core;

use Shy\Core\Cache\Memory;
use Shy\Core\Contracts\Config as ConfigContract;
use Shy\Core\Exception\Cache\InvalidArgumentException;
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
     * @throws Exception\Cache\InvalidArgumentException
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
     * @return array|false
     *
     * @throws \Shy\Core\Exception\Cache\InvalidArgumentException
     * @throws \Exception
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
     * @return string|array|null
     *
     * @throws Exception\Cache\InvalidArgumentException
     * @throws \Exception
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
            $configItem = get_array_key($keyLevels, $config);
        }
        if (is_null($configItem)) {
            if ($config = $this->load($this->dir . $filename . '.php')) {
                $configItem = get_array_key($keyLevels, $config);
            }
        }

        return $configItem;
    }
}
