<?php
/**
 * 进程管理
 * Process
 *
 * 主进程(监视) -> 子进程
 * master(monitor) -> task
 */

namespace Shy;

use Exception;
use Shy\Contract\ProcessTask;
use Shy\Facade\Hook;

class Process
{
    /**
     * 任务
     *
     * @var array
     */
    private $task;

    /**
     * 任务数量
     *
     * @var array
     */
    private $taskNum;

    /**
     * 主进程pid
     *
     * @var int
     */
    protected $masterPid = 0;

    /**
     * pid文件路径
     *
     * @var string
     */
    protected $pidFile = '';

    /**
     * 状态统计文件路径
     *
     * @var string
     */
    protected $statisticsFile;

    /**
     * 是否后台常驻
     *
     * @var bool
     */
    protected $daemon = false;

    /**
     * 是否强制停止
     *
     * @var bool
     */
    protected $forceStop = false;

    /**
     * 子进程pid地图
     *
     * @var array
     */
    protected $childRunningPidMap = [];

    /**
     * 复制子进程
     *
     * @var ProcessTask
     */
    protected $forkedTask;

    /**
     * 进程状态
     * Status
     *
     * @var int
     */
    const STATUS_STARTING = 1;
    const STATUS_RUNNING = 2;
    const STATUS_STOPPING = 3;

    protected $status = self::STATUS_STARTING;

    /**
     * 状态加载等待时间
     *
     * @var int second
     */
    public $statusLoadingTime = 3;

    /**
     * 状态刷新时间间隔
     *
     * @var int 秒 second
     */
    public $statusShowRefresh = 10;

    /**
     * 设置任务
     * Set task
     *
     * @param string $name
     * @param int $processNum
     * @param ProcessTask $task
     */
    public function setTask(string $name, int $processNum, ProcessTask $task)
    {
        $this->task[$name] = $task;
        $this->taskNum[$name] = $processNum;
    }

    /**
     * 执行
     * Run
     *
     * @return string
     * @throws Exception
     */
    public function run()
    {
        if (\PHP_SAPI !== 'cli') {
            return 'Only run in command line mode';
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            return 'Can not run in windows';
        }

        if (is_string($errorMsg = $this->parseOperation())) {
            return $errorMsg;
        }

        $this->status = self::STATUS_STARTING;

        // 主进程后台常驻
        // Master daemon
        if ($this->daemon) {
            $this->daemon();
        }

        // 进程间通信
        // Inter Process communication
        $this->installSignal();

        // 保存主进程pid
        // Save master pid
        $this->masterPid = \posix_getpid();
        if (false === \file_put_contents($this->pidFile, $this->masterPid)) {
            throw new Exception('Can not save master pid to ' . $this->pidFile);
        }

        // 复制任务进程
        // Fork task process
        $this->forkAndRunTask();

        // 监控
        // Monitor
        $this->monitorForLinux();
    }

