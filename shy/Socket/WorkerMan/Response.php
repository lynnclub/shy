<?php

namespace Shy\Socket\WorkerMan;

use Shy\Http\Response as HttpResponse;
use Shy\Http\Contracts\Response as ResponseContract;
use Workerman\Protocols\Http;

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
        if ($this->code) {
            Http::header($this->httpCodeMessage($this->code));
        }

        if (is_array($this->header)) {
            foreach ($this->header as $value) {
                Http::header($value);
            }
        }
    }
}
