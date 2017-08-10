<?php
namespace app\admin\model;

use think\Model;

class SaleRecords extends Model
{

    // 设置完整的数据表（包含前缀）
    protected $table = 'sale_records';

    // 关闭自动写入时间戳
    //protected $autoWriteTimestamp = false;

    //默认时间格式
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $type       = [
        // 设置时间戳类型（整型）
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    //自动完成
    protected $insert = [
        'created_at',
        'updated_at',
    ];

    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $update = ['updated_at'];

    protected function getExchangeStatusAttr($value) {
        $status = [ 0 => '未兑换', 1 => '已兑换'];
        return $status[$value];
    }
    // status属性读取器
//    protected function getStatusAttr($value)
//    {
//        $status = [-1 => '删除', 0 => '草稿', 1 => '发布',2 => '待审核'];
//        return $status[$value];
//    }

}

