<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;



/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id Corser
 * @property int $costume_id 服饰
 * @property string $image 封面
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCostume newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCostume newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCostume query()
 * @mixin \Eloquent
 */
class UserCostume extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_costume_image';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',
        'costume_id',
        'image',
        'created_at',
        'updated_at',
    ];
    
    
    
}
