<?php

namespace app\queue\redis;

use app\admin\model\Order;
use app\admin\model\UserTime;
use Carbon\Carbon;
use Webman\RedisQueue\Consumer;

class Job implements Consumer
{
    // 要消费的队列名
    public $queue = 'job';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($data)
    {
        $event = $data['event'];
        if ($event == 'order_cancel') {
            $id = $data['id'];
            $order = Order::find($id);
            if ($order && $order->status == 0) {
                //如果还没支付 取消订单
                $order->status = 2;
                $order->cancel_time = Carbon::now();
                $order->save();
                #退优惠券
                if ($order->coupon) {
                    $order->coupon->withTrashed()->restore();
                }
                #恢复Coser时间段
                if ($order->times->isNotEmpty()) {
                    $order->times->each(function (UserTime $time) {
                        $time->order_id = null;
                        $time->status = 'available';
                        $time->save();
                    });
                }

            }
        }
        if ($event == 'order_refund') {
            $id = $data['id'];
            $order = Order::find($id);
            if ($order && $order->status == 1) {
                //如果还接单 退款
                $order->status = 3;
                $order->cancel_time = Carbon::now();
                $order->save();
                //退款
                $order->refund();
                #退优惠券
                if ($order->coupon) {
                    $order->coupon->withTrashed()->restore();
                }
                #恢复Coser时间段
                if ($order->times->isNotEmpty()) {
                    $order->times->each(function (UserTime $time) {
                        $time->order_id = null;
                        $time->status = 'available';
                        $time->save();
                    });
                }
            }
        }

        if ($event == 'order_expire') {
            $id = $data['id'];
            $order = Order::find($id);
            if ($order && !in_array($order->status, [6, 7])) {
                //如果还未完成 退款
                $order->status = 10;
                $order->cancel_time = Carbon::now();
                $order->save();
                //退款
                $order->refund();
                #退优惠券
                if ($order->coupon) {
                    $order->coupon->withTrashed()->restore();
                }
                #恢复Coser时间段
                if ($order->times->isNotEmpty()) {
                    $order->times->each(function (UserTime $time) {
                        $time->order_id = null;
                        $time->status = 'available';
                        $time->save();
                    });
                }
            }
        }
    }

}
