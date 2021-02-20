<?php
/**
 * Process
 *
 * master monitor -> child task
 */

namespace Shy\Core;

use Exception;
use Shy\Core\Contracts\ProcessTask;
use Shy\Core\Facades\Hook;

class Process
{
    /**
     * @var array
     */
    private $task;

    /**
     * @var array
     */
    private $taskNum;

    /**
     * @var int
     */
    protected $masterPid = 0;

    /**
     * @var string
     */
    protected $pidFile = '';

    protected $statisticsFile;

    protected $daemon = false;

    protected $forceStop = false;

    protected $childRunningPidMap = [];

    /**
     * @var ProcessTask
     */
    protected $forkedTask;

    /**
     * Status
     *
     * @var int
     */
    const STATUS_STARTING = 1;
    const STATUS_RUNNING = 2;
    const STATUS_RELOADING = 3;
    const STATUS_STOPPING = 4;

    protected $status = self::STATUS_STARTING;

    /**
     * @var int second
     */
    public $statusShowLoading = 3;

    /**
     * @var int second
     */
    public $statusShowRefresh = 10;

    /**
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

        // Master daemon
        $this->daemon();

        // Inter Process communication
        $this->installSignal();

        // Save master pid
        $this->masterPid = \posix_getpid();
        if (false === \file_put_contents($this->pidFile, $this->masterPid)) {
            throw new Exception('Can not save master pid to ' . $this->pidFile);
        }

        // Fork task process
        $this->forkAndRunTask();

        // Monitor
        $this->monitorForLinux();
    }

    /**
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
            'reload',
            'status',
        );
        $usage = "Usage: php command <command> <operation> [mode]\nOperations: \nstart\t\tUse mode -d to start in DAEMON mode.\nstop\t\tUse mode -f to force stop.\t\nrestart\t\tUse mode -d to start in DAEMON mode.\t\nreload\t\tDo not stop and reload business code.\nstatus\t\tShow status.";
        if (!isset($argv[1]) || !\in_array($argv[1], $available_operation)) {
            if (isset($argv[1])) {
                $usage = 'Unknown operation: ' . $argv[1] . "\n" . $usage;
            }

            return $usage;
        }

        $command = \trim($argv[1]);
        $mode = isset($argv[2]) ? $argv[2] : '';

        // Get master process PID.
        $master_pid = \is_file($this->pidFile) ? \file_get_contents($this->pidFile) : 0;
        $master_is_alive = $master_pid && \posix_kill($master_pid, 0) && \posix_getpid() !== $master_pid;
        // Master is still alive?
        if ($master_is_alive) {
            if ($command === 'start') {
                return "[$entryCommand] already running";
            }
        } elseif ($command !== 'start' && $command !== 'restart') {
            return "[$entryCommand] not run";
        }

        // execute command.
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

                    // Master process will send SIGUSR2 signal to all child processes.
                    \posix_kill($master_pid, SIGUSR2);
                    // Loading wait.
                    \sleep($this->statusShowLoading);
                    // Clear terminal.
                    \print_r("\033c");
                    // Echo status data.
                    if (\is_readable($this->statisticsFile)) {
                        echo file_get_contents($this->statisticsFile, \FILE_IGNORE_NEW_LINES);
                    } else {
                        return "[$entryCommand] status is not available.";
                    }

                    echo "\nRefresh every {$this->statusShowRefresh} seconds.\nPress Ctrl+C to quit.\n";

                    // Refresh wait.
                    \sleep($this->statusShowRefresh);
                }
                exit(0);
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
                // Send stop signal to master process.
                $master_pid && \posix_kill($master_pid, $sig);
                // Check master process is still alive?
                while (1) {
                    $master_is_alive = $master_pid && \posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        echo "[$entryCommand] waiting ...\n";

                        \sleep(1);
                        continue;
                    }

                    // Stop success.
                    echo "[$entryCommand] stop success\n";
                    if ($command === 'stop') {
                        exit(0);
                    }

                    break;
                }

                // Restart
                if ($mode === '-d') {
                    $this->daemon = true;
                }
                break;
            case 'reload':
                \posix_kill($master_pid, \SIGUSR1);
                exit;
            default :
                return $usage;
        }
    }

    /**
     * Run as daemon mode
     *
     * @throws Exception
     */
    protected function daemon()
    {
        if (!$this->daemon) {
            return;
        }

        \umask(0);
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            throw new Exception('Fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }

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
     * Install signal handler
     *
     * @return void
     */
    protected function installSignal()
    {
        // force stop
        \pcntl_signal(\SIGINT, array($this, 'signalHandler'), false);
        // stop
        \pcntl_signal(\SIGTERM, array($this, 'signalHandler'), false);
        // reload
        \pcntl_signal(\SIGUSR1, array($this, 'signalHandler'), false);
        // status
        \pcntl_signal(\SIGUSR2, array($this, 'signalHandler'), false);
        // pipe ignore
        \pcntl_signal(\SIGPIPE, \SIG_IGN, false);
    }

    /**
     * Uninstall signal handler
     *
     * @return void
     */
    protected function uninstallSignal()
    {
        // force stop
        \pcntl_signal(\SIGINT, \SIG_IGN, false);
        // stop
        \pcntl_signal(\SIGTERM, \SIG_IGN, false);
        // reload
        \pcntl_signal(\SIGUSR1, \SIG_IGN, false);
        // status
        \pcntl_signal(\SIGUSR2, \SIG_IGN, false);
    }

    /**
     * Signal handler
     *
     * @param int $signal
     */
    public function signalHandler(int $signal)
    {
        switch ($signal) {
            // Force stop.
            case \SIGINT:
                $this->forceStop = true;
                $this->stopAll();
                break;
            // Restart.
            // Stop.
            case \SIGTERM:
                $this->forceStop = false;
                $this->stopAll();
                break;
            // Reload.
            case \SIGUSR1:
                $this->reload();
                break;
            // Show status.
            case \SIGUSR2:
                $this->writeStatisticsFile();
                break;
        }
    }

    /**
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
     * Fork process task.
     *
     * @param string $name
     * @param ProcessTask $task
     * @throws Exception
     */
    protected function forkProcessTaskForLinux(string $name, ProcessTask $task)
    {
        $pid = \pcntl_fork();
        if ($pid > 0) {
            // For master process.

            // Save child pid
            $this->childRunningPidMap[$name][$pid] = $pid;
        } elseif (0 === $pid) {
            // For child process.

            // Random seed generator
            \srand();
            \mt_srand();

            // User and group
            $this->setUserAndGroup();

            // Inter Process communication
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
     * Monitor all child processes.
     *
     * @throws Exception
     */
    protected function monitorForLinux()
    {
        $this->status = static::STATUS_RUNNING;

        while (true) {
            \pcntl_signal_dispatch();

            // Suspends execution of the current process until a child has exited, or until a signal is delivered
            $status = 0;
            $pid = \pcntl_wait($status, \WUNTRACED);

            \pcntl_signal_dispatch();

            if ($pid > 0) {
                // Find out which process exited.
                foreach ($this->childRunningPidMap as $name => $pid_array) {
                    if (isset($pid_array[$pid])) {
                        // Exit status.
                        if ($status !== 0) {
                            echo "Task {$name} exit with status $status\n";
                        }

                        // Clear child process pid.
                        unset($this->childRunningPidMap[$name][$pid]);
                        if (empty($this->childRunningPidMap[$name])) {
                            unset($this->childRunningPidMap[$name]);
                        }

                        break;
                    }
                }

                if ($this->status !== static::STATUS_STOPPING) {
                    // Fork new process.
                    $this->forkAndRunTask();
                }
            }

            if (empty($this->childRunningPidMap && $this->status === static::STATUS_STOPPING)) {
                echo 'Master stop';
                exit(0);
            }
        }
    }

    protected function reload()
    {
        $this->status = static::STATUS_RELOADING;
    }

    /**
     * Stop all
     */
    public function stopAll()
    {
        $this->status = static::STATUS_STOPPING;

        $currentPid = \posix_getpid();
        if ($this->masterPid === $currentPid) {
            // For master process.

            // Send stop signal to all child processes.
            if ($this->forceStop) {
                $sig = \SIGKILL;
            } else {
                $sig = \SIGTERM;
            }
            foreach ($this->childRunningPidMap as $name => $tasks) {
                foreach ($tasks as $pid) {
                    \posix_kill($pid, $sig);
                }
            }
        } else {
            // For child process.

            echo "Child process {$currentPid} stopping\n";
            exit(0);
        }
    }

    /**
     * Write statistics
     */
    public function writeStatisticsFile()
    {
        echo PHP_EOL . 'writeStatisticsFile' . \posix_getpid() . PHP_EOL;
        if ($this->masterPid === \posix_getpid()) {
            // For master process.

            $status_str = "----------------------------PROCESS STATUS----------------------------\nPHP version:" . \PHP_VERSION . "\n\nTask and Process:\n-----------------------\n";
            foreach ($this->childRunningPidMap as $name => $tasks) {
                $status_str .= "$name\t" . \count($tasks) . "\n\n";
                foreach ($tasks as $pid) {
                    $status_str .= "PID\t$pid\n";
                    \posix_kill($pid, \SIGUSR2);
                }
                $status_str .= "-----------------------\n";
            }
        } else {
            // For child process.

            $status_str = "PID\t" . \posix_getpid() . "\tMemory use\t" . \str_pad(round(memory_get_usage(true) / (1024 * 1024), 2) . "M", 7) . "\n";
        }

        \file_put_contents($this->statisticsFile, $status_str, \FILE_APPEND);
    }
}
