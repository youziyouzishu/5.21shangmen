<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $coser_id Cosor
 * @property string $project_amount 项目金额
 * @property string $fare_amount 车费
 * @property int $order_count 订单数量
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoserReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoserReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoserReport query()
 * @property \Illuminate\Support\Carbon $date 日期
 * @mixin \Eloquent
 */
class CoserReport extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_coser_report';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'coser_id',
        'date',
        'project_amount',
        'fare_amount',
        'order_count',
    ];

    protected $casts = [
        'date' => 'date'
    ];
    
    
    
}
