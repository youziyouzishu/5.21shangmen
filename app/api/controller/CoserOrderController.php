<?php

namespace app\api\controller;

use app\admin\model\Order;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;
use support\Response;

class CoserOrderController extends Base
{

    /**
     * 订单列表
     * @param Request $request
     * @return \support\Response
     */
    function list(Request $request)
    {
        $status = $request->post('status');#状态:0=全部,1=待接单,2=已接单,3=进行中,4=已完成
        $rows = Order::where('coser_id', $request->user_id)
            ->with(['items', 'costume', 'time', 'address'])
            ->where(function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('status', 1);
                }
                if ($status == 2) {
                    $query->where('status', 4);
                }
                if ($status == 3) {
                    $query->where('status', [5, 8, 9]);
                }
                if ($status == 4) {
                    $query->whereIn('status', [6, 7]);
                }
            })
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }


    /**
     * 接单
     * @param Request $request
     * @return Response
     */
    function take(Request $request)
    {
        $id = $request->post('id');
        $order = Order::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 1) {
            return $this->fail('订单状态错误');
        }
        $order->status = 4;#变为待出发
        $order->take_time = Carbon::now();
        $order->save();
        return $this->success('成功');
    }

    /**
     * 出发
     * @param Request $request
     * @return Response
     */
    function depart(Request $request)
    {
        $id = $request->post('id');
        $order = Order::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 4) {
            return $this->fail('订单状态错误');
        }
        $order->status = 8;#变为在路上
        $order->depart_time = Carbon::now();
        $order->save();
        return $this->success('成功');
    }

    /**
     * 到达
     * @param Request $request
     * @return Response
     */
    function arrive(Request $request)
    {
        $id = $request->post('id');
        $arrive_image = $request->post('arrive_image');
        $arrive_mark = $request->post('arrive_mark');
        if (empty($arrive_image)) {
            return $this->fail('必须上传照片');
        }
        $order = Order::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 8) {
            return $this->fail('订单状态错误');
        }
        $order->status = 9;#变为待服务
        $order->arrive_time = Carbon::now();
        $order->arrive_image = $arrive_image;
        $order->arrive_mark = $arrive_mark;
        $order->save();
        return $this->success('成功');
    }

    /**
     * 开始服务
     * @param Request $request
     */
    function startService(Request $request)
    {
        $id = $request->post('id');
        $order = Order::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 9) {
            return $this->fail('订单状态错误');
        }
        $start_service_time = Carbon::now();
        $order->status = 5;#变为服务中
        $order->start_service_time =$start_service_time;
        $order->should_end_service_time = $start_service_time->addMinutes($order->total_minute);
        $order->save();
        return $this->success('成功');
    }

    /**
     * 结束服务
     * @param Request $request
     */
    function endService(Request $request)
    {
        $id = $request->post('id');
        $order = Order::find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 5) {
            return $this->fail('订单状态错误');
        }
        $order->status = 6;#变为待评价
        $order->end_service_time = Carbon::now();
        $order->save();
        return $this->success('成功');
    }

    /**
     * 详情
     * @param Request $request
     */
    function detail(Request $request)
    {
        $id = $request->post('id');
        $order = Order::with(['address', 'items', 'time', 'costume'])->find($id);
        if (!$order) {
            return $this->fail('订单不存在');
        }

        return $this->success('成功', $order);
    }


}
