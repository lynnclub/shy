<?php

namespace Shy\Socket\WorkerMan;

use Shy\Http\Contracts\Session as SessionContract;
use Workerman\Protocols\Http;

class Session implements SessionContract
{
    /**
     * Session Start
     */
    public function sessionStart()
    {
        Http::sessionStart();
    }

    /**
     * Session exist
     *
     * @param $key
     * @return mixed
     */
    public function exist($key)
    {
        if (isset($_SESSION[$key])) {
            return true;
        } else {
            return false;
        }
    }

    public function get($key)
    {
        if ($this->exist($key)) {
            return $_SESSION[$key];
        }
        return false;
    }

    public function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }

    public function sessionId()
    {
        return Http::sessionId();
    }

}
