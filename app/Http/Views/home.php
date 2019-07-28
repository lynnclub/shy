<?php

push_resource('footer-js', [get_param('BASE_URL') . 'vendor/jquery/dist/jquery.js', ''], 'js');

?>

<div id="hello-world">
    <?php param('info') ?>
    <?php param('not_exist_param', true) ?>
</div>
<div id="system">
    <p>Container Start Id: <?php echo shy()->startId(); ?></p>
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Running Time: <?php echo microtime(true) - shy()->startTime(); ?> second</p>
    <?php
    if (shy()->has('SHY_CYCLE_START_TIME')) { ?>
        <p>Recycling Time: <?php echo microtime(true) - shy()->get('SHY_CYCLE_START_TIME'); ?> second</p>
        <?php
    }
    ?>
    <br>
    <p>Loaded instances memory used: </p>
    <ul>
        <?php
        $instanceCount = 0;
        foreach (shy()->memoryUsed() as $abstract => $instances) {
            foreach ($instances as $key => $memoryUsed) {
                $instanceCount++;
                ?>
                <li><?php echo '[' . $instanceCount . '] ' . $abstract . '(' . ($key + 1) . ') ' . $memoryUsed / 1024 . ' kb'; ?></li>
            <?php }
        } ?>
    </ul>
</div>
