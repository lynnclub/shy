<?php

namespace Shy\Http;

use Shy\Http\Contract\Request as RequestContract;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements RequestContract
{
    /**
     * 是否初始化
     *
     * @return bool
     */
    public function isInitialized()
    {
        return !empty($this->server->count());
    }

    /**
     * 获取所有参数
     * Get all params.
     *
     * @return array
     */
    public function all()
    {
        $query = $this->query->all();
        $request = $this->request->all();
        $attributes = $this->attributes->all();

        return array_merge($query, $request, $attributes);
    }

    /**
     * 是否Pjax请求
     * Is Pjax request
     *
     * @return bool
     */
    public function isPjax()
    {
        return $this->headers->get('X-PJAX') == TRUE;
    }

    /**
     * 是否期望响应json
     * Is expects response json
     *
     * @return bool
     */
    public function expectsJson()
    {
        return $this->isXmlHttpRequest() && !$this->isPjax();
    }

    /**
     * 获取请求头信息
     * Get header info
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function header(string $key, string $default = null)
    {
        return $this->headers->get($key, $default);
    }

    /**
     * 获取服务器信息
     * Get server info
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function server(string $key, string $default = null)
    {
        return $this->server->get($key, $default);
    }
}
