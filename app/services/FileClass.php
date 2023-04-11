<?php

declare(strict_types=1);

namespace app\services;

class FileClass
{
    /**
     * @var \think\file\UploadedFile
     */
    public $tmpFile;

    /**
     * 文件名
     */
    public string $fileName;

    /**
     * 文件目录
     */
    public string $fileDir;

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

    /**
     * 获取文件哈希的方式
     */
    private string $hashType = 'md5';

    /**
     * @param \think\file\UploadedFile $file
     * @param string $folder 上传目录
     */
    public function __construct(\think\file\UploadedFile $file, $folder = '')
    {
        $this->tmpFile = $file;
        $this->folder = $folder;
    }

    //获取文件后缀
    public function getFileExt(): string
    {
        return $this->fileExt ?? $this->fileExt = $this->tmpFile->getOriginalExtension();
    }

    // 生成文件名
    public function getFileName(): string
    {
        return $this->fileName ?? $this->fileName = substr($this->getHash(), 2) . '.' . $this->getFileExt();
    }

    // 获取文件目录
    public function getFileDir(): string
    {
        $this->folder = trim($this->folder, DIRECTORY_SEPARATOR);
        return $this->fileDir ?? $this->fileDir = $this->folder . DIRECTORY_SEPARATOR . substr($this->getHash(), 0, 2) . DIRECTORY_SEPARATOR;
    }

    // 获取文件路径
    public function getFilePath(): string
    {
        return $this->filePath ?? $this->filePath = $this->getFileDir() . $this->getFileName();
    }

    public function getHash(): string
    {
        return $this->fileHash ?? $this->fileHash = $this->tmpFile->hash($this->hashType);
    }

    /**
     * 上传文件
     * @access public
     * @param string      $directory 保存路径
     * @param string|null $name      保存的文件名
     * @return File
     */
    public function move(string $directory, string $name = null): \think\File
    {
        return $this->tmpFile->move($directory, $name);
    }
}
