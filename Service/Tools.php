<?php
namespace Service;

use Tools\Logger;

/***
 * Class Tools
 *
 * @package Service
 */
class Tools{
    /**
     * @var Request;
     */
    private static $_request = null;
    /**
     * @var Apps;
     */
    private static $_apps    = null;
    /**
     * @var array
     */
    private static $_params  = [];
    /**
     * @var Logger
     */
    private static $_logger  = null;

    /**
     * @param Request $request
     * @param Apps    $apps
     */
    public static function init(Request $request, Apps $apps){
        self::$_request = $request;
        self::$_apps    = $apps;
        self::$_params  = $request->getParams();
        self::$_logger  = new Logger();
    }

    /***
     * @return Request
     */
    protected function getRequest(): Request{
        return self::$_request;
    }

    /**
     * @return array
     */
    protected function getParams(): array{
        return self::$_params;
    }

    /**
     * @return Apps
     */
    protected function getApps(): Apps{
        return self::$_apps;
    }

    /**
     * @return Logger
     */
    protected function getLogger(): Logger{
        return self::$_logger;
    }
}
