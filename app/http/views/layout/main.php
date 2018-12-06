<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php param('title') ?></title>
    <link type="text/css" rel="stylesheet" href="<?php echo config('base_url') ?>css/app.css">
</head>
<body>
<?php include_sub_view() ?>
<?php include_view(config('app', 'path') . 'http/views/component/footer') ?>
</body>
</html>
