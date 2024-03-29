<?php

namespace Shy\Logger;

use Aliyun_Log_Client;
use Aliyun_Log_Models_LogItem;
use Aliyun_Log_Models_PutLogsRequest;
use Exception;
use RuntimeException;
use Shy\Contract\Logger as LoggerContract;
use Shy\Http\Contract\Request;

class Aliyun extends File implements LoggerContract
{
    /**
     * Logger constructor.
     *
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * 记录日志
     * Log with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws Exception
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
