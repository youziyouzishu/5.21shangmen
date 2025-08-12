<?php

namespace app\admin\model;


use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;
use support\Db;

/**
 * @property int $id 主键
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $sex 性别
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property string|null $mobile 手机
 * @property int $level 等级
 * @property string $money 余额(元)
 * @property int $score 积分
 * @property \Illuminate\Support\Carbon|null $last_time 登录时间
 * @property string|null $last_ip 登录ip
 * @property \Illuminate\Support\Carbon|null $join_time 注册时间
 * @property string|null $join_ip 注册ip
 * @property string|null $token token
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $role 角色
 * @property int $status 禁用
 * @property string|null $invitecode 邀请码
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserTime> $times
 * @property int|null $city_id 城市
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserCollect> $collect
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserCollect> $fans
 * @property int $sales 销量
 * @property string|null $lat 纬度
 * @property string|null $lng 经度
 * @property string|null $sign 个性签名
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserCostume> $costume
 * @property int $fare 出行费:0=免费,1=有出行费
 * @property string|null $openid 微信标识
 * @property float $grade 评分
 * @property float $rate 结算率
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\CoserReport> $report
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserCoupon> $coupon
 * @property string|null $photo 相册
 * @property-read \app\admin\model\Area|null $city
 * @property \Illuminate\Support\Carbon|null $birthday 生日
 * @property string|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_time' => 'datetime',
        'join_time' => 'datetime',
        'birthday' => 'date'
    ];

    protected $fillable = [
        'username',
        'nickname',
        'password',
        'sex',
        'avatar',
        'email',
        'mobile',
        'level',
        'birthday',
        'money',
        'score',
        'last_time',
        'last_ip',
        'join_time',
        'join_ip',
        'token',
        'role',
        'status',
        'openid',
        'sign',
        'photo'
    ];

    public static function generateInvitecode()
    {
        do {
            $invitecode = mt_rand(100000, 999999);
        } while (self::where(['invitecode' => $invitecode])->exists());
        return $invitecode;
    }

    function times()
    {
        return $this->hasMany(UserTime::class, 'user_id', 'id');
    }

    function collect()
    {
        return $this->hasMany(UserCollect::class, 'user_id', 'id');
    }

    function fans()
    {
        return $this->hasMany(UserCollect::class, 'coser_id', 'id');
    }

    function costume()
    {
        return $this->hasMany(UserCostume::class, 'user_id', 'id');
    }

    public static function changeMoney($money, $user_id, $memo)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $user = self::lockForUpdate()->find($user_id);
            if ($user && $money != 0) {
                $before = $user->money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->money = $after;
                $user->save();
                //写入日志
                UserMoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollback();
            throw $e;
        }
    }

    function report()
    {
        return $this->hasMany(CoserReport::class, 'coser_id', 'id');
    }

    function orders()
    {
        return $this->hasMany(Order::class, 'coser_id', 'id');
    }

    function coupon()
    {
        return $this->hasMany(UserCoupon::class, 'user_id', 'id');
    }

    function city()
    {
        return $this->belongsTo(Area::class, 'city_id', 'id');
    }




}
