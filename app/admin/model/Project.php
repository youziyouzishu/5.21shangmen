<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property string $name 项目名称
 * @property string $price 价格
 * @property string $image 封面
 * @property string $images 轮播图
 * @property string $tag 标签
 * @property int $minutes 分钟数
 * @property int $sales 销量
 * @property string|null $content 详情
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Project query()
 * @mixin \Eloquent
 */
class Project extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_project';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'price',
        'image',
        'images',
        'tag',
        'minutes',
        'sales',
        'content',
    ];



}
