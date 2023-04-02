<?php
// +----------------------------------------------------------------------
// | LightPicture [ 图床 ]
// +----------------------------------------------------------------------
// | 企业团队图片资源管理系统
// +----------------------------------------------------------------------
// | Github: https://github.com/osuuu/LightPicture
// +----------------------------------------------------------------------
// | Copyright © http://picture.h234.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Team <admin@osuu.net>
// +----------------------------------------------------------------------
declare(strict_types=1);

namespace app\services;

use app\model\Storage as StorageModel;
use OSS\OssClient;
use OSS\Core\OssException;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qcloud\Cos\Client;
use Upyun\Upyun;
use Upyun\Config;
use Obs\ObsClient;

class UploadCLass
{
    /**
     * 当前储存策略参数
     *
     * @var array
     */
    protected $storage = [];

    /**
     * 文件名
     */
    private $fileName;

    /**
     * 文件路径
     */
    private $filePath;

    /**
     * 文件后缀
     */
    private $fileExt;

    /**
     * 储存目录
     */
    private $folder;

    /**
     * 文件哈希值
     */
    private string $fileHash = '';

    public function __construct($folder = '')
    {
        $this->folder = $folder;
    }

    //获取文件后缀
    public function getFileExt($name)
    {
        return pathinfo($name, PATHINFO_EXTENSION);
    }

    // 生成文件名
    public function getFileName($file_path)
    {
        $hash = $this->getHash($file_path);
        return substr($hash, 2) . '.' . $this->fileExt;
    }

    // 生成上传路径
    public function getUploadPath($file_path, $folder = '')
    {
        $hash = $this->getHash($file_path);
        $path = FOLDER . ($folder ? $folder . DIRECTORY_SEPARATOR : '') . substr($hash, 0, 2) . DIRECTORY_SEPARATOR;
        is_dir($path) || mkdir($path, 0777, true);
        return $path;
    }

    public function getHash($file_path, $algo = 'md5')
    {
        $this->fileHash || $this->fileHash = hash_file($algo, $file_path);
        return $this->fileHash;
    }

    /**
     * 创建文件
     *
     * @param $file
     * @param $sid
     */
    public function create($file, $sid)
    {
        $this->storage = StorageModel::find($sid);

        $this->fileExt = $this->getFileExt($file['name']);
        $this->fileName = $this->getFileName($file['tmp_name']);
        $this->filePath = $this->getUploadPath($file['tmp_name'], $this->folder) . $this->fileName;

        switch ($this->storage['type']) {
            case 'local':
                return $this->location_upload($file);
                break;
            case 'cos':
                return $this->tencent_upload($file);
                break;
            case 'oss':
                return $this->aliyuncs_upload($file);
                break;
            case 'uss':
                return $this->upyun_upload($file, $sid);
                break;
            case 'obs':
                return $this->hwyun_upload($file, $sid);
                break;
            case 'kodo':
                return $this->qiniu_upload($file);
                break;
            default:
        }
    }

    /**
     * 删除文件
     *
     * @param $path
     * @param $sid
     */
    public function delete($path, $sid)
    {
        $this->storage = StorageModel::find($sid);
        switch ($this->storage['type']) {
            case 'local': // 本地
                unlink($path);
                break;
            case 'cos': // 腾讯云
                return $this->tencent_delete($path);
                break;
            case 'obs': // 华为云
                return $this->hwyun_delete($path);
                break;
            case 'oss': // 阿里云
                $ossClient = new OssClient($this->storage['AccessKey'], $this->storage['SecretKey'], $this->storage['region']);
                $ossClient->deleteObject($this->storage['bucket'], $path);
                break;
            case 'uss': // 又拍云
                $serviceConfig = new Config($this->storage['bucket'], $this->storage['AccessKey'], $this->storage['SecretKey']);
                $client = new Upyun($serviceConfig);
                $client->delete($path);
                break;
            case 'kodo': // 七牛云
                $auth = new Auth($this->storage['AccessKey'], $this->storage['SecretKey']);
                $config = new \Qiniu\Config();
                $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
                list($Info, $err) = $bucketManager->delete($this->storage['bucket'], $path);
                break;
            default:
        }
    }

    /**
     * 本地上传方法
     * @param  \think\Request  $file
     */
    function location_upload($file)
    {

        // 获取网站协议
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        $tmp_file = $file['tmp_name'];

        // 本地上传
        if (move_uploaded_file($tmp_file, $this->filePath)) {
            $url = $protocol . $_SERVER['HTTP_HOST'] . '/' . $this->filePath;
            return array(
                'path' => $this->filePath,
                'name' => $this->fileName,
                'url' => $url,
                'state' => 1,
            );
        } else {
            return array(
                'msg' => '上传失败',
                'state' => 0,
            );
        }
    }


