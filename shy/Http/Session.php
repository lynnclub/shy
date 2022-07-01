<?php

namespace Shy\Http;

use Exception;
use Shy\Http\Contract\Session as SessionContract;

class Session implements SessionContract
{
    /**
     * 初始化
     *
     * @param array $config
     * @return $this
     * @throws Exception
     */
    public function init(array $config = [])
    {
        // session驱动
        if (!empty($config['driver'])) {
            $class = false !== strpos($config['driver'], '\\')
                ? $config['driver']
                : '\\Shy\\Http\\Session\\' . ucwords($config['driver']);

            // 检查驱动类
            if (!class_exists($class)
                || !session_set_save_handler(new $class($config))
            ) {
                throw new Exception('error session handler:' . $class);
            }
        }

        if (isset($config['name'])) {
            session_name($config['name']);
        }

        if (isset($config['save_path'])) {
            session_save_path($config['save_path']);
        }

        if (isset($config['use_trans_sid'])) {
            ini_set('session.use_trans_sid', $config['use_trans_sid'] ? 1 : 0);
        }

        if (isset($config['use_cookies'])) {
            ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
        }

        if (isset($config['only_cookies'])) {
            ini_set('session.use_only_cookies', $config['only_cookies'] ? 1 : 0);
        }

        if (isset($config['domain'])) {
            ini_set('session.cookie_domain', $config['domain']);
        }

        if (isset($config['expire'])) {
            ini_set('session.gc_maxlifetime', $config['expire']);
            ini_set('session.cookie_lifetime', $config['expire']);
        }

        if (isset($config['secure'])) {
            ini_set('session.cookie_secure', $config['secure']);
        }

        if (isset($config['samesite'])) {
            ini_set('session.cookie_samesite', $config['samesite']);
        }

        if (isset($config['httponly'])) {
            ini_set('session.cookie_httponly', $config['httponly']);
        }

        if (isset($config['cache_limiter'])) {
            session_cache_limiter($config['cache_limiter']);
        }

        if (isset($config['cache_expire'])) {
            session_cache_expire($config['cache_expire']);
        }

        // 自动启动
        if (!empty($config['auto_start']) && !$this->isStart()) {
            ini_set('session.auto_start', 0);

            $this->start();
        }

        return $this;
    }

    /**
     * 是否启动
     *
     * @return bool
     */
    public function isStart()
    {
        return PHP_SESSION_ACTIVE === session_status();
    }

    /**
     * 启动
     *
     * @return bool
     */
    public function start()
    {
        return session_start();
    }

    /**
     * 唯一标识
     *
     * @param string $id
     * @return string
     */
    public function sessionId(string $id = '')
    {
        !$this->isStart() && $this->start();

        return $id ? session_id($id) : session_id();
    }

    /**
     * 是否存在
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        !$this->isStart() && $this->start();

        if (isset($_SESSION[$key])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 读取
     *
     * @param string $key
     * @return mixed|false
     */
    public function get(string $key)
    {
        !$this->isStart() && $this->start();

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return FALSE;
    }

    /**
     * 写入
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    public function set(string $key, $data)
    {
        !$this->isStart() && $this->start();

        $_SESSION[$key] = $data;

        return TRUE;
    }

    /**
     * 口令
     *
     * @param string $key
     * @return string
     */
    public function token(string $key = '')
    {
        !$this->isStart() && $this->start();

        if (empty($key)) {
            $key = '__token';
        }

        return $_SESSION[$key] = uniqid('', true);
    }

    /**
     * 删除
     *
     * @param string $key
     */
    public function delete(string $key)
    {
        !$this->isStart() && $this->start();

        unset($_SESSION[$key]);
    }

    /**
     * 关闭
     *
     * @return bool
     */
    public function close()
    {
        return session_write_close();
    }

    /**
     * 销毁
     *
     * @return bool
     */
    public function destroy()
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
        }

        session_unset();
        session_destroy();

        return TRUE;
    }
}
