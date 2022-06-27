<?php

namespace Shy\Http;

use InvalidArgumentException;
use Shy\Http\Contract\Response as ResponseContract;
use Shy\Http\Contract\View;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseContract
{
    /**
     * HTTP协议版本
     * HTTP protocol version
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * 状态码
     * The response status code.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * 状态码对应原因短语
     * Reason phrase corresponding to the status code.
     *
     * @var string
     */
    protected $reasonPhrase = null;

    /**
     * 响应头
     * Headers
     *
     * @var array $headers
     */
    protected $headers = [];

    /**
     * 响应体
     * Response body.
     *
     * @var StreamInterface $body
     */
    protected $body;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->headers = [
            'x-powered-by' => ['X-Powered-By' => 'Shy Framework ' . shy()->version()]
        ];
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if (is_numeric($version)) {
            $this->protocolVersion = $version;
        }

        return $this;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->statusCode = $code;
        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        $phraseList = [
            100 => '100 Continue.',
            101 => '101 Switching Protocols.',
            102 => '102 Processing.',
            103 => '103 Early Hints.',
            200 => '200 OK.',
            201 => '201 Created.',
            202 => '202 Accepted.',
            203 => '203 Non-Authoritative Information.',
            204 => '204 No Content.',
            205 => '205 Reset Content.',
            206 => '206 Partial Content.',
            207 => '207 Multi-Status.',
            208 => '208 Already Reported.',
            300 => '300 Multiple Choices.',
            301 => '301 Moved Permanently.',
            302 => '302 Found.',
            303 => '303 See Other.',
            304 => '304 Not Modified.',
            305 => '305 Use Proxy.',
            306 => '306 Unused.',
            307 => '307 Temporary Redirect.',
            308 => '308 Permanent Redirect.',
            400 => '400 Bad Request.',
            401 => '401 Unauthorized.',
            402 => '402 Payment Required.',
            403 => '403 Forbidden.',
            404 => '404 Not Found.',
            405 => '405 Method Not Allowed.',
            406 => '406 Not Acceptable.',
            407 => '407 Proxy Authentication Required.',
            408 => '408 Request Timeout.',
            409 => '409 Conflict.',
            410 => '410 Gone.',
            500 => '500 Internal Server Error.',
            501 => '501 Not Implemented.',
            502 => '502 Bad Gateway.',
            503 => '503 Service Unavailable.',
            504 => '504 Gateway Timeout.',
            505 => '505 HTTP Version Not Supported.',
            506 => '506 Variant Also Negotiates.',
            507 => '507 Insufficient Storage.',
            508 => '508 Loop Detected.',
            509 => '509 Unassigned.',
            510 => '510 Not Extended.',
            511 => '511 Network Authentication Required.',
        ];

        if (isset($phraseList[$this->statusCode])) {
            return 'HTTP/' . $this->protocolVersion . ' ' . $phraseList[$this->statusCode];
        }

        return '';
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        if (isset($this->headers[strtolower($name)])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name)
    {
        $name = strtolower($name);
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);
        if ($header) {
            return $name . ': ' . current($header);
        }

        return '';
    }

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
    public function withHeader($name, $value = '')
    {
        if (!is_string($name) || empty($name)) {
            throw new InvalidArgumentException('Invalid header names.');
        }

        if (empty($value)) {
            list($name, $value) = explode(':', $name, 2);
        }

        $name = trim($name);
        $this->headers[strtolower($name)] = [$name => trim($value)];

        return $this;
    }

    public function withHeaders(array $headers)
    {
        foreach ($headers as $key => $header) {
            if (is_string($key)) {
                $this->withHeader($key, $header);
            } else {
                $this->withHeader($header);
            }
        }

        return $this;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value)
    {
        if (!$this->hasHeader($name)) {
            $this->withHeader($name, $value);
        }

        return $this;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        if (is_null($name)) {
            $this->headers = [
                'x-powered-by' => ['X-Powered-By' => 'Shy Framework ' . shy()->version()]
            ];
        } else {
            unset($this->headers[$name]);
        }

        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        if (empty($this->body)) {
            $this->body = stream_for();
        }

        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * 发送响应头
     * Send header
     */
    public function sendHeader()
    {
        if (empty($this->reasonPhrase)) {
            $this->reasonPhrase = $this->getReasonPhrase();
        }

        header($this->reasonPhrase);

        if (is_array($this->headers)) {
            foreach ($this->headers as $value) {
                if (is_string($value)) {
                    header($value);
                } else {
                    header(key($value) . ': ' . current($value));
                }
            }
        }
    }

    /**
     * 输出响应
     * Output response
     *
     * @param view|string $view
     */
    public function output($view = null)
    {
        if (!is_null($view)) {
            if ($view instanceof View) {
                $this->body = $view->render();
            } else {
                $this->withBody(stream_for($view));
            }
        }

        $this->sendHeader();

        if (isset($this->body)) {
            echo $this->body;
        }

        $this->initialize();
    }

    /**
     * 循环初始化
     * Loop initialize
     */
    public function initialize()
    {
        $this->statusCode = 200;
        $this->reasonPhrase = null;
        $this->headers = [
            'x-powered-by' => ['X-Powered-By' => 'Shy Framework ' . shy()->version()]
        ];
        $this->body = null;
    }
}
