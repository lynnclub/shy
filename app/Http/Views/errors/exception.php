<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>Error</title>
    <style>

        * {
            margin: 0;
            padding: 0;
            word-break: break-all;
            word-wrap: break-word;
        }

        body {
            font-family: "Microsoft YaHei", Helvetica, Arial, "Hiragino Sans GB", "WenQuanYi Micro Hei", sans-serif;
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
                        if (is_string($arg)) {
                            $trace['args'][$argKey] = "'{$arg}'";
                        } elseif (is_object($arg)) {
                            $trace['args'][$argKey] = 'object(' . get_class($arg) . ')';
                        } elseif (is_array($arg)) {
                            $trace['args'][$argKey] = json_encode($arg, JSON_UNESCAPED_UNICODE);
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
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
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
                echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
            }

            echo '</tbody></table>';
        }
        ?>
    </div>
    <div class="part">
        <div class="title">Env:</div>
        <?php
        $env = getenv('SHY_ENV');
        echo '<table class="params"><tbody>'
            . '<tr><td>SHY_ENV</td><td>' . (empty($env) ? 'develop' : $env) . '</td></tr>';

        foreach ($_ENV as $key => $value) {
            echo '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
        }

        echo '</tbody></table>';
        ?>
    </div>
    <div class="part">
        <div class="title">Shy Constants:</div>
        <?php
        echo '<table class="params"><tbody>'
            . '<tr><td>BASE_PATH</td><td>' . BASE_PATH . '</td></tr>'
            . '<tr><td>APP_PATH</td><td>' . APP_PATH . '</td></tr>'
            . '<tr><td>VIEW_PATH</td><td>' . VIEW_PATH . '</td></tr>'
            . '<tr><td>CACHE_PATH</td><td>' . CACHE_PATH . '</td></tr>'
            . '<tr><td>BASE_URL</td><td>' . BASE_URL . '</td></tr>'
            . '</tbody></table>';
        ?>
    </div>
</div>
<?php include_view('component/footer') ?>
</body>
</html>
