<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id Coser
 * @property string $photo 照片
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPhoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPhoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPhoto query()
 * @mixin \Eloquent
 */
class UserPhoto extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_photo';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',
        'photo',
        'created_at',
        'updated_at',
    ];
    
    
}
