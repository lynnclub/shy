<?php

push_resource('footer-js', [get_param('BASE_URL') . 'vendor/jquery/dist/jquery.js', ''], 'js');

?>

<div id="hello-world">
    <?php param('info') ?>
    <?php param('not_exist_param', true) ?>
</div>
<div id="system">
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Used Time: <?php echo microtime(true) - config('SHY_START_TIME'); ?> second</p>
    <?php
    $cycleStartTime = config('SHY_CYCLE_START_TIME');
    if (isset($cycleStartTime) && !empty($cycleStartTime)) { ?>
        <p>Cycle Used Time: <?php echo microtime(true) - $cycleStartTime; ?> second</p>
        <?php
    }
    ?>
    <?php
    $cycleCount = config('SHY_CYCLE_COUNT');
    if (isset($cycleCount) && !empty($cycleCount)) { ?>
        <p>Cycle Count: <?php echo $cycleCount; ?></p>
        <?php
    }
    ?>
    <br>
    <p>Loaded instances memory used: </p>
    <ul>
        <?php
        $instanceCount = 0;
        foreach (shy_list_memory_used() as $abstract => $instances) {
            foreach ($instances as $key => $memoryUsed) {
                $instanceCount++;
                ?>
                <li><?php echo '[' . $instanceCount . '] ' . $abstract . '(' . ($key + 1) . ') ' . $memoryUsed / 1024 . ' kb'; ?></li>
            <?php }
        } ?>
    </ul>
    <br>
    <p>Config:</p>
    <p class="var-dump"><?php var_dump(config_all()); ?></p>
</div>
