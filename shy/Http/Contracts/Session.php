<?php

namespace Shy\Http\Contracts;

interface Session
{
    /**
     * Session Start
     */
    public function sessionStart();

    public function sessionId();

    public function exist($key);

    public function get($key);

    public function set($key, $val);

    public function token();

}
