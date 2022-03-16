<?php

namespace Shy\Http;

use Shy\Http\Contract\Request as RequestContract;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest implements RequestContract
{
    /**
     * @return bool
     */
    public function isInitialized()
    {
        return empty($this->server->all()) ? FALSE : TRUE;
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
     * Get php://input
     */
    public function content()
    {
        return $this->content;
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
     * Get client IPs
     *
     * @return array
     */
    public function getClientIps()
    {
        $clientIps = array();

        $clientIps[] = $this->server->get('HTTP_X_FORWARDED_FOR');
        $clientIps[] = $this->server->get('HTTP_CLIENT_IP');
        $clientIps[] = $this->server->get('REMOTE_ADDR');

        return array_reverse(get_valid_ips($clientIps));
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
