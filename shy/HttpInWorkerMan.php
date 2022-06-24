<?php

namespace Shy;

use Exception;
use Shy\Exception\HandlerRegister;
use Shy\Facade\Hook;
use Throwable;
use Workerman\Protocols\Http;
use Workerman\Worker;

class HttpInWorkerMan extends Worker
{
    /**
     * Virtual host to path mapping.
     *
     * @var array ['workerman.net'=>'/home', 'www.workerman.net'=>'home/www']
     */
    protected $serverRoot = array();

    /**
     * Mime mapping.
     *
     * @var array
     */
    protected static $mimeTypeMap = array();

    /**
     * Used to save user OnWorkerStart callback settings.
     *
     * @var callback
     */
    protected $_onWorkerStart = null;

    /**
     * @var $container \Shy\Container
     */
    protected $container;

    /**
     * Add virtual host.
     *
     * @param string $domain
     * @param string $config
     * @return void
     */
    public function addRoot($domain, $config)
    {
        if (is_string($config)) {
            $config = array('root' => $config);
        }
        $this->serverRoot[$domain] = $config;
    }

    /**
     * Construct.
     *
     * @param string $socket_name
     * @param array $context_option
     */
    public function __construct($socket_name, $context_option = array())
    {
        list(, $address) = explode(':', $socket_name, 2);
        parent::__construct('http:' . $address, $context_option);
        $this->name = 'WebServer';

        /**
         * Bootstrap In CLI
         */
        $this->container = require __DIR__ . '/../bootstrap/http_workerman.php';
    }

    /**
     * Run webserver instance.
     *
     * @see Workerman.Worker::run()
     */
    public function run()
    {
        $this->_onWorkerStart = $this->onWorkerStart;
        $this->onWorkerStart = array($this, 'onWorkerStart');
        $this->onMessage = array($this, 'onMessage');
        parent::run();
    }

    /**
     * Emit when process start.
     *
     * @throws Exception
     */
    public function onWorkerStart()
    {
        if (empty($this->serverRoot)) {
            Worker::safeEcho(new Exception('server root not set, please use WebServer::addRoot($domain, $root_path) to set server root path'));
            exit(250);
        }

        // Init mimeMap.
        $this->initMimeTypeMap();

        // Try to emit onWorkerStart callback.
        if ($this->_onWorkerStart) {
            try {
                ($this->_onWorkerStart)($this);
            } catch (Exception $e) {
                self::log($e);
                exit(250);
            } catch (\Error $e) {
                self::log($e);
                exit(250);
            }
        }

        $this->container->updateForkedProcessStartInfo($this->id);
    }

    /**
     * Init mime map.
     *
     * @return void
     */
    public function initMimeTypeMap()
    {
        $mime_file = Http::getMimeTypesFile();
        if (!is_file($mime_file)) {
            $this->log("$mime_file mime.type file not fond");
            return;
        }
        $items = file($mime_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($items)) {
            $this->log("get $mime_file mime.type content fail");
            return;
        }
        foreach ($items as $content) {
            if (preg_match("/\s*(\S+)\s+(\S.+)/", $content, $match)) {
                $mime_type = $match[1];
                $workerman_file_extension_var = $match[2];
                $workerman_file_extension_array = explode(' ', substr($workerman_file_extension_var, 0, -1));
                foreach ($workerman_file_extension_array as $workerman_file_extension) {
                    self::$mimeTypeMap[$workerman_file_extension] = $mime_type;
                }
            }
        }
    }

