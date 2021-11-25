<?php

namespace Shy\Core\Contracts;

interface Config extends Cache
{
    /**
     * Load config file to cache
     *
     * @param string $file
     *
     * @throws \Shy\Core\Exception\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return array|false
     */
    public function load(string $file);

    /**
     * Find key in config file cache
     *
     * @param string $key
     *
     * @throws \Shy\Core\Exception\Cache\InvalidArgumentException
     *
     * @return string|array|null
     */
    public function find(string $key);
}
