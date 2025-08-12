<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;




/**
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $name 优惠券名称
 * @property string|null $amount 优惠金额
 * @property string $with_amount 满足金额
 * @property string|null $expire_time 过期时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCoupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCoupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCoupon query()
 * @property string|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCoupon onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCoupon withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCoupon withoutTrashed()
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UserCoupon extends Base
{

    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_coupon';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'expire_time',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    
    
}
