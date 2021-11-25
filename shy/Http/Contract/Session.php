<?php

namespace Shy\Http\Contract;

interface Session
{
    public function sessionStart();

    /**
     * @return string
     */
    public function sessionId();

    /**
     * @param string $key
     * @return bool
     */
    public function exist(string $key);

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param $val
     */
    public function set(string $key, $val);

    /**
     * @param string $key
     */
    public function delete(string $key);

    /**
     * @param string $name
     * @return string
     */
    public function token(string $name);
}
