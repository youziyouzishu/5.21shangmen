<?php

namespace app\api\controller;

use app\admin\model\Order;
use app\admin\model\User;
use app\admin\model\UserMoneyLog;
use app\admin\model\UserWithdraw;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Db;
use support\Request;
use support\Response;

class WalletController extends Base
{

    /**
     * 余额
     * @param Request $request
     * @return Response
     */
    function balance(Request $request)
    {
        $user = User::find($request->user_id);
        $balance = $user->money;
        $wait_balance = $user->orders()->where('status',6)->sum('coser_get_amount');
        $wait_list = $user->orders()->where('status',6)->get();
        return $this->success('成功',[
            'balance' => $balance,
            'wait_balance' => $wait_balance,
            'wait_list' => $wait_list,
        ]);
    }


    /**
     * 获取账变记录
     * @param Request $request
     * @return Response
     */
    function getMoneyLog(Request $request)
    {
        $date = $request->post('date');
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $rows = UserMoneyLog::where('user_id', $request->user_id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->paginate()
            ->getCollection()
            ->each(function ($item) {
                if ($item->money > 0) {
                    $item->money = '+' . $item->money;
                }
            });
        return $this->success('获取成功', $rows);
    }

    /**
     * 提现
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    function withdraw(Request $request)
    {
        $amount = $request->post('amount');
        $pay_type = $request->post('pay_type');#1 微信 2 支付宝
        $ali_account = $request->post('ali_account');
        $ali_name = $request->post('ali_name');
        $user = User::find($request->user_id);
        if ($pay_type == 1 && empty($user->openid)){
            return $this->fail('请先绑定微信');
        }
        if ($pay_type == 2 && (empty($ali_account) || empty($ali_name))){
            return $this->fail('请填写支付宝账号和姓名');
        }
        if ($user->money < $amount) {
            return $this->fail('余额不足');
        }

        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $ordersn = Order::generateOrderSn();
            UserWithdraw::create([
                'user_id' => $request->user_id,
                'amount' => $amount,
                'pay_type' => $pay_type,
                'status' => $pay_type == 1 ? 1 : 2,
                'ordersn' => $ordersn,
                'ali_account' => $ali_account,
                'ali_name' => $ali_name,
                'openid' => $user->openid,
            ]);
            User::changeMoney(-$amount,$user->id,'提现：'.$ordersn);
            Pay::transfer($pay_type,$amount,$user->id,'提现：'.$ordersn,$user->openid,$ali_account,$ali_name);
            Db::connection('plugin.admin.mysql')->commit();
        }catch (\Throwable $e){
            Db::connection('plugin.admin.mysql')->rollBack();
            return $this->fail('提现失败');
        }
        if ($pay_type == 1){
            return $this->success('请到提现记录手动领取');
        }else{
            return $this->success('提现成功');
        }
    }

    /**
     * 提现列表
     * @param Request $request
     * @return Response
     */
    function getWithdrawLog(Request $request)
    {
        $rows = UserWithdraw::where('user_id',$request->user_id)->orderByDesc('id')->paginate()->items();
        return $this->success('成功',$rows);
    }

}
