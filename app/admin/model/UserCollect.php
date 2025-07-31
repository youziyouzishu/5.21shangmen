<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * @property int $id 主键
 * @property int $user_id 用户
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCollect newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCollect newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCollect query()
 * @property int $coser_id Coser
 * @property-read \app\admin\model\User|null $coser
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UserCollect extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_collect';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    protected $fillable = ['user_id', 'coser_id'];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function coser()
    {
        return $this->belongsTo(User::class, 'coser_id', 'id');
    }
}
