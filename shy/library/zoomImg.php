<?php

/**
 * zoom image
 */

namespace shy\library;

class zoomImg
{
    private $imagePath;
    private $savePath = 'upload/zoom_img';      //上传目录，位于public目录下
    private $isDateDir = true;
    private $saveUrl;

    static private $_instance;

    private function __construct($imagePath)
    {
        $this->imagePath = $imagePath;
        $this->zoom();
    }

    private function __clone()
    {
        // not allow clone outside
    }

    public static function instance($imagePath)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($imagePath);
        }
        return self::$_instance;
    }

    public function zoom($scale = 0.5, $isRandName = false)
    {
        $imagePath = $this->imagePath;
        if (!file_exists($imagePath)) {
            return false;
        }

        $image = $this->openImage($imagePath);

        //缩放比例
        list($src_w, $src_h) = getimagesize($imagePath);
        if ($src_w < 300 && $src_h < 300) {
            $scale = 1; //返回原图
        } else if ($src_w > 800 || $src_h > 800) {
            $scale = 0.3;
        }
        $dst_w = $src_w * $scale;
        $dst_h = $src_h * $scale;
        //缩放
        $newImage = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        //保存路径
        if ($this->checkDir()) {
            $path = $this->savePath;
            $url = $this->saveUrl;
            if ($isRandName) {
                $filename = uniqid('image') . '.' . pathinfo($imagePath)['extension'];
            } else {
                $filename = pathinfo($imagePath)['basename'];
            }
            $path .= $filename;
            $url .= $filename;
        } else {
            return false;
        }
        //保存
        $this->saveImage($newImage, $path);

        imagedestroy($image);
        imagedestroy($newImage);
        $this->savePath = $path;
        $this->saveUrl = $url;
        return true;
    }

    public function getUrl()
    {
        return $this->saveUrl;
    }

    public function getPath()
    {
        return $this->savePath;
    }

    private function openImage($path)
    {
        $extension = pathinfo($path)['extension'];
        if ($extension == 'jpg') {
            $extension = 'jpeg';
        }
        //拼接函数名
        $function = "imagecreatefrom" . $extension;
        if (function_exists($function)) {
            return $function($path);
        } else {
            return false;
        }
    }

    private function checkDir()
    {
        $dir = BASE_PATH . '/../public/' . $this->savePath;
        $url = BASE_URL . $this->savePath;
        if ($this->isDateDir) {
            $date = date('/Y/m/');
            $dir .= $date;
            $url .= $date;
        }
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }
        if (!is_readable($dir) || !is_writable($dir)) {
            if (!chmod($dir, 0777)) {
                return false;
            }
        }
        $this->savePath = $dir;
        $this->saveUrl = $url;

        return true;
    }

    private function saveImage($image, $path)
    {
        $type = pathinfo($path)['extension'];
        if ($type == 'pjpeg' || $type == 'jpg') {
            $type = 'jpeg';
        }
        if ($type == 'bmp') {
            $type = 'wbmp';
        }
        //拼接函数名
        $func = "image" . $type;
        if (function_exists($func)) {
            return $func($image, $path);
        } else {
            return false;
        }
    }

}