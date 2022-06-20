<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="applicable-device" content="pc,mobile">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Oops~</title>
    <link rel="stylesheet" href="<?php param('BASE_URL') ?>vendor/font-awesome/css/font-awesome.min.css">
    <style>
        html, body, h1, div, li, p, input {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "PingFang SC", "Helvetica Neue", Helvetica, STHeiTi, sans-serif;
            line-height: 1.5;
        }

        .error {
            width: 100%;
            height: 100%;
            max-width: 400px;
            position: absolute;
            left: 50%;
            top: 40%;
            display: flex;
            -webkit-box-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            flex-direction: column;
            align-items: center;
            transform: translate(-50%, -50%);
        }

        .content {
            padding: 25px;
        }

        .content i {
            font-size: 50px;
        }

        .content span {
            font-size: 30px;
        }

        .error-message {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .error-right h1 {
            font-size: 30px;
        }

        a.return, a.return:hover {
            border: 1px solid black;
            color: black;
            display: flex;
            -webkit-box-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            align-items: center;
            padding: 5px 10px;
            margin-top: 8%;
            width: auto;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="error">
    <div class="content">
        <i class="fa fa-anchor" aria-hidden="true"></i>
    </div>
    <p class="error-message"><?php echo $e->getMessage() ?></p>
    <a class="return" href="<?php param('BASE_URL') ?>">
        <i class="fa fa-reply" aria-hidden="true"></i>
        &nbsp;Home
    </a>
</div>
</body>
</html>
