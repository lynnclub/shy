<?php

namespace Shy\Http;

use Shy\Http\Contract\Session as SessionContract;

class Session implements SessionContract
{
    /**
     * Session Start
     *
     * @return bool
     */
    public function sessionStart()
    {
        return session_start();
    }

    /**
     * @return string
     */
    public function sessionId()
    {
        return session_id();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exist(string $key)
    {
        if (isset($_SESSION[$key])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get(string $key)
    {
        if ($this->exist($key)) {
            return $_SESSION[$key];
        }

        return FALSE;
    }

    /**
     * @param string $key
     * @param $val
     */
    public function set(string $key, $val)
    {
        $_SESSION[$key] = $val;
    }

    /**
     * @param string $key
     */
    public function delete(string $key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Session token
     *
     * @param string $name
     * @return string
     */
    public function token(string $name = '')
    {
        if (empty($name)) {
            $name = '__token';
        }

        return $_SESSION[$name] = uniqid();
    }
}
