<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shy framework</title>
    <link type="text/css" href="<?php echo BASE_URL; ?>css/test.css" rel="stylesheet">
</head>
<body>
<div id="hello-world">
    <?php if (isset($info)) echo $info; ?>
</div>
<div id="link">
    <a target="_blank" href="https://github.com/lynncho/shy">Shy framework</a>
</div>
</body>
</html>