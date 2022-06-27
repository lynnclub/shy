<?php

namespace Shy\Http\Contract;

interface Session
{
    /**
     * 启动
     *
     * @return bool
     */
    public function start();

    /**
     * 唯一标识
     *
     * @param string $id
     * @return string
     */
    public function sessionId(string $id = '');

    /**
     * 是否存在
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key);

    /**
     * 读取
     *
     * @param string $key
     * @return mixed|false
     */
    public function get(string $key);

    /**
     * 写入
     *
     * @param string $key
     * @param mixed $data
     * @return bool
     */
    public function set(string $key, $data);

    /**
     * 口令
     *
     * @param string $key
     * @return string
     */
    public function token(string $key = '');

    /**
     * 删除
     *
     * @param string $key
     */
    public function delete(string $key);

    /**
     * 关闭
     *
     * @return bool
     */
    public function close();

    /**
     * 销毁
     *
     * @return bool
     */
    public function destroy();
}
