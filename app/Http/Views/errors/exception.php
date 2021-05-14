<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
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
            padding: 25px;
        }

        .message {
            padding: 10px 0;
            font-size: 30px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            padding-bottom: 10px;
        }

        .file {
            padding: 10px;
        }

        .function {
            padding: 10px 30px;
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
            padding: 5px;
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
        <div class="message"><?php echo $e->getMessage(); ?></div>
        <div>
            Flie: <?php echo $e->getFile(); ?>
        </div>
        <div>
            Line: <?php echo $e->getLine(); ?>
        </div>
        <div>
            Error Code: <?php echo $e->getCode(); ?>
        </div>
    </div>
    <div class="part">
        <div class="title">Trace:</div>
        <?php foreach ($e->getTrace() as $key => $trace) : ?>
            <div class="file">
                <?php echo ($key + 1) . '. '; ?>

                <?php
                if (isset($trace['file'], $trace['line'])) {
                    echo $trace['file'] . ' Line: ' . $trace['line'];
                } else {
                    echo 'none';
                }
                ?>
            </div>
            <div class="function">
                <?php
                if (isset($trace['class'])) {
                    echo $trace['class'] . '->';
                }

                if (isset($trace['args'])) {
                    foreach ($trace['args'] as $argKey => $arg) {
                        $trace['args'][$argKey] = '(' . gettype($arg) . ')';
                        if (is_string($arg)) {
                            if (strlen($arg) > 100) {
                                $arg = substr($arg, 0, 100) . '...';
                            }
                            $trace['args'][$argKey] .= "'{$arg}'";
                        } elseif (is_object($arg)) {
                            $trace['args'][$argKey] .= get_class($arg);
                        } elseif (is_array($arg)) {
                            $json = json_encode($arg, JSON_UNESCAPED_UNICODE);
                            if (strlen($json) > 100) {
                                $json = substr($json, 0, 100) . '...}';
                            }
                            $trace['args'][$argKey] .= $json;
                        }
                    }
                } else {
                    $trace['args'] = [];
                }

                echo $trace['function'] . '(' . implode(', ', $trace['args']) . ')';
                ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="part">
        <div class="title">GET Data:</div>
        <?php
        if (empty($_GET)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_GET as $key => $value) {
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">POST Data:</div>
        <?php
        if (empty($_POST)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_POST as $key => $value) {
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Files:</div>
        <?php
        if (empty($_FILES)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_FILES as $key => $value) {
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Cookies:</div>
        <?php
        if (empty($_COOKIE)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_COOKIE as $key => $value) {
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Session:</div>
        <?php
        if (empty($_SESSION)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_SESSION as $key => $value) {
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Constants:</div>
        <?php
        echo '<table class="params"><tbody>'
            . '<tr><td>BASE_PATH</td><td>' . (defined('BASE_PATH') ? BASE_PATH : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>APP_PATH</td><td>' . (defined('APP_PATH') ? APP_PATH : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>VIEW_PATH</td><td>' . (defined('VIEW_PATH') ? VIEW_PATH : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>CACHE_PATH</td><td>' . (defined('CACHE_PATH') ? CACHE_PATH : '<span class="empty">not defined</span>') . '</td></tr>'
            . '<tr><td>BASE_URL</td><td>' . (defined('BASE_URL') ? BASE_URL : '<span class="empty">not defined</span>') . '</td></tr>'
            . '</tbody></table>';
        ?>
    </div>
    <div class="part">
        <div class="title">Server:</div>
        <?php
        if (empty($_SERVER)) {
            echo '<div class="empty">empty</div>';
        } else {
            echo '<table class="params"><tbody>';

            foreach ($_SERVER as $key => $value) {
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
</div>
<footer>
    <a target="_blank" href="https://github.com/lynncho/shy">Shy Framework at Github</a>
</footer>
</body>
</html>
