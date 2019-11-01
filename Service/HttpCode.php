<?php
namespace Service;

use Service\Http\Message\StatusCodeInterface;

/**
 * Http错误代码 class
 * Class HttpCode
 *
 * @package Service
 */
class HttpCode implements StatusCodeInterface  {

    public const API_CODE_OK           = self::STATUS_OK;
    public const API_CODE_CREATED      = self::STATUS_CREATED;
    public const API_CODE_NO_CONTENT   = self::STATUS_NO_CONTENT;
    public const API_CODE_BAD_REQUEST  = self::STATUS_BAD_REQUEST;
    public const API_CODE_UNAUTHORIZED = self::STATUS_UNAUTHORIZED;
    public const API_CODE_PAYMENT_REQUIRED = self::STATUS_PAYMENT_REQUIRED;
    public const API_CODE_FORBIDDEN    = self::STATUS_FORBIDDEN;
    public const API_CODE_NOT_FOUND    = self::STATUS_NOT_FOUND;
    public const API_CODE_METHOD_NOT_ALLOWED   = self::STATUS_METHOD_NOT_ALLOWED;
    public const API_CODE_GONE         = self::STATUS_GONE;
    public const API_CODE_UNSUPPORTED_MEDIA_TYPE   = self::STATUS_UNSUPPORTED_MEDIA_TYPE;
    public const API_CODE_UNPROCESSABLE_ENTITY = self::STATUS_UNPROCESSABLE_ENTITY;
    public const API_CODE_TOO_MANY_REQUESTS    = self::STATUS_TOO_MANY_REQUESTS;
    public const API_CODE_INTERNAL_SERVER_ERROR    = self::STATUS_INTERNAL_SERVER_ERROR;

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
