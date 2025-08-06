<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户
 * @property string $amount 提现金额
 * @property integer $pay_type 提现方式:1=微信,2=支付宝
 * @property integer $status 状态:1=待收款,2=提现成功
 * @property string $ordersn 订单编号
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string|null $ali_account 支付宝账号
 * @property string|null $ali_name 支付宝姓名
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserWithdraw query()
 * @property string|null $openid 微信标识
 * @property string|null $package_info pkg
 * @property string|null $mchid 商户id
 * @property string|null $appid appid
 * @mixin \Eloquent
 */
class UserWithdraw extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_withdraw';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = ['user_id','amount','pay_type','status','ordersn','ali_account','ali_name','openid','mchid','appid','package_info'];
    
    
}
