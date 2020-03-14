<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>{$title}</title>
    <link type="text/css" rel="stylesheet" href="/css/app.css">
</head>
<body>
<div id="hello-world">
    {$info}
</div>
<div id="system">
    <h3>Basic Information:</h3>
    <br>
    <p>Container Start Id: {$shy->startId()}</p>
    <p>Memory Peak:{memory_get_peak_usage()/1024} kb</p>
    <p>Running Time: {microtime(true) - $shy->startTime()} second</p>
    {if $shy->has('SHY_CYCLE_START_TIME')}
        <p>Recycling Time: {microtime(true) - $shy->get('SHY_CYCLE_START_TIME')} second</p>
    {/if}
    <br>
    <h3>Loaded Instance Uses Memory:</h3>
    <br>
    <ol>
        {foreach $shy->memoryUsed() as $abstract => $memoryUsed}
            <li>{$abstract} {$memoryUsed/1024} kb</li>
        {/foreach}
    </ol>
    <br>
    <h3>Test Case:</h3>
    <br>
    <a href="/echo/string/with/middleware">Echo string with middleware</a>
    <br><br>
    <a href="/return/string/with/middleware">Return string with middleware</a>
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
{include file='component/footer.php'}
</body>
</html>
