<?php

namespace Shy\Http\Contract;

use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\View;

interface Router
{
    /**
     * @return string
     */
    public function getPathInfo();

    /**
     * @return string
     */
    public function getController();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return array
     */
    public function getMiddleware();

    /**
     * Pipeline Handle
     *
     * @param \Closure $next
     * @param Request
     * @return string|View
     */
    public function handle($next, RequestContract $request);
}