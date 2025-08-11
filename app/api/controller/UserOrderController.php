<?php

namespace app\api\controller;

use app\admin\model\Admin;
use app\admin\model\Area;
use app\admin\model\Costume;
use app\admin\model\Order;
use app\admin\model\OrderComment;
use app\admin\model\OrderItem;
use app\admin\model\Project;
use app\admin\model\User;
use app\admin\model\UserAddress;
use app\admin\model\UserCostume;
use app\admin\model\UserCoupon;
use app\admin\model\UserTime;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Db;
use support\Log;
use support\Request;
use support\Response;
use Webman\RedisQueue\Client;

class UserOrderController extends Base
{


    /**
     * 预创建订单
     * @param Request $request
     * @return Response
     */
    function preCreate(Request $request)
    {
        $project_list = $request->post('project_list');//[{"project_id":"xxx","num":"xxx"},{'project_id':'xxx','num':'xxx'},]
        $coser_id = $request->post('coser_id');
        $costume_id = $request->post('costume_id');
        $address_id = $request->post('address_id');
        $time_id = $request->post('time_id');
        $coupon_id = $request->post('coupon_id');
        $project_amount = 0;#项目总金额
        $coupon_amount = 0;#优惠金额
        $total_minute = 0;#总时长
        $fare_amount = 0;#车费
        $total_distance = 0;#总距离
        $costume = null;

        $coser = User::find($coser_id);

        if (!empty($costume_id)) {
            $costume = Costume::find($costume_id);
            if ($costume) {
                $userCostume = UserCostume::where('user_id', $coser->id)
                    ->where('costume_id', $costume_id)
                    ->first();

                if ($userCostume) {
                    $costume->image = $userCostume->image;
                }
            }
        }

        if (!empty($address_id) && ($address = UserAddress::find($address_id)) && $coser->fare == 1) {
            $distance = Area::getDistanceFromLngLat($address->lat, $address->lng, $coser->lat, $coser->lng);
            $distance = ceil($distance);
            $total_distance = $distance * 2;
            $fare_amount = $total_distance * 6;
        }
        if ($coser->fare == 1){
            $fare_text = '滴滴/出租';
            $distance_text = '全程共'.$total_distance .'公里，出行收取往返路费，每公里6元。';
        }else{
            $fare_text = '免费';
            $distance_text = '本次行程免费';
        }




        foreach ($project_list as &$item) {
            $project = Project::find($item['project_id']);
            $project_amount += $project->price * $item['num'];
            $item['image'] = $project->image;
            $item['name'] = $project->name;
            $item['price'] = $project->price;
            $item['tag'] = $project->tag;
            $item['minutes'] = $project->minutes;
            $total_minute += $project->minutes * $item['num'];
        }
        $total_amount = $project_amount + $fare_amount;


        if (!empty($coupon_id) && ($cancoupon = UserCoupon::where('user_id', $request->user_id)
                ->where('with_amount', '<=', $total_amount)
                ->where('expire_time', '>', Carbon::now())
                ->where('id', $coupon_id)
                ->first())) {
            $total_amount = $total_amount - $cancoupon->amount;
            $coupon_amount = $cancoupon->amount;
        }

        $available_coupon = UserCoupon::where('user_id', $request->user_id)
            ->where('with_amount', '<=', $total_amount)
            ->where('expire_time', '>', Carbon::now())
            ->get();
        $unavailable_coupon = UserCoupon::where('user_id', $request->user_id)
            ->where(function ($query) use ($total_amount) {
                $query->where('with_amount', '>', $total_amount)
                    ->orWhere('expire_time', '<', Carbon::now());
            })
            ->get();
        return $this->success('成功', [
            'project_amount' => $project_amount,
            'pay_amount' => $total_amount,
            'fare_amount' => $fare_amount,
            'coupon_amount' => $coupon_amount,
            'project_list' => $project_list,
            'costume' => $costume,
            'coser' => $coser,
            'available_coupon' => $available_coupon,
            'unavailable_coupon' =>  $unavailable_coupon,
            'total_minute' => $total_minute,
            'fare_text' => $fare_text,
            'distance_text' => $distance_text,
            'total_distance' => $total_distance,
        ]);
    }

