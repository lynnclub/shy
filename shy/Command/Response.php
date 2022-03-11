<?php

namespace Shy\Command;

use Shy\Http\Response as HttpResponse;
use Shy\Http\Contract\Response as ResponseContract;

class Response extends HttpResponse implements ResponseContract
{
    /**
     * Send header.
     */
    public function sendHeader()
    {
        //can no send header to PHP-CLI console
    }

    /**
     * Initialize in cycle
     */
    public function initialize()
    {
        $this->statusCode = 200;
        $this->reasonPhrase = null;
        $this->headers = [];
        $this->body = null;
    }
}
