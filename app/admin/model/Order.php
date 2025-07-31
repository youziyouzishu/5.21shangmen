<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $coser_id Coser
 * @property int $costume_id 服饰
 * @property int $address_id 地址
 * @property int $time_id 预约时间
 * @property int|null $coupon_id 优惠券
 * @property string $project_amount 商品价格
 * @property string $pay_amount 支付价格
 * @property string $fare_amount 车费
 * @property int $total_minute 总时长
 * @property int $pay_type 支付方式:0=无,1=微信,2=支付宝
 * @property int $status 状态:0=待支付,1=待接单,2=取消,3=接单超时,4=待出发,5=服务中,6=待评价,7=已完成,8=在路上,9=待服务
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @property-read \app\admin\model\UserAddress|null $address
 * @property-read \app\admin\model\User|null $coser
 * @property-read \app\admin\model\Costume|null $costume
 * @property-read \app\admin\model\UserCoupon|null $coupon
 * @property-read \app\admin\model\UserTime|null $time
 * @property-read \app\admin\model\User|null $user
 * @property object|null $costume_ext 服装信息
 * @property string $ordersn 订单编号
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\OrderItem> $items
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @property \Illuminate\Support\Carbon|null $cancel_time 取消时间
 * @property string|null $mark 备注
 * @property-read mixed $status_text
 * @property string $coupon_amount 优惠券金额
 * @property-read mixed $pay_type_text
 * @property string $total_amount 订单总金额
 * @property-read \app\admin\model\OrderComment|null $comment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserTime> $times
 * @property \Illuminate\Support\Carbon|null $finish_time 完成时间
 * @property string $coser_get_amount Coser结算金额
 * @property string $agent_get_amount 代理结算金额
 * @property string $admin_get_amount 平台结算金额
 * @property int|null $admin_id 所属代理
 * @property string $user_lat 用户纬度
 * @property string $user_lng 用户经度
 * @property string $coser_lat Coser纬度
 * @property string $coser_lng Coser经度
 * @property float $distance 距离
 * @property \Illuminate\Support\Carbon|null $take_time 接单时间
 * @property \Illuminate\Support\Carbon|null $depart_time 出发时间
 * @property \Illuminate\Support\Carbon|null $arrive_time 到达时间
 * @property \Illuminate\Support\Carbon|null $start_service_time 开始服务时间
 * @property \Illuminate\Support\Carbon|null $end_service_time 结束服务时间
 * @property string|null $arrive_image 到达拍照
 * @property string|null $arrive_mark 到达备注
 * @property \Illuminate\Support\Carbon|null $should_end_service_time 应该结束时间
 * @mixin \Eloquent
 */
class Order extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_orders';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'coser_id',
        'costume_id',
        'address_id',
        'time_id',
        'coupon_id',
        'project_amount',
        'pay_amount',
        'fare_amount',
        'costume_ext',
        'total_minute',
        'pay_type',
        'status',
        'created_at',
        'updated_at',
        'ordersn',
        'pay_time',
        'coupon_amount',
        'total_amount',
        'finish_time',
        'cancel_time',
        'coser_get_amount',
        'agent_get_amount',
        'admin_get_amount',
        'admin_id',
        'user_lat',
        'user_lng',
        'coser_lat',
        'coser_lng',
        'distance',
        'take_time'
    ];

    protected $appends = [
        'status_text',
        'pay_type_text',
        'status_color'
    ];

    protected $casts = [
        'costume_ext' => 'object',
        'pay_time' => 'datetime',
        'cancel_time' => 'datetime',
        'finish_time' => 'datetime',
        'take_time' => 'datetime',
        'depart_time' => 'datetime',
        'arrive_time' => 'datetime',
        'start_service_time' => 'datetime',
        'end_service_time' => 'datetime',
        'should_end_service_time' => 'datetime',
    ];

    public static function generateOrderSn()
    {
        return date('Ymd') . mb_strtoupper(uniqid());
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function coser()
    {
        return $this->belongsTo(User::class, 'coser_id', 'id');
    }

    function costume()
    {
        return $this->belongsTo(Costume::class, 'costume_id', 'id');
    }

    function address()
    {
        return $this->belongsTo(UserAddress::class, 'address_id', 'id');
    }

    function time()
    {
        return $this->belongsTo(UserTime::class, 'time_id', 'id');
    }

    function coupon()
    {
        return $this->belongsTo(UserCoupon::class, 'coupon_id', 'id');
    }

    function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /**
     * 退款
     */
    function refund()
    {
        $config = config('payment');
        return match ($this->pay_type) {
            1 => \Yansongda\Pay\Pay::wechat($config)->refund([
                'out_trade_no' => $this->ordersn,
                'out_refund_no' => self::generateOrderSn(),
                'amount' => [
                    'refund' => (int)bcmul($this->pay_amount, 100, 2),
                    'total' => (int)bcmul($this->pay_amount, 100, 2),
                    'currency' => 'CNY',
                ],
                'reason' => '订单超时',
                '_action' => 'app', // app 退款
            ]),
            2 => \Yansongda\Pay\Pay::alipay($config)->refund([
                'out_trade_no' => $this->ordersn,
                'refund_amount' => $this->pay_amount,
                'refund_reason' => '订单超时',
                '_action' => 'app', // 退款 APP 订单
            ]),
        };
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = $this->getStatusList();
        return $list[$value]??'';
    }

    //状态:0=待支付,1=待接单,2=取消,3=接单超时,4=待服务,5=服务中,6=待评价,7=已完成
    function getStatusList()
    {
        return [
            0 => '待支付',
            1 => '待接单',
            2 => '订单取消',
            3 => '接单超时',
            4 => '待出发',
            5 => '服务中',
            6 => '待评价',
            7 => '已完成',
            8 => '在路上',
            9 => '待服务',
            10 => '服务超时'
        ];
    }

    // 获取状态颜色
    function getStatusColorAttribute($value)
    {
        $value = $value !== null ? $value : $this->status;
        $list = $this->getStatusColorList();
        return $list[$value] ?? '#999'; // 默认灰色
    }

    // 状态颜色列表（可以用十六进制、颜色名，或 Tailwind/Bulma 等类名）
    function getStatusColorList()
    {
        return [
            0 => '#f39c12', // 待支付：橙色
            1 => '#3498db', // 待接单：蓝色
            2 => '#e74c3c', // 取消：红色
            3 => '#c0392b', // 接单超时：深红
            4 => '#8e44ad', // 待出发：紫色
            5 => '#1abc9c', // 服务中：青色
            6 => '#f1c40f', // 待评价：金色
            7 => '#2ecc71', // 已完成：绿色
            8 => '#2980b9', // 在路上：深蓝
            9 => '#d35400', // 待服务：橙红
            10 => '#e67e22',// 服务超时：橙色
        ];
    }

    function getPayTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->pay_type;
        $list = $this->getPayTypeList();
        return $list[$value]??'';
    }

    function getPayTypeList()
    {
        return [
            0 => '无',
            1 => '微信',
            2 => '支付宝',
        ];
    }

    function comment()
    {
        return $this->hasOne(OrderComment::class, 'order_id', 'id');
    }

    function times()
    {
        return $this->hasMany(UserTime::class, 'order_id', 'id');
    }








}
