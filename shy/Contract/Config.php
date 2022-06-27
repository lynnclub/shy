<?php

namespace Shy\Contract;

use Exception;
use Shy\Exception\Cache\InvalidArgumentException;

interface Config extends Cache
{
    /**
     * 加载配置文件并缓存
     * Load config file and cache
     *
     * @param string $file
     * @return array|false
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function load(string $file);

    /**
     * 从配置文件缓存中查找键值
     * Find key in config file cache
     *
     * @param string $key
     * @return array|string|null
     *
     * @throws InvalidArgumentException
     */
    public function find(string $key);
}
