<?php

namespace Shy\Facade;

use Shy\Contract\Logger as LoggerContract;
use Shy\Facade;

/**
 * Class Logger
 * @package Shy\Facade
 *
 * @method static emergency($message, array $context = array())
 * @method static alert($message, array $context = array())
 * @method static critical($message, array $context = array())
 * @method static error($message, array $context = array())
 * @method static warning($message, array $context = array())
 * @method static notice($message, array $context = array())
 * @method static info($message, array $context = array())
 * @method static debug($message, array $context = array())
 */
class Logger extends Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(LoggerContract::class);
    }
}
