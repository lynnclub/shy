<?php

namespace Shy\Http\Contracts;

interface Request
{
    /**
     * Initialize request.
     *
     * @param array $query $_GET
     * @param array $request $_POST
     * @param array $cookies $_COOKIE
     * @param array $files $_FILES
     * @param array $server $_SERVER
     * @param string $content php://input
     */
    public function initialize(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null);

    /**
     * Is request initialized.
     *
     * @return bool
     */
    public function isInit();

    /**
     * Set is initialized false.
     */
    public function setInitFalse();

    /**
     * Get a parameter.
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null);

    /**
     * Get all parameters.
     *
     * @return array
     */
    public function all();

    /**
     * Get php://input
     */
    public function content();

}
