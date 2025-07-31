<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $name 名称
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdviceClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdviceClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdviceClass query()
 * @mixin \Eloquent
 */
class AdviceClass extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_advice_class';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
