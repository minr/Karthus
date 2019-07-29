<?php
namespace Service;

use Tools\IP\Rcode;
use Tools\Logger;

/**
 * Class Request
 *
 * @package Service
 */
class Request{
    /**
     * @var \Swoole\Http\Request;
     */
    private $request = null;
    private static $instance = null;
    private $params = [];

    public function __construct(\Swoole\Http\Request $request = null) {
        $this->request = $request;
    }


    /**
     * 初始化
     *
     * @param \Swoole\Http\Request|null $request
     * @return Request
     */
    public static function initRequest(\Swoole\Http\Request $request = null): Request{
        if(is_null($request) === false) {
            self::$instance = new Request($request);
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getBody(): string{
        return $this->request->rawContent();
    }

    /***
     * @return array
     */
    public function getHeader(): array{
        return $this->request->header;
    }

    /**
     * @return array
     */
    public function getServer(): array{
        return $this->request->server;
    }

    /**
     * 获取参数
     *
     * @return array
     */
    public function getParams(): array{
        $get    = $this->request->get;
        $post   = $this->request->post;

        $get    = $get ?: [];
        $post   = $post ?: [];

        $this->params = array_merge($this->params, $get, $post);
        return $this->params;
    }

    /**
     * 设置参数头
     *
     * @param array $params
     */
    public function setParams(array $params = []): void {
        $params = $params ?: [];
        $this->params = array_merge($this->params, $params);
    }

    /**
     * 获取COOKIES
     *
     * @return array
     */
    public function getCookies(): array{
        $cookies= $this->request->cookie;

        return is_null($cookies) ? [] : $cookies;
    }

    /**
     * 获取上传的文件头
     *
     * @return array
     */
    public function getFiles(): array{
        $files = $this->request->files;

        return is_null($files) ? [] : $files;
    }

    /**
     * 获取QueryString
     *
     * @return string
     */
    public function getQueryString(): string{
        $queryString = $this->request->server['query_string'] ?? '';

        return $queryString;
    }

    /**
     * 获取method
     *
     * @return string
     */
    public function getMethod(): string{
        $method = $this->request->server['request_method'] ?? 'GET';

        return strtoupper($method);
    }

    /**
     * 获取PATH INFO
     *
     * @return string
     */
    public function getPathInfo(): string{
        $pathInfo = $this->request->server['path_info'] ?? '/';

        return $pathInfo;
    }

    /**
     * 获取请求时间
     *
     * @return float
     */
    public function getRequestTime():float {
        $requestTime = $this->request->server['request_time_float'] ?? 0.00;

        return $requestTime;
    }

    /**
     * 获取server 协议
     *
     * @return string
     */
    public function getServerProtocol(): string{
        $serverProtocol = $this->request->server['server_protocol'] ?? 'HTTP/1.1';

        return $serverProtocol;
    }

    /**
     * 获取Accept
     *
     * @return string
     */
    public function getAccept(): string{
        $accept = $this->request->header['accept'] ?? '*/*';

        return $accept;
    }

    /**
     * 获取用户Ua
     *
     * @return string
     */
    public function getUserAgent(): string{
        $userAgent = $this->request->header['user-agent'] ?? '';

        return $userAgent;
    }

    /**
     * 获取content-type
     *
     * @return string
     */
    public function getContentType(): string{
        $contentType = $this->request->header['content-type'] ?? '';

        return $contentType;
    }

    /**
     * 获取x-request-id
     *
     * @return string
     */
    public function getRequestID(): string{
        $requestID = $this->request->header['x-request-id'] ?? '-';

        return $requestID;
    }

    /**
     * 获取 x-remote-userid
     *
     * @return int
     */
    public function getRemoteUserID(): int{
        $userID = $this->request->header['x-remote-userid'] ?? 0;

        return $userID;
    }

    /**
     * 获取 accept-language
     *
     * @return string
     */
    public function getAcceptLanguage(): string{
        $acceptLanguage = $this->request->header['accept-language'] ?? '';

        return $acceptLanguage;
    }

    /**
     * 获取IP
     *
     * @return string
     */
    public function getRemoteIP():string{
        $remoteIp = $this->request->header['x-real-ip'] ??
                        ($this->request->header['remote_addr']
                            ?? '127.0.0.1');

        return $remoteIp;
    }

    /***
     * 获取语言
     *
     * @return string
     */
    public function getLanguage(): string{
        $lang = $this->getAcceptLanguage();
        $lang = strtolower($lang);
        if (preg_match('/^zh\-(cn|sg)/i', $lang)) {
            return 'zh-cn';
        } elseif (preg_match('/^zh\-(tw|hk)/i', $lang)) {
            return 'zh-tw';
        } elseif (preg_match('/^zh/i', $lang)) {
            return 'zh-cn';
        } elseif (preg_match('/^en/i', $lang)) {
            return 'en-us';
        }elseif(preg_match('/^([a-z]{1,})-([a-z]{1,})/i', $lang, $match)){
            return strval($match[1]);
        }elseif(preg_match('/^([a-z]{1,})-/i', $lang, $match)){
            return strval($match[1]);
        }elseif(preg_match('/^-([a-z]{1,})/i', $lang, $match)){
            return strval($match[1]);
        }else{
            (new Logger())->error('Language not match'. $lang. $this->getUserAgent());
            return "";
        }
    }
}