    /**
     * 解析操作
     * Parse operation
     *
     * @return string
     */
    protected function parseOperation()
    {
        global $argv;
        $entryCommand = $argv[0];
        $this->pidFile = CACHE_PATH . 'app/' . $entryCommand . '.pid';
        $this->statisticsFile = \sys_get_temp_dir() . "/$entryCommand.status";

        $available_operation = array(
            'start',
            'stop',
            'restart',
            'status',
        );
        $usage = "Usage: php command <command> <operation> [mode]\nOperations: \n\nstart\t\tProcess start.\t\n\t\tUse mode -d to start in DAEMON mode.\n\nstop\t\tGraceful stop.\t\n\t\tUse mode -f to force stop.\n\t\nrestart\t\tGraceful stop and restart.\t\n\t\tUse mode -f to force stop.\t\n\t\tUse mode -d to start in DAEMON mode.\n\nstatus\t\tShow status.";
        if (!isset($argv[1]) || !\in_array($argv[1], $available_operation)) {
            if (isset($argv[1])) {
                $usage = 'Unknown operation: ' . $argv[1] . "\n" . $usage;
            }

            return $usage;
        }

        $command = \trim($argv[1]);
        $mode = $argv[2] ?? '';

        // 获取主进程pid
        // Get master process pid.
        $master_pid = \is_file($this->pidFile) ? \file_get_contents($this->pidFile) : 0;
        $master_is_alive = $master_pid && \posix_kill($master_pid, 0) && \posix_getpid() !== $master_pid;

        // 主进程是否存活
        // Master is still alive?
        if ($master_is_alive) {
            if ($command === 'start') {
                return "[$entryCommand] already running";
            }
        } elseif ($command !== 'start' && $command !== 'restart') {
            return "[$entryCommand] not run";
        }

        // 执行命令 execute command.
        switch ($command) {
            case 'start':
                if ($mode === '-d') {
                    $this->daemon = true;
                }
                break;
            case 'status':
                while (1) {
                    if (\is_file($this->statisticsFile)) {
                        @\unlink($this->statisticsFile);
                    }

                    echo "\nLoading...\n";

                    // 主进程将发送SIGUSR1信号到所有子进程
                    // Master process will send SIGUSR1 signal to all child processes.
                    \posix_kill($master_pid, SIGUSR1);
                    // 状态加载等待 Loading wait.
                    \sleep($this->statusLoadingTime);
                    // 清除终端 Clear terminal.
                    \print_r("\033c");
                    // 输出状态数据 Echo status data.
                    if (\is_readable($this->statisticsFile)) {
                        echo file_get_contents($this->statisticsFile, \FILE_IGNORE_NEW_LINES);
                    } else {
                        return "[$entryCommand] status is not available.";
                    }

                    echo "\nRefresh every {$this->statusShowRefresh} seconds.\nPress Ctrl+C to quit.\n";

                    // 刷新等待 Refresh wait.
                    \sleep($this->statusShowRefresh);
                }
            case 'restart':
            case 'stop':
                if ($command === 'stop' && $mode === '-f') {
                    $this->forceStop = true;
                    $sig = \SIGINT;
                    echo "[$entryCommand] force stopping ...\n";
                } else {
                    $this->forceStop = false;
                    $sig = \SIGTERM;
                    echo "[$entryCommand] stopping ...\n";
                }
                // 发送停止信号给主进程
                // Send stop signal to master process.
                $master_pid && \posix_kill($master_pid, $sig);
                // 检查主进程是否存活
                // Check master process is still alive?
                while (1) {
                    $master_is_alive = $master_pid && \posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        echo "[$entryCommand] waiting ...\n";

                        \sleep(1);
                        continue;
                    }

                    echo "[$entryCommand] stop success\n";
                    if ($command === 'stop') {
                        // 停止当前进程 Stop current.
                        exit(0);
                    }

                    break;
                }

                // 重启 Restart
                if ($mode === '-d') {
                    $this->daemon = true;
                }
                break;
            default :
                return $usage;
        }
    }

    /**
     * 按后台模式执行
     * Run as daemon mode
     *
     * @throws Exception
     */
    protected function daemon()
    {
        \umask(0);
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            throw new Exception('Fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }

        // 启动新会话，离开当前终端
        // Set up a new session leader and leave the terminal.
        if (-1 === \posix_setsid()) {
            throw new Exception("Set sid fail");
        }

        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            throw new Exception("Fork fail");
        } elseif ($pid > 0) {
            exit(0);
        }
    }

    /**
     * 安装信号处理
     * Install signal handler
     *
     * @return void
     */
    protected function installSignal()
    {
        // 强制停止 force stop
        \pcntl_signal(\SIGINT, array($this, 'signalHandler'), false);
        // 停止 stop
        \pcntl_signal(\SIGTERM, array($this, 'signalHandler'), false);
        // 状态 status
        \pcntl_signal(\SIGUSR1, array($this, 'signalHandler'), false);
        // 忽略 pipe ignore
        \pcntl_signal(\SIGPIPE, \SIG_IGN, false);
    }

    /**
     * 卸载信号处理
     * Uninstall signal handler
     *
     * @return void
     */
    protected function uninstallSignal()
    {
        // 强制停止 force stop
        \pcntl_signal(\SIGINT, \SIG_IGN, false);
        // 停止 stop
        \pcntl_signal(\SIGTERM, \SIG_IGN, false);
        // 状态 status
        \pcntl_signal(\SIGUSR1, \SIG_IGN, false);
        // 忽略 pipe ignore
        \pcntl_signal(\SIGPIPE, \SIG_IGN, false);
    }

    /**
     * 信号处理
     * Signal handler
     *
     * @param int $signal
     */
    public function signalHandler(int $signal)
    {
        switch ($signal) {
            // 强制停止 Force stop.
            // Ctrl + C.
            case \SIGINT:
                $this->forceStop = true;
                $this->stopAll();
                break;
            // 重启 Restart.
            // 停止 Stop.
            case \SIGTERM:
                $this->forceStop = false;
                $this->stopAll();
                break;
            // 展示状态 Show status.
            case \SIGUSR1:
                $this->writeStatisticsFile();
                break;
        }
    }

    /**
     * 复制子进程
     * Fork task
     *
     * @throws Exception
     */
    protected function forkAndRunTask()
    {
        foreach ($this->task as $name => $task) {
            if (!isset($this->taskNum[$name])) {
                $this->taskNum[$name] = 1;
            }
            if (!isset($this->childRunningPidMap[$name])) {
                $this->childRunningPidMap[$name] = [];
            }

            while (\count($this->childRunningPidMap[$name]) < $this->taskNum[$name]) {
                $this->forkProcessTaskForLinux($name, $task);
            }
        }
    }

    /**
     * 复制子进程，Linux
     * Fork process task for Linux.
     *
     * @param string $name
     * @param ProcessTask $task
     * @throws Exception
     */
    protected function forkProcessTaskForLinux(string $name, ProcessTask $task)
    {
        $pid = \pcntl_fork();
        if ($pid > 0) {
            // 主进程流程 For master process.

            // 保存子进程pid Save child pid
            $this->childRunningPidMap[$name][$pid] = $pid;
        } elseif (0 === $pid) {
            // 子进程流程 For child process.

            // 伪随机种子生成 Random seed generator
            \srand();
            \mt_srand();

            // 用户和组 User and group
            $this->setUserAndGroup();

            // 进程间通信 Inter Process communication
            $this->uninstallSignal();
            $this->installSignal();

            $this->forkedTask = $task;

            while (true) {
                \pcntl_signal_dispatch();

                Hook::run('process_task_before');

                $this->forkedTask->run();

                Hook::run('process_task_after');
            }
        } else {
            throw new Exception("fork process task fail");
        }
    }

    /**
     * 为当前进程设置unix用户和组
     * Set unix user and group for current process.
     *
     * @return void
     */
    public function setUserAndGroup()
    {
        $user_info = \posix_getpwuid(\posix_getuid());
        if (!$user_info) {
            return;
        }

        // Get uid.
        $uid = $user_info['uid'];
        // Get gid.
        $gid = $user_info['gid'];

        // Set uid and gid.
        if ($uid !== \posix_getuid() || $gid !== \posix_getgid()) {
            if (!\posix_setgid($gid) || !\posix_initgroups($user_info['name'], $gid) || !\posix_setuid($uid)) {
                echo 'Warning: change gid or uid fail.';
            }
        }
    }

    /**
     * 监控所有子进程
     * Monitor all child processes.
     *
     * @throws Exception
     */
    protected function monitorForLinux()
    {
        $this->status = static::STATUS_RUNNING;

        while (true) {
            \pcntl_signal_dispatch();

            // 挂起当前进程，直至有子进程退出，或者收到信号
            // Suspends execution of the current process until a child has exited, or until a signal is delivered
            $status = 0;
            $pid = \pcntl_wait($status, \WUNTRACED);

            \pcntl_signal_dispatch();

            if ($pid > 0) {
                // 查明哪个进程退出
                // Find out which process exited.
                foreach ($this->childRunningPidMap as $name => $pid_array) {
                    if (isset($pid_array[$pid])) {
                        // 退出状态 Exit status.
                        if ($status !== 0) {
                            echo "Task {$name} exit with status $status\n";
                        }

                        // 清除子进程pid Clear child process pid.
                        unset($this->childRunningPidMap[$name][$pid]);
                        if (empty($this->childRunningPidMap[$name])) {
                            unset($this->childRunningPidMap[$name]);
                        }

                        break;
                    }
                }

                if ($this->status !== static::STATUS_STOPPING) {
                    // 启动新子进程 Fork new child process.
                    $this->forkAndRunTask();
                }
            }

            if (empty($this->childRunningPidMap) && $this->status === static::STATUS_STOPPING) {
                echo 'Master stop';
                exit(0);
            }
        }
    }

    /**
     * 停止所有
     * Stop all
     */
    public function stopAll()
    {
        $this->status = static::STATUS_STOPPING;

        $currentPid = \posix_getpid();
        if ($this->masterPid === $currentPid) {
            // 主进程流程 For master process.

            // 发送停止信号给所有子进程
            // Send stop signal to all child processes.
            if ($this->forceStop) {
                $sig = \SIGKILL; // kill -9
            } else {
                $sig = \SIGTERM;
            }
            foreach ($this->childRunningPidMap as $name => $tasks) {
                foreach ($tasks as $pid) {
                    \posix_kill($pid, $sig);
                }
            }
        } else {
            // 子进程流程 For child process.

            echo "Child process {$currentPid} graceful stop\n";
            exit(0);
        }
    }

    /**
     * 写入状态统计
     * Write statistics
     */
    public function writeStatisticsFile()
    {
        if ($this->masterPid === \posix_getpid()) {
            // 主进程流程 For master process.

            $status_str = "----------------------------PROCESS STATUS----------------------------\nPHP version:" . \PHP_VERSION . "\n\nTask and Process:\n-----------------------\n";
            foreach ($this->childRunningPidMap as $name => $tasks) {
                $status_str .= "$name\t" . \count($tasks) . "\n\n";
                foreach ($tasks as $pid) {
                    $status_str .= "PID\t$pid\n";

                    \posix_kill($pid, \SIGUSR1);
                }
                $status_str .= "-----------------------\n";
            }
        } else {
            // 子进程流程 For child process.

            $status_str = "PID\t" . \posix_getpid() . "\tMemory use\t" . \str_pad(round(memory_get_usage(true) / (1024 * 1024), 2) . "M", 7) . "\n";
        }

        \file_put_contents($this->statisticsFile, $status_str, \FILE_APPEND);
    }
}
