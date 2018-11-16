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
        $requestID = $this->request->header['x-request-id'] ?? $this->createRequestID();

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


    /**
     * 获取akamai edgescape 信息
     * georegion=212,country_code=TW,city=TAIPEI,lat=25.02,long=121.45,timezone=GMT+8,
     * continent=AS,throughput=vhigh,bw=5000,asnum=24158,network_type=mobile,location_id=0
     *
     * @return array
     */
    function getEdgescape(): array{
        $edgescape  = $this->request->header['x-akamai-edgescape'] ?? '';
        if($edgescape === ''){
            return array();
        }

        $__         = implode('&', explode(',', $edgescape));
        parse_str($__);

        return array(
            'georegion'     => $georegion ?? 0,
            'country_code'  => $country_code ?? "",
            'city'          => $city ?? "",
            'lat'           => $lat ?? 0,
            'long'          => $long ?? 0,
            'timezone'      => $timezone ?? "",
            'continent'     => $continent ?? "",
            'throughput'    => $throughput ?? "",
            'bw'            => $bw ?? 0,
            'asnum'         => $asnum ?? 0,
            'network_type'  => $network_type ?? "",
            'location_id'   => $location_id ?? 0,
        );
    }

    /**
     * 判断当前用户是否是台湾地区的用户
     *
     * @return bool
     */
    public function isTW(): bool {
        $edgescape  = $this->getEdgescape();
        if(empty($edgescape)){
            return false;
        }

        $country_code   = $edgescape['country_code'] ?? '';

        if($country_code !== 'TW'){
            return false;
        }

        return true;
    }

    /**
     * 是否是大陆地区
     *
     * @return bool
     */
    public function isMainland(): bool {
        $params = $this->getParams();
        $ip     = $params['ip'] ?? $this->getRemoteIP();
        $iprcode= Rcode::conversion($ip);
        if(preg_match('/^1_156/', $iprcode) && !preg_match('/^1_156_(71|81|82)/', $iprcode)){
            return true;
        }

        return false;
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

    /***
     * 解析UA
     *
     * @param string $ua
     * @return array
     */
    public function httpParseUa(string $ua = ''): array{
        $ua = trim($ua);
        if (empty($ua)) {
            $ua = $this->getUserAgent();
        }

        $httpUa = [
            'device' => '',
            'platform' => 'unknown',
            'screen' => array(1080, 1920),
            'version_name' => '',
            'version_code' => '',
            'timezone' => 'Asia/Shanghai',
            'additions' => '',
            'app' => 1,
            'ibb' => 0,
        ];

        $pattern = '#^Mozilla/5\.0 \((.+)\) .*(ios|android|windowsphone|windows|mac)/([^ ]+) \(([^ ]+)\)(.*)$#i';

        if (!preg_match($pattern, $ua, $match)) {
            return $httpUa;
        }

        $_['device'] = $match[1];
        $_['platform'] = strtolower($match[2]);

        list($width, $height, $version_name, $version_code)
            = explode('_', strrev($match[3]));
        $_['screen'] = array($width, $height);
        $_['version_name'] = $version_name;
        $_['version_code'] = intval($version_code);
        $_['timezone'] = trim($match[4]);
        $_['additions'] = trim($match[5]);

        if (preg_match('#app/([0-9]+)#', $_['additions'], $match)) {
            $_['app'] = trim($match[1]);
        }

        if (preg_match('#ibb/([0-9\.]+)#', $_['additions'], $match)) {
            $_['ibb'] = trim($match[1]);
        }

        return $_;
    }

    /***
     * 手动生成Request-id
     *
     * @return string
     */
    private function createRequestID() : string{
        if(file_exists('uuid_create')) {
            $uuid = uuid_create();
            return str_replace('-', '', $uuid);
        }else{
            return '-';
        }
    }
}