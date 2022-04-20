<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>Exception</title>
    <style>

        * {
            margin: 0;
            padding: 0;
            word-break: break-all;
            word-wrap: break-word;
        }

        body {
            font-family: Helvetica, Arial, "Hiragino Sans GB", "WenQuanYi Micro Hei", "Microsoft YaHei", sans-serif;
            font-size: 15px;
        }

        .container {
            margin: auto;
        }

        .part {
            margin: 25px 25px 30px;
        }

        .message {
            margin: 25px 0;
            font-size: 30px;
        }

        .message-tip {
            margin: 10px 0;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 15px;
        }

        .file {
            padding: 10px 15px;
        }

        .function {
            padding: 10px 0;
        }

        .params {
            width: 100%;
            font-size: 13px;
            border-spacing: 0 6px;
        }

        .params tr td:first-child {
            width: 40%;
            font-weight: bold;
        }

        .params tr td {
            padding: 5px 0;
            border-bottom: 1px solid rgba(34, 36, 38, 0.15);
        }

        .empty {
            color: #ccc;
            padding: 0 4px;
        }

        footer {
            margin: 50px auto;
            text-align: center;
        }

        footer > a {
            color: black;
            text-decoration: none;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="part">
        <div class="message">
            <?php echo $e->getMessage(); ?>
        </div>
        <div class="message-tip">
            Error Code: <?php echo $e->getCode(); ?>
        </div>
        <div class="message-tip">
            File: <?php echo $e->getFile() . ' line ' . $e->getLine(); ?>
        </div>
    </div>
    <div class="part">
        <div class="title">Trace</div>
        <?php foreach ($e->getTrace() as $key => $trace) : ?>
            <div class="function">
                <?php echo ($key + 1) . '. '; ?>

                <?php
                $argString = '';
                if (isset($trace['args'])) {
                    foreach ($trace['args'] as $argKey => $arg) {
                        if (is_string($arg)) {
                            if (strlen($arg) > 200) {
                                $arg = substr($arg, 0, 200) . '...';
                            }
                        } elseif (is_object($arg)) {
                            $arg = '(object)' . get_class($arg);
                        } elseif (is_array($arg)) {
                            $arg = json_encode($arg, JSON_UNESCAPED_UNICODE);
                            if (strlen($arg) > 200) {
                                $arg = substr($arg, 0, 200) . '...';
                            }

                            $arg = '(array)' . $arg;
                        }

                        $trace['args'][$argKey] = $arg;
                    }

                    $argString = implode(', ', $trace['args']);
                }

                if (isset($trace['class'])) {
                    echo $trace['class'] . '->';
                }

                echo $trace['function'] . '(' . $argString . ')';
                ?>
            </div>
            <div class="file">
                <?php
                if (isset($trace['file'], $trace['line'])) {
                    echo $trace['file'] . ' line ' . $trace['line'];
                } else {
                    echo '{anonymous}';
                }
                ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="part">
        <div class="title">GET</div>
        <?php
        if (empty($_GET)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_GET as $key => $value) {
                $value = is_string($value)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">POST</div>
        <?php
        if (empty($_POST)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_POST as $key => $value) {
                $value = is_string($value)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Files</div>
        <?php
        if (empty($_FILES)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_FILES as $key => $value) {
                $value = is_string($value)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Cookies</div>
        <?php
        if (empty($_COOKIE)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_COOKIE as $key => $value) {
                $value = is_string($value)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Session</div>
        <?php
        if (empty($_SESSION)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_SESSION as $key => $value) {
                $value = is_string($value)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Constants</div>
        <?php
        echo '<table class="params"><tbody>'
            . '<tr><td>BASE_PATH</td><td>' . (defined('BASE_PATH')
                ? BASE_PATH
                : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>APP_PATH</td><td>' . (defined('APP_PATH')
                ? APP_PATH
                : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>VIEW_PATH</td><td>' . (defined('VIEW_PATH')
                ? VIEW_PATH
                : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>CACHE_PATH</td><td>' . (defined('CACHE_PATH')
                ? CACHE_PATH
                : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>BASE_URL</td><td>' . (defined('BASE_URL')
                ? BASE_URL
                : '<span class="empty">not defined</span>') . '</td></tr>'
            . '</tbody></table>';
        ?>
    </div>
    <div class="part">
        <div class="title">Server</div>
        <?php
        if (empty($_SERVER)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_SERVER as $key => $value) {
                $value = is_string($value)
                    ? $value
                    : json_encode($value, JSON_UNESCAPED_UNICODE);

                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
</div>
<footer>
    <a target="_blank" href="https://github.com/lynnclub/shy">Shy Framework at Github</a>
</footer>
</body>
</html>
