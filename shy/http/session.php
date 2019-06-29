<?php
/**
 * Session
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http;

use Workerman\Protocols\Http;

class session
{
    /**
     * Session Start
     */
    public function sessionStart()
    {
        if (config('IS_CLI')) {
            Http::sessionStart();
        } else {
            session_start();
        }
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
        if (config('IS_CLI')) {
            Http::sessionId();
        } else {
            return session_id();
        }
    }

}
