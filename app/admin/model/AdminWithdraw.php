<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $admin_id 代理商
 * @property string $amount 提现金额
 * @property string $ordersn 订单编号
 * @property string $name 姓名
 * @property string $bank_name 银行名称
 * @property string $bank_account 银行卡号
 * @property integer $status 状态:0=待审核,1=已打款,2=驳回
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminWithdraw query()
 * @mixin \Eloquent
 */
class AdminWithdraw extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_admin_withdraw';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
