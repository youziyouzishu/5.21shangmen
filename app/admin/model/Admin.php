<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;
use support\Db;


/**
 * @property int $id ID
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property string|null $mobile 手机
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string|null $login_at 登录时间
 * @property int|null $status 禁用
 * @property string $rate 结算率
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @property int|null $city_id 城市
 * @property-read \app\admin\model\Area|null $city
 * @mixin \Eloquent
 */
class Admin extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_admins';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'password',
        'nickname',
        'avatar',
        'email',
        'mobile',
        'status',
        'last_login_ip',
        'last_login_time',
        'deleted_at',
        'created_at',
        'updated_at',
        'rate',
        'city_id'
    ];

    public static function changeMoney($money, $admin_id, $memo)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $user = self::lockForUpdate()->find($admin_id);
            if ($user && $money != 0) {
                $before = $user->money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->money = $after;
                $user->save();
                //写入日志
                AdminMoneyLog::create(['admin_id' => $admin_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollback();
            throw $e;
        }
    }

    function city()
    {
        return $this->belongsTo(Area::class, 'city_id', 'id');
    }




}
