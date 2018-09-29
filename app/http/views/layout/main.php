<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title ?></title>
    <link type="text/css" rel="stylesheet" href="<?php echo config('base_url'); ?>css/app.css">
</head>
<body>
<?php echo $this->getSubView(); ?>
<?php echo include_view(config('app', 'path') . 'http/views/component/footer'); ?>
</body>
</html>
