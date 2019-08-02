<?php

namespace Shy\Core\Logger;

use Shy\Core\Contracts\Logger as LoggerContract;
use Shy\Http\Contracts\Request;
use Shy\Core\Contracts\Config;
use RuntimeException;
use Aliyun_Log_Client;
use Aliyun_Log_Models_LogItem;
use Aliyun_Log_Models_PutLogsRequest;

class Aliyun extends File implements LoggerContract
{
    /**
     * Logger constructor.
     *
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Config $config, Request $request = null)
    {
        parent::__construct($config, $request);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        $config = config('aliyun_log');
        if (!isset($config['endPoint'], $config['accessId'], $config['accessKey'], $config['project'], $config['logStore'])) {
            throw new RuntimeException('Aliyun log config error.');
        }
        $client = shy(Aliyun_Log_Client::class, null, $config['endPoint'], $config['accessId'], $config['accessKey']);
        /**
         * Log Item
         */
        $logItem = shy(Aliyun_Log_Models_LogItem::class);
        $logItem->pushBack('message', $message);
        $logItem->pushBack('Level', $level);
        $logItem->pushBack('Date', date('Y-m-d H:i:s'));
        $logItem->pushBack('Content', json_encode($context));
        $request = shy(Request::class);
        if (is_object($request) && $request->isInitialized()) {
            $logItem->pushBack('Url', $request->getUrl());
            $logItem->pushBack('UA', $_SERVER['HTTP_USER_AGENT']);
            $logItem->pushBack('ClientIp', implode(',', $request->getClientIps()));
        }
        /**
         * Push
         */
        $putLogsRequest = shy(Aliyun_Log_Models_PutLogsRequest::class, null, $config['project'], $config['logStore'], null, null, [$logItem]);
        $client->putLogs($putLogsRequest);

        parent::log($level, $message, $context);
    }

}