    /**
     * Emit when http message coming.
     *
     * @param \Workerman\Connection\TcpConnection $connection
     * @throws \Shy\Exception\Container\NotFoundException
     */
    public function onMessage($connection)
    {
        // REQUEST_URI.
        $workerman_url_info = parse_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        if (!$workerman_url_info) {
            Http::header('HTTP/1.1 400 Bad Request');
            $connection->close('<h1>400 Bad Request</h1>');
            return;
        }

        $workerman_path = isset($workerman_url_info['path']) ? $workerman_url_info['path'] : '/';

        $workerman_path_info = pathinfo($workerman_path);
        $workerman_file_extension = isset($workerman_path_info['extension']) ? $workerman_path_info['extension'] : '';
        if ($workerman_file_extension === '') {
            //$workerman_path = ($len = strlen($workerman_path)) && $workerman_path[$len - 1] === '/' ? $workerman_path . 'index.php' : $workerman_path . '/index.php';
            $workerman_path = 'index.php';
            $workerman_file_extension = 'php';
        }

        $workerman_siteConfig = isset($this->serverRoot[$_SERVER['SERVER_NAME']]) ? $this->serverRoot[$_SERVER['SERVER_NAME']] : current($this->serverRoot);
        $workerman_root_dir = $workerman_siteConfig['root'];
        $workerman_file = "$workerman_root_dir/$workerman_path";
        if (isset($workerman_siteConfig['additionHeader'])) {
            Http::header($workerman_siteConfig['additionHeader']);
        }

        // Try file.
        if ($workerman_file_extension === 'php' && !is_file($workerman_file)) {
            $workerman_file = "$workerman_root_dir/index.php";
            if (!is_file($workerman_file)) {
                $workerman_file = "$workerman_root_dir/index.html";
                $workerman_file_extension = 'html';
            }
        }

        // File exsits.
        if ($workerman_file_extension !== 'php' && is_file($workerman_file)) {
            // Security check.
            if (
                (!($workerman_request_realpath = realpath($workerman_file)) || !($workerman_root_dir_realpath = realpath($workerman_root_dir)))
                || 0 !== strpos($workerman_request_realpath, $workerman_root_dir_realpath)
            ) {
                Http::header('HTTP/1.1 400 Bad Request');
                $connection->close('<h1>400 Bad Request</h1>');
                return;
            }

            $workerman_file = realpath($workerman_file);

            // Send file to client.
            self::sendFile($connection, $workerman_file);
        } else {
            ob_start();

            // $_SERVER.
            $_SERVER['REMOTE_ADDR'] = $connection->getRemoteIp();
            $_SERVER['REMOTE_PORT'] = $connection->getRemotePort();

            /**
             * Run Shy Framework
             */
            try {
                // 循环信息 Loop info
                $this->container->set('HTTP_LOOP_COUNT', $this->container->get('HTTP_LOOP_COUNT') + 1);
                $this->container->set('HTTP_LOOP_START_TIME', microtime(TRUE));

                // 装载请求 Load the request
                $this->container['request']->initialize(
                    $_GET,
                    $_POST,
                    [],
                    $_COOKIE,
                    $_FILES,
                    $_SERVER,
                    file_get_contents('php://input')
                );

                // 启动会话
                $this->container['session']->init(config('session'));

                // 钩子-请求处理前
                Hook::run('request_before');

                // 处理请求，输出响应 Process the request and output the response
                $response = $this->container['router']->run($this->container['request']);
                if (method_exists($response, 'output')) {
                    $response->output();
                } else {
                    $this->container['response']->output($response);
                }

                // 钩子-响应后
                Hook::run('response_after');

                // 将请求恢复初始状态 Restore the request to original state
                $this->container['request']->initialize();
            } catch (Throwable $e) {
                $this->container->get(HandlerRegister::class)->handleException($e);
            }

            $content = ob_get_clean();
            if (strtolower($_SERVER['HTTP_CONNECTION']) === "keep-alive") {
                $connection->send($content);
            } else {
                $connection->close($content);
            }
        }
    }

    /**
     * Send file
     *
     * @param $connection
     * @param $file_path
     * @return mixed
     */
    public static function sendFile($connection, $file_path)
    {
        // Check 304.
        $info = stat($file_path);
        $modified_time = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $info) {
            // Http 304.
            if ($modified_time === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
                // 304
                Http::header('HTTP/1.1 304 Not Modified');
                // Send nothing but http headers..
                $connection->close('');
                return;
            }
        }

        // Http header.
        if ($modified_time) {
            $modified_time = "Last-Modified: $modified_time\r\n";
        }
        $file_size = filesize($file_path);
        $file_info = pathinfo($file_path);
        $extension = isset($file_info['extension']) ? $file_info['extension'] : '';
        $file_name = isset($file_info['filename']) ? $file_info['filename'] : '';
        $header = "HTTP/1.1 200 OK\r\n";
        if (isset(self::$mimeTypeMap[$extension])) {
            $header .= "Content-Type: " . self::$mimeTypeMap[$extension] . "\r\n";
        } else {
            $header .= "Content-Type: application/octet-stream\r\n";
            $header .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        }
        $header .= "Connection: keep-alive\r\n";
        $header .= $modified_time;
        $header .= "Content-Length: $file_size\r\n\r\n";
        $trunk_limit_size = 1024 * 1024;
        if ($file_size < $trunk_limit_size) {
            return $connection->send($header . file_get_contents($file_path), TRUE);
        }
        $connection->send($header, TRUE);

        // Read file content from disk piece by piece and send to client.
        $connection->fileHandler = fopen($file_path, 'r');
        $do_write = function () use ($connection) {
            // Send buffer not full.
            while (empty($connection->bufferFull)) {
                // Read from disk.
                $buffer = fread($connection->fileHandler, 8192);
                // Read eof.
                if ($buffer === '' || $buffer === FALSE) {
                    return;
                }
                $connection->send($buffer, TRUE);
            }
        };
        // Send buffer full.
        $connection->onBufferFull = function ($connection) {
            $connection->bufferFull = TRUE;
        };
        // Send buffer drain.
        $connection->onBufferDrain = function ($connection) use ($do_write) {
            $connection->bufferFull = FALSE;
            $do_write();
        };
        $do_write();
    }
}
