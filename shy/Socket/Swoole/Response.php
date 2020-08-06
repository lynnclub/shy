<?php

namespace Shy\Socket\Swoole;

use Shy\Http\Response as HttpResponse;
use Shy\Http\Contracts\Response as ResponseContract;
use Swoole\Http\Response as SwooleResponse;

class Response extends HttpResponse implements ResponseContract
{
    /**
     * Initialize in cycle
     */
    public function initialize()
    {
        $this->code = null;
        $this->header = ['X-Powered-By: Shy Framework ' . shy()->version() . '/PHP-CLI'];
        $this->response = '';
    }

    /**
     * Send header
     */
    public function sendHeader()
    {
        $response = shy(SwooleResponse::class);

        if ($this->code) {
            //$response->status($this->code, $this->httpCodeMessage($this->code));
        }

        if (is_array($this->header)) {
            foreach ($this->header as $value) {
                list($key, $value) = array_pad(explode(':', $value), 2, '');
                //$response->header(trim($key), trim($value));
            }
        }
    }
}
