<?php

namespace Shy\Socket\WorkerMan;

use Exception;
use Shy\Http\Session as HttpSession;
use Shy\Http\Contract\Session as SessionContract;
use Workerman\Protocols\Http;

class Session extends HttpSession implements SessionContract
{
    /**
     * 初始化
     *
     * @param array $config
     * @return $this
     *
     * @throws Exception
     */
    public function init(array $config = [])
    {
        // session驱动
        if (!empty($config['driver'])) {
            throw new Exception('WorkerMan not support session driver ' . $config['driver']);
        }

        if (isset($config['name'])) {
            Http::sessionName($config['name']);
        }

        if (isset($config['save_path'])) {
            Http::sessionSavePath($config['save_path']);
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
        return Http::sessionStarted();
    }

    /**
     * 启动
     *
     * @return bool
     */
    public function start()
    {
        return Http::sessionStart();
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

        return Http::sessionId($id);
    }

    /**
     * 关闭
     *
     * @return bool
     */
    public function close()
    {
        return Http::sessionWriteClose();
    }
}
