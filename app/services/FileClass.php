<?php

declare(strict_types=1);

namespace app\services;

class FileClass
{
    /**
     * @var \think\File
     */
    public $tmpFile;

    /**
     * 文件大小
     */
    public int $fileSize;

    /**
     * 文件Mime
     */
    public string $fileMime;

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

    private static $mimes;

    /**
     * @param \think\File $file
     * @param string $folder 上传目录
     */
    public function __construct(\think\File $file, $folder = '')
    {
        $this->tmpFile = $file;
        $this->folder = $folder;
    }

    //获取文件后缀
    public function getFileExt(): string
    {
        return $this->fileExt ?? $this->fileExt = $this->tmpFile->extension();
    }

    // 生成文件名
    public function getFileName(): string
    {
        return $this->fileName ?? $this->fileName = substr($this->getHash(), 2) . '.' . $this->getFileExt();
    }

    // 获取文件目录
    public function getFileDir(): string
    {
        $this->folder = trim(strtolower($this->folder), DIRECTORY_SEPARATOR);
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

    public function getFileSize(): int
    {
        return $this->fileSize ?? $this->fileSize = $this->tmpFile->getSize();
    }

    public function getFileMime(): string
    {
        if (!isset($this->fileMime)) {
            if ($this->tmpFile instanceof \think\file\UploadedFile) {
                $this->fileMime = $this->tmpFile->getOriginalMime();
            } else {
                self::$mimes = self::$mimes ?? require(root_path() . 'extend/mimes.php');
                $ext = strtolower(pathinfo($this->tmpFile->getPathname(), PATHINFO_EXTENSION));
                $this->fileMime = self::$mimes[$ext];
            }
        }

        return $this->fileMime;
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
