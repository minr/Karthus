<?php
namespace Service;

/**
 * Http错误代码 class
 * Class HttpCode
 *
 * @package Service
 */
class HttpCode{

    const API_CODE_OK           = 200;
    const API_CODE_CREATED      = 201;
    const API_CODE_NO_CONTENT   = 204;
    const API_CODE_BAD_REQUEST  = 400;
    const API_CODE_UNAUTHORIZED = 401;
    const API_CODE_PAYMENT_REQUIRED = 402;
    const API_CODE_FORBIDDEN    = 403;
    const API_CODE_NOT_FOUND    = 404;
    const API_CODE_METHOD_NOT_ALLOWED   = 405;
    const API_CODE_GONE         = 410;
    const API_CODE_UNSUPPORTED_MEDIA_TYPE   = 415;
    const API_CODE_UNPROCESSABLE_ENTITY = 422;
    const API_CODE_TOO_MANY_REQUESTS    = 429;
    const API_CODE_INTERNAL_SERVER_ERROR    = 500;

    public static $ErrorCode    = [
        200 => '操作成功',
        400 => '非法请求',
        401 => '验证失败,请重新登录',
        402 => '需付费',
        403 => '操作被禁止',
        404 => '未找到',
        405 => '请求的方法不支持',
        410 => '操作不被支持',
        415 => '请求错误',
        422 => '参数错误',
        429 => '操作过于频繁,请稍后再试',
        500 => '未知错误,请检查您的网络',
    ];
}