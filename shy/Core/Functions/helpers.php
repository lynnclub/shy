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

if (!function_exists('mime')) {
    /**
     * Get mime
     *
     * @param string $type
     * @return string
     */
    function mime(string $type)
    {
        $mime = [
            //applications
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'exe' => 'application/octet-stream',
            'doc' => 'application/vnd.ms-word',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pps' => 'application/vnd.ms-powerpoint',
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'swf' => 'application/x-shockwave-flash',
            // archives
            'gz' => 'application/x-gzip',
            'tgz' => 'application/x-gzip',
            'bz' => 'application/x-bzip2',
            'bz2' => 'application/x-bzip2',
            'tbz' => 'application/x-bzip2',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar',
            'tar' => 'application/x-tar',
            '7z' => 'application/x-7z-compressed',
            // texts
            'txt' => 'text/plain',
            'php' => 'text/x-php',
            'html' => 'text/html',
            'htm' => 'text/html',
            'js' => 'text/javascript',
            'css' => 'text/css',
            'rtf' => 'text/rtf',
            'rtfd' => 'text/rtfd',
            'py' => 'text/x-python',
            'java' => 'text/x-java-source',
            'rb' => 'text/x-ruby',
            'sh' => 'text/x-shellscript',
            'pl' => 'text/x-perl',
            'sql' => 'text/x-sql',
            // images
            'bmp' => 'image/x-ms-bmp',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'tga' => 'image/x-targa',
            'psd' => 'image/vnd.adobe.photoshop',
            //audio
            'mp3' => 'audio/mpeg',
            'mid' => 'audio/midi',
            'ogg' => 'audio/ogg',
            'mp4a' => 'audio/mp4',
            'wav' => 'audio/wav',
            'wma' => 'audio/x-ms-wma',
            // video
            'avi' => 'video/x-msvideo',
            'dv' => 'video/x-dv',
            'mp4' => 'video/mp4',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mov' => 'video/quicktime',
            'wm' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'mkv' => 'video/x-matroska',
        ];

        if (isset($mime[$type])) {
            return $mime[$type];
        }

        return '';
    }
}
