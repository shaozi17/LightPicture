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

use app\model\Images as ImagesModel;
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
     * 创建文件
     *
     * @param $file
     * @param $sid
     */
    public function create(FileClass $file, $sid)
    {
        $this->storage = StorageModel::find($sid);

        //检查文件是否已存在
        $img = ImagesModel::where([
            'hash'       => $file->getHash(),
            'storage_id' => $sid
        ])->find();
        if ($img) {
            return array(
                'img' => $img,
                'state' => 2,
            );
        }

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
     * @param  FileClass  $file
     */
    public function location_upload(FileClass $file)
    {
        $tmp_file = $file->tmpFile['tmp_name'];

        // 本地上传
        $uploadPath = FOLDER . $file->getFilePath();
        is_dir(dirname($uploadPath)) || mkdir(dirname($uploadPath), 0777, true);

        if (move_uploaded_file($tmp_file, $uploadPath)) {
            return array(
                'hash'  => $file->getHash(),
                'path'  => $uploadPath,
                'name'  => $file->getFileName(),
                'url'   => $file->getFilePath(),
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
     * @param  FileClass  $file
     */
    public function aliyuncs_upload(FileClass $file)
    {
        $tmp_file = $file->tmpFile['tmp_name'];
        try {
            $ossClient = new OssClient($this->storage['AccessKey'], $this->storage['SecretKey'], $this->storage['region']);
            $ossClient->uploadFile($this->storage['bucket'], $file->getFilePath(), $tmp_file);
            return array(
                'hash' => $file->getHash(),
                'path' => $file->getFilePath(),
                'name' => $file->getFileName(),
                'url' => $file->getFilePath(),
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
     * @param  FileClass  $file
     */
    function tencent_upload(FileClass $file)
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
        $tmp_file = $file->tmpFile['tmp_name'];
        try {
            $cosClient->upload(
                $bucket = $this->storage['bucket'], //格式：BucketName-APPID
                $key = $file->getFilePath(),
                $body = fopen($tmp_file, 'rb')
            );
            return array(
                'hash' => $file->getHash(),
                'path' => $file->getFilePath(),
                'name' => $file->getFileName(),
                'url' => $file->getFilePath(),
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
     * @param  FileClass  $file
     */
    function qiniu_upload(FileClass $file)
    {
        $auth = new Auth($this->storage['AccessKey'], $this->storage['SecretKey']);
        // 生成上传 Token
        $token = $auth->uploadToken($this->storage['bucket']);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 要上传文件的本地路径
        $tmp_file = $file->tmpFile['tmp_name'];
        // 上传到七牛后保存的文件名
        list($ret, $err) = $uploadMgr->putFile($token, $file->getFilePath(), $tmp_file);

        if ($err !== null) {
            return array(
                'msg' => $err,
                'state' => 0,
            );
        } else {
            return array(
                'hash'  => $file->getHash(),
                'path'  => $file->getFilePath(),
                'name'  => $file->getFileName(),
                'url'   => $file->getFilePath(),
                'state' => 1,
            );
        }
    }


    /**
     * 又拍云上传方法
     * @param  FileClass  $file
     */
    function upyun_upload(FileClass $file)
    {
        $serviceConfig = new Config($this->storage['bucket'], $this->storage['AccessKey'], $this->storage['SecretKey']);
        $client = new Upyun($serviceConfig);
        $tmp_file = $file->tmpFile['tmp_name'];
        try {
            $client->write($file->getFilePath(), fopen($tmp_file, 'r'));
            return array(
                'hash'  => $file->getHash(),
                'path'  => $file->getFilePath(),
                'name'  => $file->getFileName(),
                'url'   => $file->getFilePath(),
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
     * @param FileClass  $file
     */
    function hwyun_upload(FileClass $file)
    {
        $obsClient = new ObsClient([
            'key' => $this->storage['AccessKey'],
            'secret' => $this->storage['SecretKey'],
            'endpoint' => $this->storage['region']
        ]);
        $tmp_file = $file->tmpFile['tmp_name'];
        try {
            $obsClient->putObject([
                'Bucket' => $this->storage['bucket'],
                'Key' => $file->getFilePath(),
                'SourceFile' => $tmp_file  // localfile为待上传的本地文件路径，需要指定到具体的文件名
            ]);
            return array(
                'hash'  => $file->getHash(),
                'path'  => $file->getFilePath(),
                'name'  => $file->getFileName(),
                'url'   => $file->getFilePath(),
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
