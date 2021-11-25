<?php

namespace Shy\Http\Exception;

use RuntimeException;
use Exception;

/**
 * HttpException.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class HttpException extends RuntimeException
{
    private $statusCode;
    private $headers;

    public function __construct(int $statusCode, string $message = null, Exception $previous = null, array $headers = array(), int $code = 0)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}
