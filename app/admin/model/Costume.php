<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;



/**
 * 
 *
 * @property int $id 主键
 * @property string $image 图片
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Costume newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Costume newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Costume query()
 * @property string $name 名称
 * @mixin \Eloquent
 */
class Costume extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_costume';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'name',
        'image',
        'created_at',
        'updated_at',
    ];


}
