<?php
namespace Tools;

use \Service\Request;

/**
 * Class Logger
 *
 * @package Tools
 */
class Logger{
    private $dir = LOGGER_DIR;
    private $msg = '';
    private $level = 'info';

    /**
     * @var \Service\Request
     */
    private $request = null;

    public function __construct(\Service\Request $request = null) {
        $this->request = is_null($request) ? Request::initRequest() : $request;
    }

    /***
     * 设置日志内容
     *
     * @param $msg
     * @return $this
     */
    public function setContent($msg): Logger{
        $msg        = is_scalar($msg) ? strval($msg)
            : json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $this->msg  = $msg;

        return $this;
    }

    /***
     * 设置日志目录
     *
     * @param string $dir
     * @return Logger
     */
    public function setDir(string $dir = ''): Logger{
        if($dir){
            exit('Dir Not Allowed Empty ');
        }

        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }

        $this->dir  = $dir;
        return $this;
    }

    /***
     * 设置日志登记
     *
     * @param string $level
     * @return Logger
     */
    public function setLevel(string $level = 'info'): Logger{
        $this->level = strval($level);
        return $this;
    }

    /***
     * 错误日志
     *
     * @param $msg
     */
    public function error($msg){
        $this->setLevel('error')
            ->setContent($msg)
            ->Logger();
    }

    /***
     * 成功日志
     *
     * @param $msg
     */
    public function success($msg){
        $this->setLevel('success')
            ->setContent($msg)
            ->Logger();
    }

    /***
     * info日志
     *
     * @param $msg
     */
    public function info($msg){
        $this->setLevel('info')
            ->setContent($msg)
            ->Logger();
    }

    /***
     * 打日志
     */
    public function Logger(){
        $date   = date('Ymd');
        //参数处理
        $msg    = $this->msg;
        $time   = strftime('[%d/%h/%Y:%H:%M:%S %z]', $this->request->getRequestTime());
        $id     = strval($this->request->getRequestID());
        $msg    = "{$this->request->getRemoteIP()} {$this->request->getRemoteUserID()} {$id} {$time} \"{$this->request->getMethod()} {$this->request->getPathInfo()} $msg\"\n";

        $file   = "{$this->dir}/{$this->level}.log.{$date}";
        @file_put_contents($file, $msg, FILE_APPEND | LOCK_EX);
    }

}