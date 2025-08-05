<?php

namespace app\api\controller;

use app\admin\model\GoodsOrders;
use app\admin\model\Order;
use app\admin\model\User;
use app\admin\model\UsersWithdraw;
use app\admin\model\VipOrders;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Db;
use support\Log;
use support\Request;
use Webman\RedisQueue\Client;
use Yansongda\Pay\Pay;

class NotifyController extends Base
{

    protected array $noNeedLogin = ['*'];

    function alipay(Request $request)
    {
        $request->setParams('get', ['paytype' => 'alipay']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return response('success');
    }

    function wechat(Request $request)
    {
        $request->setParams('get', ['paytype' => 'wechat']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return json(['code' => 'FAIL', 'message' => $e->getMessage()]);
        }
        return json(['code' => 'SUCCESS', 'message' => '成功']);
    }

    function balance(Request $request)
    {
        $request->setParams('get', ['paytype' => 'balance']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success();
    }

    function transfer(Request $request)
    {
        $request->setParams('get', ['paytype' => 'transfer']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 接受回调
     * @throws \Throwable
     */
    private function pay(Request $request)
    {
        Log::info('支付回调', $request->all());
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $paytype = $request->input('paytype');
            $config = config('payment');
            switch ($paytype) {
                case 'wechat':
                    $pay = Pay::wechat($config);
                    $res = $pay->callback($request->post());
                    $res = $res->resource;
                    $res = $res['ciphertext'];
                    $trade_state = $res['trade_state'];
                    if ($trade_state !== 'SUCCESS') {
                        throw new \Exception('支付失败');
                    }
                    $out_trade_no = $res['out_trade_no'];
                    $attach = $res['attach'];
                    $mchid = $res['mchid'];
                    $transaction_id = $res['transaction_id'];
                    $openid = $res['payer']['openid'] ?? '';


//                    $app = new Application(config('wechat'));
//                    $api = $app->getClient();
//                    $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Asia/Shanghai'));
//                    $formatted_date = $date->format('c');
//                    $api->postJson('/wxa/sec/order/upload_shipping_info', [
//                        'order_key' => ['order_number_type' => 1, 'mchid' => $mchid, 'out_trade_no' => $out_trade_no],
//                        'logistics_type' => 3,
//                        'delivery_mode' => 1,
//                        'shipping_list' => [[
//                            'item_desc' => '发货'
//                        ]],
//                        'upload_time' => $formatted_date,
//                        'payer' => ['openid' => $openid]
//                    ]);
                    $paytype = 1;
                    break;
                case 'alipay':
                    $pay = Pay::alipay($config);
                    $res = $pay->callback($request->post());
                    $trade_status = $res->trade_status;
                    if ($trade_status !== 'TRADE_SUCCESS') {
                        throw new \Exception('支付失败');
                    }
                    $out_trade_no = $res->out_trade_no;
                    $attach = $res->passback_params;
                    $paytype = 2;
                    break;
                case 'balance':
                    $out_trade_no = $request->input('out_trade_no');
                    $attach = $request->input('attach');
                    $paytype = 4;
                    break;
                case 'transfer':
                    $pay = Pay::wechat($config);
                    $res = $pay->callback($request->post());
                    $res = $res->resource;
                    $res = $res['ciphertext'];
                    Log::info('转账回调', $res);
                    $trade_state = $res['state'];
                    if ($trade_state !== 'SUCCESS') {
                        throw new \Exception('支付失败');
                    }
                    $attach = 'transfer';
                    $out_trade_no = $res['out_bill_no'];
                    break;
                default:
                    throw new \Exception('支付类型错误');
            }

            switch ($attach) {
                case 'coser':
                    $order = Order::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 1;
                    $order->pay_time = Carbon::now();
                    $order->pay_type = $paytype;
                    $order->save();
                    Client::send('job', ['id' => $order->id, 'event' => 'order_refund'], 60 * 15);
                    break;
                case 'recharge':
                    $order = RechargeOrders::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 1;
                    $order->pay_time = date('Y-m-d H:i:s');
                    $order->save();
                    $inc_jinbi = $order->pay_amount * 100;
                    //增加用户金币
                    User::score($inc_jinbi, $order->user_id, $order->pay_type_text . '充值', 'money');
                    //给上级反佣金
                    if ($order->user->parent) {
                        User::score(round($inc_jinbi * 0.2), $order->user->parent_id, '充值金币返佣', 'money');
                    }
                    break;
                case 'transfer':
                    $order = UsersWithdraw::where(['ordersn' => $out_trade_no, 'status' => 1])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 3;
                    $order->save();
                    break;
                default:
                    throw new \Exception('回调错误');
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('支付回调失败');
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }


}