    /**
     * 阿里云OSS上传方法
     * @param  \think\Request  $file
     */
    public function aliyuncs_upload($file)
    {
        $tmp_file = $file['tmp_name'];
        try {
            $ossClient = new OssClient($this->storage['AccessKey'], $this->storage['SecretKey'], $this->storage['region']);
            $ossClient->uploadFile($this->storage['bucket'], $this->filePath, $tmp_file);
            return array(
                'path' => $this->filePath,
                'name' => $this->fileName,
                'url' => $this->storage['space_domain'] . '/' . $this->filePath,
                'state' => 1,
            );
        } catch (OssException $e) {
            return array(
                'msg' => $e->getMessage(),
                'state' => 0,
            );
        }
    }

    /**
     * 腾讯云cos上传方法
     * @param  \think\Request  $file
     */
    function tencent_upload($file)
    {
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $this->storage['region'],
                'schema' => 'http', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $this->storage['AccessKey'],
                    'secretKey' => $this->storage['SecretKey']
                )
            )
        );
        $tmp_file = $file['tmp_name'];
        try {
            $cosClient->upload(
                $bucket = $this->storage['bucket'], //格式：BucketName-APPID
                $key = $this->filePath,
                $body = fopen($tmp_file, 'rb')
            );
            return array(
                'path' => $this->filePath,
                'name' => $this->fileName,
                'url' => $this->storage['space_domain'] . '/' . $this->filePath,
                'state' => 1,
            );
        } catch (\Exception $e) {
            return array(
                'msg' => $e->getMessage(),
                'state' => 0,
            );
        }
    }

    /**
     * 腾讯云cos删除方法
     * @param  \think\Request  $file
     */
    function tencent_delete($path)
    {
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $this->storage['region'],
                'schema' => 'http', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $this->storage['AccessKey'],
                    'secretKey' => $this->storage['SecretKey']
                )
            )
        );
        $cosClient->deleteObject(array(
            'Bucket' => $this->storage['bucket'], //格式：BucketName-APPID
            'Key' => $path,
            // 'VersionId' => 'exampleVersionId' //存储桶未开启版本控制时请勿携带此参数
        ));
    }



    /**
     * 七牛云上传方法
     * @param  \think\Request  $file
     */
    function qiniu_upload($file)
    {
        $auth = new Auth($this->storage['AccessKey'], $this->storage['SecretKey']);
        // 生成上传 Token
        $token = $auth->uploadToken($this->storage['bucket']);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 要上传文件的本地路径
        $tmp_file = $file['tmp_name'];
        // 上传到七牛后保存的文件名
        list($ret, $err) = $uploadMgr->putFile($token, $this->filePath, $tmp_file);

        if ($err !== null) {
            return array(
                'msg' => $err,
                'state' => 0,
            );
        } else {
            return array(
                'path' => $this->filePath,
                'name' => $this->fileName,
                'url' => $this->storage['space_domain'] . '/' . $this->filePath,
                'state' => 1,
            );
        }
    }


    /**
     * 又拍云上传方法
     * @param  \think\Request  $file
     */
    function upyun_upload($file)
    {
        $serviceConfig = new Config($this->storage['bucket'], $this->storage['AccessKey'], $this->storage['SecretKey']);
        $client = new Upyun($serviceConfig);
        $tmp_file = $file['tmp_name'];
        try {
            $client->write($this->filePath, fopen($tmp_file, 'r'));
            return array(
                'path' => $this->filePath,
                'name' => $this->fileName,
                'url' => $this->storage['space_domain'] . '/' . $this->filePath,
                'state' => 1,
            );
        } catch (\Exception $e) {
            return array(
                'msg' => $e->getMessage(),
                'state' => 0,
            );
        }
    }

    /**
     * 华为云上传方法
     * @param  \think\Request  $file
     */
    function hwyun_upload($file)
    {
        $obsClient = new ObsClient([
            'key' => $this->storage['AccessKey'],
            'secret' => $this->storage['SecretKey'],
            'endpoint' => $this->storage['region']
        ]);
        $tmp_file = $file['tmp_name'];
        try {
            $obsClient->putObject([
                'Bucket' => $this->storage['bucket'],
                'Key' => $this->filePath,
                'SourceFile' => $tmp_file  // localfile为待上传的本地文件路径，需要指定到具体的文件名
            ]);
            return array(
                'path' => $this->filePath,
                'name' => $this->fileName,
                'url' => $this->storage['space_domain'] . '/' . $this->filePath,
                'state' => 1,
            );
        } catch (\Exception $e) {
            return array(
                'msg' => $e->getMessage(),
                'state' => 0,
            );
        }
    }

    /**
     * 华为云删除方法
     * @param  \think\Request  $file
     */
    function hwyun_delete($path)
    {
        $obsClient = new ObsClient([
            'key' => $this->storage['AccessKey'],
            'secret' => $this->storage['SecretKey'],
            'endpoint' => $this->storage['region']
        ]);

        $obsClient->deleteObject([
            'Bucket' => $this->storage['bucket'],
            'Key' => $path,
        ]);
    }
}
