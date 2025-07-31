<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户
 * @property string $class_name 分类名称
 * @property string $content 内容
 * @property mixed $images 图片
 * @property string $truename 姓名
 * @property string $mobile 电话
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advice query()
 * @mixin \Eloquent
 */
class Advice extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_advice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'user_id',
        'class_name',
        'content',
        'images',
        'truename',
        'mobile',
        'created_at',
        'updated_at'
    ];
    
    
    
}
