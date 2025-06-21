<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $order_id 订单
 * @property int $coser_id Coser
 * @property int $grade 评分(1-5)
 * @property string|null $tags 标签
 * @property int $cryptonym 匿名:0=否,1=是
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderComment query()
 * @property-read \app\admin\model\User|null $coser
 * @property-read \app\admin\model\Order|null $orders
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class OrderComment extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_orders_comment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'order_id',
        'coser_id',
        'grade',
        'tags',
        'cryptonym'
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    function coser()
    {
        return $this->belongsTo(User::class, 'coser_id','id');
    }

    function orders()
    {
        return $this->belongsTo(Order::class, 'order_id','id');
    }


    
    
}
