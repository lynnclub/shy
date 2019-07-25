<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>{$title}</title>
    <link type="text/css" rel="stylesheet" href="{BASE_URL}css/app.css">
</head>
<body>
<div id="hello-world">
    {$info}
</div>
<div id="system">
    <p>Container Start Id: {container_id()}</p>
    <p>Memory Peak:{memory_get_peak_usage()/1024} kb</p>
    <p>Used Time: {microtime(true) - config('SHY_START_TIME')} second</p>
    {assign var="cycleStartTime" value="{config('SHY_CYCLE_START_TIME')}"}
    {if isset($cycleStartTime) && !empty($cycleStartTime)}
        <p>Cycle Used Time: {microtime(true) - $cycleStartTime} second</p>
    {/if}
    {assign var="cycleCount" value="{config('SHY_CYCLE_COUNT')}"}
    {if isset($cycleCount) && !empty($cycleCount)}
        <p>Cycle Count: {$cycleCount}</p>
    {/if}
    <br>
    <p>Loaded instance's abstract: </p>
    <ol>
        {foreach shy_list_memory_used() as $abstract => $instances}
            {foreach $instances as $key => $memoryUsed}
                <li>{$abstract}({$key + 1}) {$memoryUsed/1024} kb</li>
            {/foreach}
        {/foreach}
    </ol>
    <br>
    <p>Config:</p>
    <p class="var-dump">{var_dump(config_all())}</p>
</div>
{include file='component/footer.php'}
</body>
</html>
