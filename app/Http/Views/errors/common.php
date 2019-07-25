<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>Http error</title>
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

        .container .message, .container .return {
            padding: 50px;
            font-size: 20px;
            text-align: center;
        }

        .container .return a {
            text-decoration: none;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="message"><?php echo $e->getMessage(); ?></div>
    <div class="return">
        <a href="<?php param('BASE_URL') ?>">Back Home</a>
    </div>
</div>
</body>
</html>
