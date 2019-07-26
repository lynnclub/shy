<?php

namespace Shy\Http\Contracts;

interface Session
{
    /**
     * Session Start
     */
    public function sessionStart();

    /**
     * Session exist
     *
     * @param $key
     * @return mixed
     */
    public function exist($key);

    public function get($key);

    public function set($key, $val);

    public function sessionId();

}
