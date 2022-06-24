<?php

namespace Shy\Contract;

use Exception;
use Shy\Exception\Cache\InvalidArgumentException;

interface Config extends Cache
{
    /**
     * Load config file to cache
     *
     * @param string $file
     *
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @return array|false
     */
    public function load(string $file);

    /**
     * Find key in config file cache
     *
     * @param string $key
     * @return string|array|null
     *
     * @throws InvalidArgumentException
     */
    public function find(string $key);
}
