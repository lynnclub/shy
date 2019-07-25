<?php

namespace Shy\Core\Contracts;

interface Config extends Cache
{
    /**
     * Load config file to cache
     *
     * @param string $filename
     *
     * @throws \Shy\Core\Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     *
     * @return bool
     */
    public function load(string $filename);

    /**
     * Find key in config file cache
     *
     * @param string $key
     * @param string $filename
     *
     * @throws \Shy\Core\Exceptions\Cache\InvalidArgumentException
     *
     * @return string|array|null
     */
    public function find(string $key, string $filename = 'app');

}
