<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-09-19
 * Time: 14:22
 */

namespace Toolkit\Web\Traits;

use Toolkit\Web\Helper\ResponseCode;

/**
 * Class RespondUtilTrait
 * @package Toolkit\Web\Traits
 *
 * ```php
 * class Respond extends ResponseCode {
 *      use RespondUtilTrait;
 *
 *      // define more code constants;
 * }
 * ```
 */
trait RespondUtilTrait
{
    /**
     * @var string
     */
    public static $defaultMsg = 'successful';

    /**
     * @param mixed $data
     * @param int $code
     * @param string $msg
     * @param array $msgArgs
     * @return string
     */
    public static function json($data, int $code = ResponseCode::OK, string $msg = '', array $msgArgs = [])
    {
        return self::fmtJson($data, $code, $msg, $msgArgs);
    }

    /**
     * @param mixed  $data
     * @param int    $code
     * @param string $msg
     * @param array  $msgArgs
     *
     * @return string
     */
    public static function errJson(int $code = ResponseCode::OK, string $msg = null, array $msgArgs = [], $data = null)
    {
        return self::fmtJson($data, $code, $msg, $msgArgs);
    }

    /**
     * @param mixed $data
     * @return string
     */
    public static function rawJson($data)
    {
        return json_encode($data);
    }

    /**
     * @param mixed $data
     * @param int $code
     * @param string $msg
     * @param array $msgArgs
     * @return string
     */
    public static function fmtJson($data, int $code = ResponseCode::OK, string $msg = '', array $msgArgs = [])
    {
        return json_encode(static::fmtData($data, $code, $msg, $msgArgs));
    }

    /**
     * @param mixed $data
     * @param int $code
     * @param string $msg
     * @param array $msgArgs
     * @return array
     */
    public static function fmtData($data, int $code = ResponseCode::OK, string $msg = '', array $msgArgs = [])
    {
        return [
            'code' => $code,
            'msg' => $msg ?: static::getMsgByCode($code, $msgArgs),
            'time' => microtime(true),
            'data' => $data,
        ];
    }

    /**
     * @param int $code
     * @param array $msgArgs
     * @return mixed
     */
    public static function getMsgByCode($code, array $msgArgs = [])
    {
        // if ($lang = container()->getIfExist('lang')) {
        //     return $lang->tl('response.' . $code, $msgArgs, self::$defaultMsg);
        // }

        return static::$defaultMsg;
    }
}
