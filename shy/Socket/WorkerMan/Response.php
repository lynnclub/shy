<?php

namespace Shy\Socket\WorkerMan;

use Shy\Http\Response as HttpResponse;
use Shy\Http\Contracts\Response as ResponseContract;
use Workerman\Protocols\Http;

class Response extends HttpResponse implements ResponseContract
{
    /**
     * Send header.
     */
    public function sendHeader()
    {
        if (empty($this->reasonPhrase)) {
            $this->reasonPhrase = $this->getReasonPhrase();
        }

        Http::header($this->reasonPhrase);

        if (is_array($this->headers)) {
            foreach ($this->headers as $lowerKey => $value) {
                if (is_string($value)) {
                    Http::header($value);
                } else {
                    Http::header(key($value) . ': ' . current($value));
                }
            }
        }
    }

    /**
     * Initialize in cycle
     */
    public function initialize()
    {
        $this->statusCode = 200;
        $this->reasonPhrase = null;
        $this->headers = [
            'x-powered-by' => ['X-Powered-By' => 'Shy Framework ' . shy()->version() . '/PHP-CLI']
        ];
        $this->body = null;
    }
}
