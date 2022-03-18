<?php

namespace Shy\Logger;

use Shy\Contract\Logger as LoggerContract;
use Shy\Http\Contract\Request;
use Shy\Contract\Config;
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
        $logItem = new Aliyun_Log_Models_LogItem;
        $logItem->pushBack('Message', $message);
        $logItem->pushBack('Level', strtoupper($level));
        $logItem->pushBack('Date', date('Y-m-d H:i:s'));
        $logItem->pushBack('Content', json_encode($context, JSON_UNESCAPED_UNICODE));

        if (method_exists($this->request, 'isInitialized') && $this->request->isInitialized()) {
            $logItem->pushBack('URL', $this->request->getUri());
            $logItem->pushBack('UA', $this->request->header('User-Agent'));
            $logItem->pushBack('Method', $this->request->getMethod());
            $logItem->pushBack('ClientIps', implode(',', $this->request->getClientIps()));
        }
        /**
         * Push
         */
        $putLogsRequest = new Aliyun_Log_Models_PutLogsRequest($config['project'], $config['logStore'], null, null, [$logItem]);
        $client->putLogs($putLogsRequest);

        parent::log($level, $message, $context);
    }
}
