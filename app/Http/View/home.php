<?php

push_resource('footer-js', '/vendor/jquery/dist/jquery.js', 'js');

?>

<div class="hello-world" style="margin-top: 50px">
    <?php param('info') ?>
</div>
<div class="hello-world">
    <?php param('infoEng') ?>
    <?php param('not_exist_param', true) ?>
</div>
<div class="system" style="margin-top: 50px">
    <h2>基础信息 Basic Information</h2>
    <br>
    <table class="params">
        <thead>
        <tr>
            <td>中文 Chinese</td>
            <td>英文 English</td>
            <td>值 Value</td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>容器启动id</td>
            <td>Container Start Id</td>
            <td><?php echo shy()->startId(); ?></td>
        </tr>
        <tr>
            <td>内存峰值</td>
            <td>Memory Peak</td>
            <td><?php echo memory_get_peak_usage() / 1024; ?> kb</td>
        </tr>
        <tr>
            <td>运行耗时</td>
            <td>Use Time</td>
            <td><?php echo microtime(true) - shy()->startTime(); ?> sec</td>
        </tr>
        <?php
        if (shy()->has('HTTP_LOOP_START_TIME')) { ?>
            <tr>
                <td>循环耗时</td>
                <td>Loop Use Time</td>
                <td><?php echo microtime(true) - shy()->get('HTTP_LOOP_START_TIME'); ?> sec</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>
<div class="system">
    <h2>实例化内存消耗记录 Memory consumption record when making</h2>
    <br>
    <table class="params">
        <thead>
        <tr>
            <td>序号 Num</td>
            <td>实例id Instance Id</td>
            <td>消耗内存 Memory Consumption</td>
        </tr>
        </thead>
        <tbody>
        <?php
        $instanceCount = 0;
        foreach (shy()->memoryUsed() as $abstract => $memoryUsed) {
            $instanceCount++;
            ?>
            <tr>
                <td><?php echo $instanceCount; ?></td>
                <td><?php echo $abstract; ?></td>
                <td><?php echo $memoryUsed / 1024; ?> kb</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<div class="system">
    <h2>测试用例 Test Case</h2>
    <br>
    <a href="/echo/string/with/middleware?do=test237897">输出字符串，途经中间件 Echo string, via middleware</a>
    <br><br>
    <a href="/return/string/with/middleware?do=test345">返回字符串，途经中间件 Return string, via middleware</a>
    <br><br>
    <a href="/middleware_stop/<?php echo random_code() ?>">中间件截停，输出路径参数 Middleware stop, echo path param</a>
    <br><br>
    <a href="/test/prefix/home">测试URL前缀 Test url prefix</a>
    <br><br>
    <a href="/test/prefix/return/string/with/get/param?do=test">返回GET参数字符串 Return GET param string</a>
    <br><br>
    <a href="/test/path/param/dj54hd2nx82m">输出路径参数 Test path param</a>
    <br><br>
    <a href="/smarty">使用Smarty模版 Use Smarty</a>
    <br><br>
    <a href="/controller_2/home">自定义控制器目录 Customer controller dir</a>
    <br><br>
    <a href="/controller_2/return/string/without/get/param">Return string without get param</a>
    <br><br>
    <a href="/testLang">测试语言函数-中文 Test lang() chinese</a>
    <br><br>
    <a href="/testLangUS">测试语言函数-英文 Test lang() english</a>
    <br><br>
    <a href="/not/found">产生不存在路径 404</a>
    <br><br>
    <a href="/test/error/500">产生错误 error</a>
    <br><br>
    <a href="/test/url/func">测试URL函数 Test url()</a>
    <br><br>
</div>
