<?php
/**
 * Helpers functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('dd')) {
    /**
     * Development output
     *
     * @param mixed $msg
     */
    function dd(...$msg)
    {
        foreach ($msg as $item) {
            var_dump($item);
        }

        exit(0);
    }
}

if (!function_exists('is_valid_ip')) {
    /**
     * Get valid ip
     *
     * @param string $ip
     * @return bool
     */
    function is_valid_ip(string $ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }

        return false;
    }
}

if (!function_exists('get_response_json')) {
    /**
     * Get response json
     *
     * @param int $code
     * @param string|array $msg
     * @param array $data
     * @return string
     */
    function get_response_json(int $code, $msg = null, $data = array())
    {
        if ($msg === null) {
            $msg = lang($code);
        }

        return json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data), JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('get_array_key')) {
    /**
     * Get key in array
     *
     * @param array $keyLevels
     * @param array $array
     * @return array
     */
    function get_array_key(array $keyLevels, array $array)
    {
        $currentLevel = array_shift($keyLevels);

        if (isset($array[$currentLevel])) {
            if (empty($keyLevels)) {
                return $array[$currentLevel];
            } else {
                return get_array_key($keyLevels, $array[$currentLevel]);
            }
        } elseif (empty($keyLevels)) {
            return $array;
        }
    }
}
