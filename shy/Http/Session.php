<?php

namespace Shy\Http;

use Shy\Http\Contracts\Session as SessionContract;

class Session implements SessionContract
{
    /**
     * Session Start
     */
    public function sessionStart()
    {
        session_start();
    }

    public function sessionId()
    {
        return session_id();
    }

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

    public function token()
    {
        $_SESSION['__token'] = uniqid();
    }

}
