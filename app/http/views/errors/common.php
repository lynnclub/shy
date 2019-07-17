<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>Http error</title>
    <link type="text/css" rel="stylesheet" href="<?php param('BASE_URL'); ?>css/app.css">
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

        .container > .title .message {
            padding: 10px 0;
            font-size: 30px;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="title">
        <div class="message"><?php echo $e->getMessage(); ?></div>
    </div>
</div>
</body>
</html>
