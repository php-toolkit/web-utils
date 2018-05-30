<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/6/10
 * Time: 下午12:26
 */

namespace Toolkit\Web\Util;

/**
 * Class RequestHelper
 * @package Toolkit\Web\Util
 */
class RequestUtil
{
    /**
     * 本次请求开始时间
     * @param bool $float
     * @return mixed
     */
    public static function time($float = true)
    {
        if ((bool)$float) {
            return $_SERVER['REQUEST_TIME_FLOAT'];
        }

        return $_SERVER['REQUEST_TIME'];
    }

    /**
     * Get a value from $_POST / $_GET
     * if unavailable, take a default value
     * @param string $key Value key
     * @param mixed $default (optional)
     * @return mixed Value
     */
    public static function param($key, $default = null)
    {
        if (!$key || !\is_string($key)) {
            return false;
        }

        $ret = $_POST[$key] ?? $_GET[$key] ?? $default;

        if (\is_string($ret)) {
            return stripslashes(urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret))));
        }

        return $ret;
    }

    /**
     * @param null|string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name = null, $default = null)
    {
        if (null === $name) {
            return $_GET;
        }

        return $_GET[$name] ?? $default;
    }

    /**
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public static function post($name = null, $default = null)
    {
        $body = self::getParsedBody();

        if (null === $name) {
            return $body;
        }

        return $body[$name] ?? $default;
    }

    /**
     * @var false|array
     */
    private static $parsedBody = false;

    /**
     * @return array
     */
    public static function getParsedBody(): array
    {
        if (self::$parsedBody === false) {
            // post data is json
            if (!($type = $_SERVER['HTTP_CONTENT_TYPE'] ?? null) || \strpos($type, '/json') <= 0) {
                self::$parsedBody = &$_POST;
            } else {
                self::$parsedBody = \json_decode(\file_get_contents('php://input'), true);
            }
        }

        return self::$parsedBody;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function hasParam(string $key): bool
    {
        return isset($_POST[$key]) ? true : isset($_GET[$key]);
    }

    public static function safePostVars()
    {
        if (!$_POST || !\is_array($_POST)) {
            $_POST = [];
        } else {
            $_POST = array_map('htmlspecialchars', $_POST);
        }
    }

    /**
     * Get all values from $_POST/$_GET
     * @return array
     */
    public static function getAll(): array
    {
        return $_POST + $_GET;
    }

    /**
     * @param $data
     * @param $separator
     * @example
     *      /status/active/id/12
     *  =>
     *    [
     *     'status' => 'active',
     *     'id'     => '12',
     *    ]
     * @return array
     */
    public static function buildQueryParams($data, $separator = '/'): array
    {
        $arrData = \is_string($data) ? \explode($separator, $data) : $data;
        $arrData = \array_values(\array_filter($arrData));
        $newArr = [];
        $count = \count($arrData); #统计

        // $arrData 中的 奇数位--变为键，偶数位---变为前一个奇数 键的值 array('前一个奇数'=>'偶数位')
        for ($i = 0; $i < $count; $i += 2) {
            $newArr[$arrData[$i]] = $arrData[$i + 1] ?? '';
        }

        unset($arrData);

        return $newArr;
    }

    /**
     * @param $name
     * @param string $default
     * @return mixed
     */
    public static function serverParam($name, $default = '')
    {
        return self::server($name, $default);
    }

    /**
     * get $_SERVER value
     * @param  string $name
     * @param  string $default
     * @return mixed
     */
    public static function server($name, $default = '')
    {
        $name = \strtoupper($name);

        return isset($_SERVER[$name]) ? \trim($_SERVER[$name]) : $default;
    }
}
