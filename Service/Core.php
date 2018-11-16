<?php
namespace Service;

use Swoole\Server;

/**
 * Class Core
 *
 * @package Service
 */
abstract class Core{
    public const DECODE_PHP            = 1;   //使用PHP的serialize打包
    public const DECODE_JSON           = 2;   //使用json_encode打包
    public const DECODE_MSGPACK        = 3;   //使用msgpack打包
    public const DECODE_SWOOLE         = 4;   //使用swoole_serialize打包
    public const DECODE_GZIP           = 128; //启用GZIP压缩

    public const LEVER_DEBUG     = 0;
    public const LEVER_TRACE     = 1;
    public const LEVER_INFO      = 2;
    public const LEVER_NOTICE    = 3;
    public const LEVER_WARNING   = 4;
    public const LEVER_ERROR     = 5;

    /***
     * @var \Swoole\Http\Server
     */
    private $server = null;
    private $host = '';
    private $port = 8000;
    private $processName = '-';
    private $pidFile = null;
    private $method  = 'GET';
    private $pathinfo= '/';
    private $httpContentType = 'json';
    protected $jobs   = null;
    /**
     * @var \Service\Request
     */
    public $request;

    /**
     * @var \Swoole\Http\Response
     */
    public $responses;

    /**
     * 默认配置
     *
     * @var array
     */
    private $settings = [
        'max_request'   => 100,
        'reactor_num'   => 2,
        'worker_num'    => 2,
        'daemonize'     => false,
        'dispatch_mode' => 3,
        'task_ipc_mode' => 3,
        'backlog'       => 2000,
        'http_compression'  => true,
        'http_parse_post'   => true,
        'log_level'         => 5,
        'open_cpu_affinity' => 1,
        'open_tcp_nodelay'  => 1,
        'open_tcp_keepalive' => 1,
        'tcp_keepidle'      => 5,
        'tcp_keepcount'     => 3,
        'tcp_keepinterval'  => 3,
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 10,
    ];

    /***
     * Core constructor.
     *
     * @param string $uri
     * @param array  $settings
     */
    public function __construct(string $uri, array $settings = array()) {
        $ps             = parse_url($uri);
        $this->host     = strval($ps['host']);
        $this->port     = intval($ps['port']);
        $this->settings = $settings ? array_merge($this->settings, $settings) : $this->settings;
    }

    /***
     * 设置PID文件
     *
     * @param string $filename
     * @return $this
     */
    public function setPidFile(string $filename = ''){
        $this->pidFile  = $filename ? $filename : __ROOT__ . '/'. $this->processName. '.pid';
        return $this;
    }

    /***
     * 设置是否开启压缩
     *
     * @param bool $compression
     * @return $this
     */
    public function setCompression(bool $compression = false): Core{
        $this->settings['http_compression'] = !!$compression;
        return $this;
    }

    /***
     * 设置日志文件
     *
     * @param String $file
     * @return Core
     */
    public function setLogFile(String $file = ''): Core{
        $this->settings['log_file']  = $file;
        return $this;
    }

    /***
     * 设置日志登记
     *
     * @param int $level
     * @return Core
     */
    public function setLogLevel(int $level = 0): Core{
        $this->settings['log_level'] = intval($level);
        return $this;
    }

    /***
     * 设置worker数量
     *
     * @param int $workerNum
     * @return Core
     */
    public function setWorkerNum(int $workerNum = 2): Core{
        $this->settings['worker_num'] = intval($workerNum);
        return $this;
    }


    /***
     * @param string $name
     * @return $this
     */
    public function setProcessName(string $name){
        $this->processName  = $name ? strval($name) : $this->processName;
        return $this;
    }

    /**
     * run
     *
     * @return void
     */
    public function run(){
        $cmd    = $_SERVER['argv'][1] ?? '';
        $option = $_SERVER['argv'][2] ?? '';

        if($cmd === ''){
            $this->_usageUI();
        }

        switch ($cmd){
            case 'start':
                echo "Start {$this->processName} Done...\n";
                //看进程是否存在
                $pid    = @file_get_contents($this->pidFile);
                if($pid && \Swoole\Process::kill($pid, 0)){
                    echo "PID file exists: ", $this->pidFile, "\n";
                    echo 'Process (', $pid,
                    ") is running.\n";
                    exit();
                }

                if($option === '-d'){
                    $this->settings['daemonize'] = true;
                }

                $this->_run();
                break;
            case 'reload':
            case 'reopen':
                $pid    = file_get_contents($this->pidFile);
                if($pid && \Swoole\Process::kill($pid, 0)){
                    \Swoole\Process::kill($pid, SIGUSR1);

                    echo "Reload {$this->processName} Done...\n";
                }else{
                    echo "Not Found PidFile...\n";
                }
                break;
            case 'status':
                break;
            case 'stop':
                $pid    = file_get_contents($this->pidFile);
                if($pid && \Swoole\Process::kill($pid, 0)){
                    \Swoole\Process::kill($pid, SIGTERM);

                    echo "ShutDown {$this->processName} Done....\n";
                }else{
                    echo "Not Found PidFile...\n";
                }
                break;
            default:
                $this->_usageUI();
                break;
        }
    }

