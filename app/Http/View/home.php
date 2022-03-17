<?php

push_resource('footer-js', '/vendor/jquery/dist/jquery.js', 'js');

?>

<div id="hello-world">
    <?php param('info') ?>
    <?php param('not_exist_param', true) ?>
</div>
<div id="system">
    <h3>Basic Information:</h3>
    <br>
    <p>Container Start Id: <?php echo shy()->startId(); ?></p>
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Running Time: <?php echo microtime(true) - shy()->startTime(); ?> second</p>
    <?php
    if (shy()->has('HTTP_LOOP_START_TIME')) { ?>
        <p>Recycling Time: <?php echo microtime(true) - shy()->get('HTTP_LOOP_START_TIME'); ?> second</p>
        <?php
    }
    ?>
    <br>
    <h3>Loaded Instance Uses Memory:</h3>
    <br>
    <ol>
        <?php
        $instanceCount = 0;
        foreach (shy()->memoryUsed() as $abstract => $memoryUsed) {
            $instanceCount++;
            ?>
            <li><?php echo $abstract . ' ' . $memoryUsed / 1024 . ' kb'; ?></li>
        <?php } ?>
    </ol>
    <br>
    <h3>Test Case:</h3>
    <br>
    <a href="/echo/string/with/middleware?do=test237897">Echo string with middleware</a>
    <br><br>
    <a href="/return/string/with/middleware?do=test345">Return string with middleware</a>
    <br><br>
    <a href="/middleware_stop/<?php echo random_code() ?>">middleware path param</a>
    <br><br>
    <a href="/test/prefix/home">Test url prefix</a>
    <br><br>
    <a href="/test/prefix/return/string/with/get/param?do=test">Return string with get param</a>
    <br><br>
    <a href="/test/path/param/dj54hd2nx82m">Test path param</a>
    <br><br>
    <a href="/smarty">Smarty</a>
    <br><br>
    <a href="/controller_2/home">Customer controller dir</a>
    <br><br>
    <a href="/controller_2/return/string/without/get/param">Return string without get param</a>
    <br><br>
    <a href="/controller_2/smarty">Controller_2 smarty</a>
    <br><br>
    <a href="/testLang">Test lang chinese</a>
    <br><br>
    <a href="/testLangUS">Test lang english</a>
    <br><br>
    <a href="/not/found">404</a>
    <br><br>
    <a href="/test/error/500">error</a>
    <br><br>
    <a href="/test/url/func">Test url()</a>
    <br><br>
</div>
