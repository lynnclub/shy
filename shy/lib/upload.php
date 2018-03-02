<?php

/**
 * upload
 */

namespace shy\lib;

class upload
{
    private $file;                       //上传文件
    private $fileRealType;               //文件头部类型
    private $uploadDir = 'upload/';      //上传目录，位于public目录下
    private $maxSize = 3;                //最大尺寸 单位Mb
    private $isRandName = true;          //是否开启随机文件名
    private $isDateDir = true;           //是否开启日期目录
    private $fileUrl;                    //上传后的文件路径
    private $filePath;                   //上传后的文件路径
    private $errNo;                      //错误号
    private $allowedType = ['jpg', 'png', 'doc', 'pdf', 'rar', 'zip'];

    static private $_instance;

    /**
     * upload constructor.
     *
     * @param array $options
     */
    private function __construct($options)
    {
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if (property_exists(__CLASS__, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    private function __clone()
    {
        // not allow clone outside
    }

    public static function instance($options = [])
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($options);

        }
        return self::$_instance;
    }


    /**
     * upload
     *
     * @param string $fieldName
     * @return bool|string
     */
    public function upload($fieldName = 'file')
    {
        if (!$this->checkUploadFile($fieldName)) {
            return false;
        }

        if (!$this->moveFile()) {
            return false;
        }

        return true;
    }

    public function getFileUrl()
    {
        return $this->fileUrl;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getUploadFileName()
    {
        return $this->file['name'];
    }

    public function getErrorInfo($english = false)
    {
        $error = [
            //php error
            0 => '文件上传成功',
            1 => '文件大小超出ini限制',
            2 => '文件大小超出HTML限制',
            3 => '文件只有部分被上传',
            4 => '没有文件被上传',
            6 => '找不到上传文件夹',
            7 => '文件写入失败',
            //custom error
            -1 => '没有上传文件信息',
            -2 => '上传文件不存在',
            -3 => '文件不得超过' . $this->maxSize . 'Mb',
            -4 => '文件类型只允许：' . implode(',', $this->allowedType),
            -5 => '保存路径创建失败',
            -6 => '保存路径不可读写',
            -7 => '文件保存失败',
        ];
        $error_en = [
            //php error
            0 => 'The file uploaded with success',
            1 => 'The uploaded file exceeds the directive in ini',
            2 => 'The uploaded file exceeds the directive that was specified in the HTML',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            //custom error
            -1 => 'No uploaded file info',
            -2 => 'The uploaded file not exist',
            -3 => 'file size limit ' . $this->maxSize . 'Mb',
            -4 => 'file type allowed:' . implode(',', $this->allowedType),
            -5 => 'make save path fail',
            -6 => 'save path not writable',
            -7 => 'save file fail',
        ];
        if ($english) {
            return $error_en[$this->errNo];
        } else {
            return $error[$this->errNo];
        }
    }

    private function checkUploadFile($fieldName)
    {
        $file = $_FILES[$fieldName];
        if (!isset($file['name'], $file['type'], $file['tmp_name'], $file['error'], $file['size'])) {
            $this->errNo = -1;
            return false;
        }
        //php error
        if ($file['error']) {
            $this->errNo = $file['error'];
            return false;
        }
        //file exist
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->errNo = -2;
            return false;
        }
        //size
        if ($file['size'] > $this->maxSize * 1024 * 1024) {
            $this->errNo = -3;
            return false;
        }
        $this->file = $file;
        //type
        $this->getFileRealType();
        if (!in_array($this->fileRealType, $this->allowedType)) {
            $this->errNo = -4;
            return false;
        }

        return true;
    }

    private function moveFile()
    {
        if (!$this->checkDir()) {
            return false;
        }

        $path = $this->uploadDir;
        $url = $this->fileUrl;

        if ($this->isRandName) {
            $filename = uniqid() . '.' . $this->fileRealType;
        } else {
            $filename = $this->file['name'];

        }
        $path .= $filename;
        $url .= $filename;

        if (!move_uploaded_file($this->file['tmp_name'], $path)) {
            $this->errNo = -7;
            return false;
        }

        $this->fileUrl = $url;
        $this->filePath = $path;
        $this->errNo = 0;
        return true;
    }

    private function checkDir()
    {
        $dir = BASE_PATH . '/../public/' . $this->uploadDir;
        $url = BASE_URL . $this->uploadDir;

        if ($this->isDateDir) {
            $date = date('Y/m/');
            $dir .= $date;
            $url .= $date;
        }

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                $this->errNo = -5;
                return false;
            }
        }

        if (!is_readable($dir) || !is_writable($dir)) {
            if (!chmod($dir, 0777)) {
                $this->errNo = -6;
                return false;
            }
        }

        $this->uploadDir = $dir;
        $this->fileUrl = $url;

        return true;
    }

    private function getFileRealType()
    {
        if (!empty($this->fileRealType)) {
            return $this->fileRealType;
        }

        $file = @fopen($this->file['tmp_name'], "rb");
        $bin = @fread($file, 2); //只读2字节
        @fclose($file);
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
        switch ($typeCode) {
            case 7790:
                $file_type = 'exe';
                break;
            case 7784:
                $file_type = 'midi';
                break;
            case 8075:
                $file_type = $this->getFileType(); //docx，xlsx文件也是8075，本质上是一个ZIP文件
                break;
            case 8297:
                $file_type = 'rar';
                break;
            case 255216:
                $file_type = 'jpg';
                break;
            case 7173:
                $file_type = 'gif';
                break;
            case 6677:
                $file_type = 'bmp';
                break;
            case 13780:
                $file_type = 'png';
                break;
            case 208207: {
                $file_type = $this->getFileType();    //excel也是208207
                break;
            }
            case 3780: {
                $file_type = 'pdf';
                break;
            }
            default:
                $file_type = 'unknown';
                break;
        }

        $this->fileRealType = $file_type;
    }

    private function getFileType()
    {
        $info = pathinfo($this->file['name']);
        if (empty($info['extension'])) {
            return false;
        }
        return $info['extension'];
    }

}