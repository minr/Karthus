<?php
namespace Tools\Client;

use Service\Tools;

/**
 * Class Http
 *
 * @package Tools\Client
 */
class Http extends Tools {
    private $client = null;
    private $path   = '/';
    private $timeout= 0.5;
    private $header = [];
    private $body   = '';
    private $method = 'GET';
    private $data   = null;
    private $statusCode = 200;
    private $errCode = 0;

    /**
     * Http constructor.
     *
     * @param string $ip
     * @param int    $port
     * @param bool   $ssl
     */
    public function __construct(string $ip = '127.0.0.1', int $port = 80, bool $ssl = false) {
        $this->client = new \Swoole\Coroutine\Http\Client($ip, $port, !!$ssl);
    }

    /**
     * 设置method
     *
     * @param string $method
     * @return Http
     */
    public function setMethod(string $method = 'GET'): Http{
        $method = strtoupper($method);
        $this->client->setMethod($method);
        $this->method = $method;
        return $this;
    }

    /**
     * 设置path
     *
     * @param string $path
     * @return Http
     */
    public function setPath(string $path = '/'): Http{
        $path = strval($path);
        $this->path = $path;
        return $this;
    }

    /**
     * 设置超时时间
     *
     * @param float $timeout
     * @return Http
     */
    public function setTimeout(float $timeout = 0.5): Http{
        $timeout = intval($timeout);
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 设置header
     *
     * @param string $name
     * @param string $value
     * @return Http
     */
    public function setHeader(string $name, string $value): Http{
        $header[$name] = $value;
        $this->header  = array_merge($this->header, $header);
        return $this;
    }

    /**
     * 设置Http请求的包体
     * $data 为字符串格式
     * 设置$data后并且未设置$method，底层会自动设置为POST
     * 未设置Http请求包体并且未设置$method，底层会自动设置为GET
     *
     * @param string $data
     * @return Http
     */
    public function setData(string $data): Http{
        $this->data = $data;
        return $this;
    }

    /**
     * 设置header头中的X-Remote-UserID
     *
     * @param int $uid
     * @return Http
     */
    public function setRemoteUID(int $uid):Http{
        $this->setHeader('X-Remote-UserID', $uid);
        return $this;
    }

    /**
     * 设置requestID
     *
     * @param string $requestID
     * @return Http
     */
    public function setRequestID(string $requestID): Http{
        $this->setHeader('X-Request-ID', $requestID);
        return $this;
    }

    /***
     * 设置content-type
     *
     * @param string $contentType
     * @return Http
     */
    public function setContentType(string $contentType): Http{
        $this->setHeader('content-type', $contentType);
        return $this;
    }

    /***
     * 设置User-Agent
     *
     * @param string $ua
     * @return Http
     */
    public function setUserAgent(string $ua):Http{
        $this->setHeader('User-Agent', $ua);
        return $this;
    }

    /**
     * 设置Authorization Basic鉴权头
     *
     * @param string $name
     * @param string $password
     * @return Http
     */
    public function setAuthorization(string $name, string $password): Http{
        $this->setHeader('Authorization', 'Basic '. base64_encode("$name:$password"));
        return $this;
    }

    /**
     * 执行
     *
     * @return Http
     */
    public function execute():Http{
        $this->client->set([
            'timeout'   => $this->timeout,
        ]);
        $this->client->setHeaders($this->header);
        switch ($this->method){
            case 'GET':
                $this->client->get($this->path);
                break;
            default:
                $this->client->post($this->path, $this->data);
                break;
        }

        $this->body       = $this->client->body;
        $this->statusCode = $this->client->statusCode;
        $this->errCode    = $this->client->errCode;
        $this->client->close();
        return $this;
    }

    /***
     * 存储上次请求的返回包体
     *
     * @return string
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * Http状态码，如200、404等。状态码如果为负数，表示连接存在问题。
     * -1：连接超时，服务器未监听端口或网络丢失，可以读取$errCode获取具体的网络错误码
     * -2：请求超时，服务器未在规定的timeout时间内返回response
     * -3：客户端请求发出后，服务器强制切断连接
     *
     * @return int
     */
    public function getStatusCode(): int{
        return $this->statusCode;
    }

    /**
     * 类型为int型。当connect/send/recv/close失败或者超时时，会自动设置Swoole\Coroutine\Http\Client->errCode的值。
     * errCode的值等于Linux errno。可使用socket_strerror将错误码转为错误信息。
     * echo socket_strerror($client->errCode);
     * 如果connect refuse，错误码为111
     * 如果超时，错误码为110
     *
     * @return int
     */
    public function getErrCode(): int{
        return $this->errCode;
    }
}
