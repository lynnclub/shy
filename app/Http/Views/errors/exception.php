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

        .container > .title, .container .trace {
            padding: 30px;
        }

        .container > .title .message {
            padding: 10px 0;
            font-size: 30px;
        }

        .trace .title {
            font-size: 20px;
            padding-bottom: 10px;
        }

        .trace .file {
            padding: 10px;
        }

        .trace .function {
            padding: 10px 30px;
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
    <div class="title">
        <div class="message"><?php echo $e->getMessage(); ?></div>
        <div class="file">
            Flie: <?php echo $e->getFile(); ?>
        </div>
        <div class="file">
            Line: <?php echo $e->getLine(); ?>
        </div>
        <div class="file">
            Error Code: <?php echo $e->getCode(); ?>
        </div>
    </div>
    <div class="trace">
        <div class="title">Trace:</div>
        <?php
        foreach ($e->getTrace() as $key => $trace) {
            ?>
            <div class="file">
                <?php
                echo '[' . $key . '] ';
                if (isset($trace['file'], $trace['line'])) {
                    echo $trace['file'] . ' ' . $trace['line'];
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
                        if (is_object($arg)) {
                            $trace['args'][$argKey] = '(object)' . get_class($arg);
                        } elseif (is_array($arg)) {
                            $trace['args'][$argKey] = '(array)' . json_encode($arg);
                        }
                    }
                } else {
                    $trace['args'] = [];
                }
                echo $trace['function'] . '(' . implode(', ', $trace['args']) . ')';
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>
</body>
</html>
