<div id="hello-world">
    <?php param('info') ?>
</div>
<div id="system">
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Used Time: <?php echo microtime(true) - (IS_CLI ? $GLOBALS['_SHY_START'] : SHY_START); ?> second</p>
    <br>
    <p>Loaded instance's abstract: </p>
    <ol>
        <?php foreach (shy_list_memory_used() as $abstract => $memoryUsed) { ?>
            <li><?php echo $abstract . '  ' . $memoryUsed . ' kb'; ?></li>
        <?php } ?>
    </ol>
</div>
