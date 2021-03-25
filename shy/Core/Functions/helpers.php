<?php
/**
 * Helpers functions
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

if (!function_exists('get_array_key')) {
    /**
     * Get key in array
     *
     * @param array $key_levels
     * @param array $array
     * @return string|array
     */
    function get_array_key(array $key_levels, array $array)
    {
        $currentLevel = array_shift($key_levels);

        if (is_null($currentLevel)) {
            return $array;
        } elseif (isset($array[$currentLevel])) {
            if (empty($key_levels)) {
                return $array[$currentLevel];
            } elseif (is_array($array[$currentLevel])) {
                return get_array_key($key_levels, $array[$currentLevel]);
            }
        }

        return '';
    }
}

if (!function_exists('empty_or_splice')) {
    /**
     * Empty or splice
     *
     * @param string $string
     * @param string $splice
     * @param bool $is_prefix
     * @return string
     */
    function empty_or_splice(string $string, string $splice = '', bool $is_prefix = TRUE)
    {
        if (empty($string)) {
            return '';
        } else {
            if ($is_prefix) {
                return $splice . $string;
            } else {
                return $string . $splice;
            }
        }
    }
}

if (!function_exists('mime')) {
    /**
     * Get mime
     *
     * @param string $type
     * @param bool $flip
     * @return string
     */
    function mime(string $type, bool $flip = FALSE)
    {
        $mime = [
            //applications
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'exe' => 'application/octet-stream',
            'dll' => 'application/x-msdownload',
            'doc' => 'application/vnd.ms-word',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pps' => 'application/vnd.ms-powerpoint',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pdf' => 'application/pdf',
            'xml' => 'application/xml',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'swf' => 'application/x-shockwave-flash',
            'js' => 'application/javascript',
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
            'shtml' => 'text/html',
            'xhtml' => 'text/html',
            'jhtml' => 'text/html',
            'jsp' => 'text/html',
            'jspx' => 'text/html',
            'asp' => 'text/html',
            'aspx' => 'text/html',
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
            'ico' => 'image/x-icon',
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

        if ($flip) {
            $mime = array_flip($mime);
        }

        if (isset($mime[$type])) {
            return $mime[$type];
        }

        return '';
    }
}
