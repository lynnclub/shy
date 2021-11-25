<?php

namespace Shy\Http\Contract;

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
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value = '');

    public function withHeaders(array $headers);

    /**
     * Send header.
     */
    public function sendHeader();

    /**
     * Output
     *
     * @param view|string $view
     */
    public function output($view = null);

    /**
     * Initialize in cycle
     */
    public function initialize();
}
