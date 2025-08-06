<?php

namespace app\admin\model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use plugin\admin\app\model\Base;


/**
 * @property int $id 主键
 * @property int $user_id 用户
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserTime query()
 * @property string $status 预约状态:available=可约,unavailable=不可约,booked=已被预约
 * @property int|null $order_id 订单ID
 * @property \Illuminate\Support\Carbon $time 时间
 * @method static Builder<static>|UserTime gtNow()
 * @property-read mixed $time_formatted
 * @property-read mixed $status_text
 * @mixin \Eloquent
 */
class UserTime extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_time';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'time',
        'status',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'time' => 'datetime',
    ];

    protected $appends = [
        'time_formatted',
        'status_text',
    ];

    function scopeGtNow(Builder $query)
    {
        $query->where('time', '>=',  Carbon::now())->orderBy('id');
    }

    public function getTimeFormattedAttribute()
    {
        return $this->time ? $this->time->format('H:i') : null;
    }

    public function getStatusTextAttribute()
    {
        return [
            'available' => '可预约',
            'unavailable' => '不可约预',
            'booked' => '已被预约',
        ][$this->status] ?? '';
    }
    
    
    
}
