<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $name 姓名
 * @property string $birthday 出生日期
 * @property int $age 年龄
 * @property int $sex 性别:女=0,1=男
 * @property string $card_front 身份证正面
 * @property string $card_back 身份证反面
 * @property string $card_handheld 手持身份证
 * @property int $status 状态:0=待审核,1=成功,2=驳回
 * @property string|null $reason 驳回原因
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coser query()
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class Coser extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_coser';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'name',
        'birthday',
        'age',
        'sex',
        'card_front',
        'card_back',
        'card_handheld',
        'status',
        'reason',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
