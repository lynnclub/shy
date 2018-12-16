<?php

/**
 * Session
 */

namespace shy\core\library;

use Workerman\Protocols\Http;

class session
{
    protected $last_cycle_count = 0;

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
        if (IS_CLI) {
            Http::sessionId();
        } else {
            return session_id();
        }
    }
}