    /**
     * 创建订单
     * @param Request $request
     * @return Response
     */
    function create(Request $request)
    {
        $project_list = $request->post('project_list');//[{"project_id":"xxx","num":"xxx"},{'project_id':'xxx','num':'xxx'},]
        $coser_id = $request->post('coser_id');
        $costume_id = $request->post('costume_id');
        $address_id = $request->post('address_id');
        $time_id = $request->post('time_id');
        $coupon_id = $request->post('coupon_id');
        $mark = $request->post('mark');
        $project_amount = 0;#项目总金额
        $total_minute = 0;#总时长
        $coupon_amount = 0;#优惠金额
        $address = UserAddress::find($address_id);
        if (!$address) {
            return $this->fail('请选择地址');
        }
        $coser = User::find($coser_id);
        if (!$coser) {
            return $this->fail('请选择Coser');
        }

        $costume = Costume::find($costume_id);
        if (!$costume) {
            return $this->fail('请选择服饰');
        }
        if ($userCostme = UserCostume::where('user_id', $coser->id)->where('costume_id', $costume->id)->first()) {
            $costume->image = $userCostme->image;
        }


        $items = [];
        foreach ($project_list as &$item) {
            $project = Project::find($item['project_id']);
            $project_amount += $project->price * $item['num'];
            $item['image'] = $project->image;
            $item['name'] = $project->name;
            $item['price'] = $project->price;
            $item['tag'] = $project->tag;
            $item['minutes'] = $project->minutes;
            $total_minute += $project->minutes * $item['num'];

            $items[] = [
                'project_id' => $item['project_id'],
                'num' => $item['num'],
                'project_ext' => $project
            ];
        }
        $pay_amount = $total_amount = $project_amount;

        $need_times = $total_minute / 30;

        $time_list = UserTime::where('user_id', $coser->id)
            ->gtNow()
            ->where('id', '>=', $time_id)
            ->orderBy('id')
            ->take($need_times)
            ->get();
        foreach ($time_list as $time) {
            if ($time->status != 'available') {
                return $this->fail('时间段不足');
            }
        }

        $cancoupon = null;
        if (!empty($coupon_id)) {
            $cancoupon = UserCoupon::where('user_id', $request->user_id)
                ->where('with_amount', '<=', $pay_amount)
                ->where('expire_time', '>', Carbon::now())
                ->where('id', $coupon_id)
                ->first();
            if (empty($cancoupon)) {
                return $this->fail('优惠券不存在');
            }
            $pay_amount = $pay_amount - $cancoupon->amount;
            $coupon_amount = $cancoupon->amount;
        }

        $coser_get_amount = bcmul($pay_amount, $coser->rate, 2);
        $agent_get_amount = 0;
        $agent = Admin::where('city_id', $coser->city_id)->first();
        if ($agent) {
            $agent_get_amount = bcmul($pay_amount, $agent->rate, 2);
        }
        $admin_get_amount = $pay_amount - $coser_get_amount - $agent_get_amount;


        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $ordersn = Order::generateOrderSn();
            $order = Order::create([
                'admin_id' => $agent ? $agent->id : null,
                'user_id' => $request->user_id,
                'coser_id' => $coser_id,
                'costume_id' => $costume_id,
                'address_id' => $address_id,
                'time_id' => $time_id,
                'coupon_id' => $coupon_id ?: null,
                'ordersn' => $ordersn,
                'project_amount' => $project_amount,
                'pay_amount' => $pay_amount,
                'costume_ext' => $costume,
                'total_minute' => $total_minute,
                'mark' => $mark,
                'coupon_amount' => $coupon_amount,
                'total_amount' => $total_amount,
                'agent_get_amount' => $agent_get_amount,
                'coser_get_amount' => $coser_get_amount,
                'admin_get_amount' => $admin_get_amount,
            ]);
            $order->items()->createMany($items);
            if ($cancoupon) {
                $cancoupon->delete();
            }

            $time_list->each(function (UserTime $time) use ($order) {
                $time->status = 'booked';
                $time->order_id = $order->id;
                $time->save();
            });

            Client::send('job', ['id' => $order->id, 'event' => 'order_cancel'], 60 * 15);
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('创建订单失败');
            Log::error($e->getMessage());
            return $this->fail('创建订单失败');
        }
        return $this->success('成功', $order);
    }

    /**
     * 支付
     * @param Request $request
     * @return Response
     */
    function pay(Request $request)
    {
        $pay_type = $request->post('pay_type');#支付方式:1=微信,2=支付宝
        $ordersn = $request->post('ordersn');
        $order = Order::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('重复支付');
        }
        try {
            $ret = Pay::pay($pay_type, $order->pay_amount, $order->ordersn, '订单支付', 'coser');
        } catch (\Throwable $e) {
            Log::error('订单支付失败');
            Log::error($e->getMessage());
            return $this->fail('订单支付失败');
        }
        return $this->success('成功', $ret);
    }

    /**
     * 订单列表
     * @param Request $request
     * @return Response
     */
    function list(Request $request)
    {
        $status = $request->post('status');# 订单状态:0=全部,1=待付款,2=进行中,3=待评价,4=完成
        $rows = Order::where('user_id', $request->user_id)
            ->with(['items', 'time', 'coser'])
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('status', 0);
                }
                if ($status == 2) {
                    $query->whereIn('status', [1, 4, 5, 8, 9]);
                }
                if ($status == 3) {
                    $query->where('status', 6);
                }
                if ($status == 4) {
                    $query->where('status', 7);
                }
            })
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    /**
     * 订单详情
     * @param Request $request
     * @return Response
     */
    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = Order::with(['address', 'items', 'coser', 'time'])->find($id);
        return $this->success('成功', $row);
    }

    /**
     * 退款
     * @param Request $request
     * @return Response
     */
//    function refund(Request $request)
//    {
//        $id = $request->post('id');
//        Client::send('job', ['id' => $id, 'event' => 'order_refund']);
//        return $this->success('成功');
//    }

    /**
     * 取消订单
     * @param Request $request
     * @return Response
     */
    function cancel(Request $request)
    {
        $id = $request->post('id');
        Client::send('job', ['id' => $id, 'event' => 'order_expire']);
        return $this->success('成功');
    }

    /**
     * 评价
     * @param Request $request
     * @return Response
     */
    function comment(Request $request)
    {
        $id = $request->post('id');
        $grade = $request->post('grade');
        $tags = $request->post('tags');
        $cryptonym = $request->post('cryptonym');
        $order = Order::find($id);
        if (!$order || $order->status != 6) {
            return $this->fail('订单不存在或未完成');
        }
        $order->status = 7;
        $order->finish_time = Carbon::now();
        $order->save();
        $order->comment->create([
            'grade' => $grade,
            'tags' => $tags,
            'cryptonym' => $cryptonym,
            'order_id' => $id,
            'user_id' => $request->user_id,
        ]);
        $order->coser->sales += 1;
        $order->coser->grade = OrderComment::where('coser_id', $order->coser_id)->avg('grade');
        $order->coser->save();
        $order->items->each(function (OrderItem $item) {
            $item->project->sales += $item->num;
            $item->project->save();
        });

        return $this->success('成功');
    }


}
