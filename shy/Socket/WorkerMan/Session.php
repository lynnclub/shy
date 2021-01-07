<?php

namespace Shy\Socket\WorkerMan;

use Shy\Http\Session as HttpSession;
use Shy\Http\Contracts\Session as SessionContract;
use Workerman\Protocols\Http;

class Session extends HttpSession implements SessionContract
{
    /**
     * @return bool
     */
    public function sessionStart()
    {
        return Http::sessionStart();
    }

    /**
     * @return string
     */
    public function sessionId()
    {
        return Http::sessionId();
    }
}
