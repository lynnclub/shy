<?php

namespace Shy\Http\Contracts;

interface Response
{
    /**
     * Initialize in cycle
     */
    public function initialize();

    /**
     * Set Response
     *
     * @param $response
     * @return $this
     */
    public function set($response);

    /**
     * Set Http Code
     *
     * @param int $code
     * @return $this
     */
    public function setCode(int $code);

    /**
     * Set Http Header
     *
     * @param array|string $header
     * @return $this
     */
    public function setHeader($header);

    /**
     * Send Response
     *
     * @param view|string $view
     */
    public function send($view = null);

}
