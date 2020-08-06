<?php

namespace Shy;

use Swoole\Http\Server as HttpServer;
use Throwable;
use Shy\Core\Exceptions\HandlerRegister;

class HttpInSwoole
{
    public function __construct($host, $option = array())
    {
        $http = new HttpServer($host, $option['port']);

        $http->set([
            'static_handler_locations' => ['/'],
        ]);

        /**
         * Bootstrap In CLI
         */
        require __DIR__ . '/../bootstrap/http-swoole.php';

        $http->on('start', function ($server) use ($host, $option) {
            shy()->addForkPid($server->worker_id);
            echo 'Swoole http server is started at ' . $host . ':' . $option['port'] . PHP_EOL;
        });

        $http->on('request', function ($request, $response) {
            global $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER;

            $_GET = empty($request->get) ? [] : $request->get;
            $_POST = empty($request->post) ? [] : $request->post;
            $_COOKIE = empty($request->cookie) ? [] : $request->cookie;
            $_FILES = empty($request->files) ? [] : $request->post;
            $_SERVER = empty($request->server) ? [] : $request->server;

            $stream = fopen('php://input', 'wb+');
            fwrite($stream, $request->rawContent());
            fclose($stream);

            //shy(Swoole\Http\Response::class, $response);

            ob_start();

            /**
             * Run Shy Framework
             */
            try {
                /**
                 * Cycle count
                 */
                shy()->set('SHY_CYCLE_COUNT', shy()->get('SHY_CYCLE_COUNT') + 1);
                /**
                 * Cycle start time
                 */
                shy()->set('SHY_CYCLE_START_TIME', microtime(TRUE));
                /**
                 * Run framework
                 */
                shy('request')->initialize($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
                //shy('session')->sessionStart();
                shy(Http::class)->run();
            } catch (Throwable $e) {
                shy(HandlerRegister::class)->handleException($e);
            }

            $response->status(200);
            $response->end(ob_get_clean());
        });

        $http->start();
    }
}
