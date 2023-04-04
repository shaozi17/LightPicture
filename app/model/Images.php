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
namespace app\model;

use think\Model;
use think\Exception;

class Images extends Model
{
    public function storage()
    {
        return $this->belongsTo(Storage::class, 'storage_id', 'id');
    }

    public function getUrlAttr($value, $data)
    {
        return $this->storage->space_domain . '/' . $value;
    }

    public function getUrlPathAttr($value, $data)
    {
        return $data['url'];
    }

    public static function getImgs($role, $data)
    {
        if ($role['is_admin'] == 1 || $role['is_read_all'] == 1) {
            $result = self::where('name', 'like', '%' .  $data['name'] . '%')->order('id desc')->paginate([
                'list_rows' => (int)$data['size'],
                'page' => (int)$data['page'],
            ]);
            return $result;
        } else if ($role['is_read'] == 1) {
            $result = self::where('storage_id', $role['storage_id'])->where('name', 'like', '%' .  $data['name'] . '%')->order('id desc')->paginate([
                'list_rows' => (int)$data['size'],
                'page' => (int)$data['page'],
            ]);
            return $result;
        } else {
            $result = self::where('user_id', $data['uid'])->where('name', 'like', '%' .  $data['name'] . '%')->order('id desc')->paginate([
                'list_rows' => (int)$data['size'],
                'page' => (int)$data['page'],
            ]);
            return $result;
        }
    }
    public static function delImgs($id)
    {
        try {
            self::destroy($id);
        } catch (\Error $e) {
            throw new Exception($e->getMessage());
        }
    }
}
