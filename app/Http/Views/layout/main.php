<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title><?php param('title') ?></title>
    <link type="text/css" rel="stylesheet" href="/css/app.css">
    <?php push_resource('header-css') ?>
</head>
<body>
<?php include_sub_view() ?>
<?php include_view('component/footer') ?>
<?php push_resource('footer-js') ?>
</body>
</html>
