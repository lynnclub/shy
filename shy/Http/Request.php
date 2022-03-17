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
        return empty($this->server->count()) ? FALSE : TRUE;
    }

    /**
     * Get all parameters.
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
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo();
    }

    /**
     * Is Pjax
     *
     * @return bool
     */
    public function isPjax()
    {
        return $this->headers->get('X-PJAX') == TRUE;
    }

    /**
     * Expects Json
     *
     * @return bool
     */
    public function expectsJson()
    {
        return $this->isXmlHttpRequest() && !$this->isPjax();
    }

    /**
     * Get Header
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function header($key, $default = null)
    {
        return $this->headers->get($key, $default);
    }

    /**
     * Get Server
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function server($key, $default = null)
    {
        return $this->server->get($key, $default);
    }

    /**
     * Prepares the base URL.
     *
     * @return string
     */
    protected function prepareBaseUrl()
    {
        $baseUrl = parent::prepareBaseUrl();

        if (empty($baseUrl)) {
            $path = $this->server->get('PHP_SELF');
            if (false !== $pos = stripos($path, '/index.php')) {
                $baseUrl = substr($path, 0, $pos);
            }
        }

        return $baseUrl;
    }
}
