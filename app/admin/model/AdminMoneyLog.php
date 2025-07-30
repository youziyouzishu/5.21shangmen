<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property int $admin_id 代理
 * @property string $money 变更余额
 * @property string $before 变更前余额
 * @property string $after 变更后余额
 * @property string|null $memo 备注
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminMoneyLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminMoneyLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminMoneyLog query()
 * @property-read \app\admin\model\Admin|null $admin
 * @mixin \Eloquent
 */
class AdminMoneyLog extends Base
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_admin_money_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'admin_id',
        'money',
        'before',
        'after',
        'memo',
    ];

    function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }


}
