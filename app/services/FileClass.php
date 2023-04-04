<?php

declare(strict_types=1);

namespace app\services;

class FileClass
{
    /**
     * tmpFile
     */
    public $tmpFile = [];

    /**
     * 文件名
     */
    public string $fileName;

    /**
     * 文件路径
     */
    public string $filePath;

    /**
     * 文件后缀
     */
    public string $fileExt;

    /**
     * 储存目录
     */
    public string $folder;

    /**
     * 文件哈希值
     */
    public string $fileHash;

    public function __construct($file, $folder = '')
    {
        $this->tmpFile = $file;
        $this->folder = $folder;
    }

    //获取文件后缀
    public function getFileExt(): string
    {
        return $this->fileExt ?? $this->fileExt = pathinfo($this->tmpFile['name'], PATHINFO_EXTENSION);
    }

    // 生成文件名
    public function getFileName(): string
    {
        return $this->fileName ?? $this->fileName = substr($this->getHash(), 2) . '.' . $this->getFileExt();
    }

    // 生成文件路径
    public function getFilePath(): string
    {
        if (in_array($this->getFileExt(), ['jpg', 'jpeg', 'gif', 'png', 'ico', 'svg', 'bmp', 'wbpm'])) {
            $folders = ['images', $this->folder];
        } else {
            $folders = ['other', $this->folder];
        }

        $this->folder = implode(DIRECTORY_SEPARATOR, array_filter($folders)) . DIRECTORY_SEPARATOR;

        return $this->filePath ?? $this->filePath = $this->folder . substr($this->getHash(), 0, 2) . DIRECTORY_SEPARATOR . $this->getFileName();
    }

    public function getHash($algo = 'md5'): string
    {
        return $this->fileHash ?? $this->fileHash = hash_file($algo, $this->tmpFile['tmp_name']);
    }
}
