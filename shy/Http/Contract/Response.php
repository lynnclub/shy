<?php

namespace Shy\Http\Contract;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

interface Response extends ResponseInterface
{
    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value = '');

    public function withHeaders(array $headers);

    /**
     * 发送响应头
     * Send header.
     */
    public function sendHeader();

    /**
     * 输出响应
     * Output response
     *
     * @param view|string $view
     */
    public function output($view = null);

    /**
     * 循环初始化
     * Loop initialize
     */
    public function initialize();
}