    /***
     * 运行服务
     */
    private function _run(){
        $this->server = new \Swoole\Http\Server($this->host, $this->port,
            SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->set($this->settings);
        // 开启http服务器
        $this->server->on('request', array($this, 'request'));
        $this->server->on('workerStart', array($this, 'workerStart'));
        $this->server->on('workerStop', array($this, 'workerStop'));
        $this->server->on('managerStart', array($this, 'managerStart'));
        $this->server->on('managerStop', array($this, 'managerStop'));
        $this->server->on('start', array($this, 'start'));
        $this->server->on('shutdown', array($this, 'shutdown'));
        $this->server->on('workerError', array($this, 'workerError'));
        $this->server->start();
    }

    /***
     * @param Server $server
     */
    public function start(\Swoole\Server $server){
        echo 'Date:' . date('Y-m-d H:i:s') ,
            "\t Swoole\Http\Server master worker start\n";
        @swoole_set_process_name($server->setting['process_name'] . '-master');
        //记录进程id,脚本实现自动重启
        $pid = $server->master_pid;
        file_put_contents($this->pidFile, $pid);
    }

    /***
     * @param \Swoole\Http\Server $server
     * @param                     $task_id
     * @param                     $data
     */
    public function finish(\Swoole\Http\Server $server, $task_id, $data){}

    /***
     * @param Server $server
     * @param        $worker_id
     * @param        $worker_pid
     * @param        $exit_code
     */
    public function workerError(\Swoole\Server $server, $worker_id,
                                 $worker_pid, $exit_code){
        echo 'Date:' . date('Y-m-d H:i:s') ,
        "\t Swoole\Http\Server has error $worker_id#$worker_pid $exit_code\n";
    }

    /***
     * @param Server $server
     */
    public function shutdown(\Swoole\Server $server){
        unlink($this->pidFile);
        echo 'Date:' . date('Y-m-d H:i:s') ,
                "\t Swoole\Http\Server shutdown\n";
    }

    /***
     * @param Server $server
     * @param        $workerId
     */
    public function workerStop(\Swoole\Server $server, $workerId){
        $date = date('Y-m-d H:i:s');
        echo "Date:{$date} \t Swoole\Http\Server worker:{$workerId} shutdown\n";
    }

    /***
     * @param Server $server
     * @param        $workerID
     */
    public function workerStart(\Swoole\Server $server, $workerID){
        //判断worker
        if($workerID >= $server->setting['worker_num']) {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t Swoole\Http\Server task-worker start\n";
            @swoole_set_process_name("{$this->processName}-task");
        } else {
            echo 'Date:' . date('Y-m-d H:i:s') . "\t Swoole\Http\Server worker start\n";
            @swoole_set_process_name("{$this->processName}-worker");
        }
    }

    /***
     * @param Server $server
     */
    public function managerStart(\Swoole\Server $server){
        echo 'Date:' . date('Y-m-d H:i:s') ,
            "\t Swoole\Http\Server manager worker start\n";
        @swoole_set_process_name($server->setting['process_name'] . '-manager');
    }

    /***
     * @param Server $server
     */
    public function managerStop(\Swoole\Server $server){
        echo 'Date:' . date('Y-m-d H:i:s') . "\t Swoole\Http\Server manager worker stop\n";
    }

    /***
     * @param \Swoole\Http\Request  $request
     * @param \Swoole\Http\Response $response
     */
    public function request(\Swoole\Http\Request $request, \Swoole\Http\Response $response){
        //首先初始化，HTTP请求的参数
        $this->initHttpRequest($request, $response);
        $this->method   = $this->request->getMethod();
        $this->pathinfo = $this->request->getPathInfo();

        //解析客户端发来的数据
        $rawContent = trim($this->request->getBody());
        $__         = json_decode($rawContent, true);

        $params     = $this->request->getParams();

        $this->pathinfo   = preg_replace_callback('/\.(json)/', function($match){
            $this->httpContentType = strval($match[1]);
            return '';
        }, $this->pathinfo);

        if($this->pathinfo === '/favicon.ico' || $this->pathinfo === ''){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => '',
                'data'      => [],
            ));
            return;
        }

        $this->requestDo();
        return;
    }

    abstract function requestDo();

    /***
     * Worker 初始化
     *
     * @param \Swoole\Server $server
     * @param int            $workerId
     * @return mixed
     */
    abstract function initWorker(\Swoole\Server $server, int $workerId);

    /***
     * @param int                   $code
     * @param array                 $data
     */
    public function httpResponse(int $code, array $data){
        $code   = intval($code);
        $code   = $code === 0 || $code === 204 ? 200 : $code;
        if($this->httpContentType === 'json'){
            $content_type = 'application/json';
        }else{
            $content_type = 'text/html';
        }

        $this->responses->header('content-type', $content_type);
        $this->responses->status($code);

        $data['message']    = HttpCode::$ErrorCode[$data['code']] ?? '';
        $json   = json_encode($data);
        $this->responses->end($json);
    }

    /**
     * 显示help
     *
     * @return void
     */
    private function _usageUI(){
        echo PHP_EOL;
        echo 'USAGE: php index.php commond', PHP_EOL;
        echo '1. start,以debug模式开启服务，此时服务不会以daemon形式运行', PHP_EOL;
        echo '2. start -d,以daemon模式开启服务', PHP_EOL;
        echo '3. status,查看服务器的状态', PHP_EOL;
        echo '4. stop,停止服务器', PHP_EOL;
        echo '5. reload,热加载所有业务代码', PHP_EOL, PHP_EOL;
        exit;
    }

    /**
     * 初始化
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    private function initHttpRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response){
        $this->request = Request::initRequest($request);
        $this->responses = $response;
    }
}
