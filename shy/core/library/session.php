<?php

/**
 * Session
 */

namespace shy\core\library;

class session
{
    static private $_instance;

    private $session_id;

    private function __construct()
    {
        session_start();
    }

    private function __clone()
    {
        // not allow clone outside
    }

    public static function instance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * session
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
        if (empty($this->session_id)) {
            $this->session_id = session_id();
        }
        return $this->session_id;
    }
}