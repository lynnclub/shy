<div id="hello-world">
    <?php param('info') ?>
</div>
<div id="system">
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Used Time: <?php echo microtime(true) - SHY_START; ?> second</p>
</div>
