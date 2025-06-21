<?php

namespace app\api\service;


use Psr\Http\Message\MessageInterface;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;
use Yansongda\Artful\Exception\InvalidResponseException;
use Yansongda\Artful\Rocket;
use Yansongda\Supports\Collection;

class Pay
{
    /**
     * 支付
     * @param $pay_type *支付类型:1=微信,2=支付宝,3=数字人民币
     * @param  $pay_amount
     * @param  $order_no
     * @param $mark
     * @param $attach
     * @return string|Rocket|Collection
     * @throws \Exception
     */
    public static function pay($pay_type, $pay_amount, $order_no, $mark, $attach)
    {
        $config = config('payment');
        if ($pay_type == 1) {
            $result = \Yansongda\Pay\Pay::wechat($config)->app([
                'out_trade_no' => $order_no,
                'description' => $mark,
                'amount' => [
                    'total' => function_exists('bcmul') ? (int)bcmul($pay_amount, 100, 2) : $pay_amount * 100,
                    'currency' => 'CNY',
                ],
                'attach' => $attach
            ]);
        } elseif ($pay_type == 2) {
            $result = \Yansongda\Pay\Pay::alipay($config)->app([
                'out_trade_no' => $order_no,
                'total_amount' => $pay_amount,
                'subject' => $mark,
                'passback_params' => urlencode($attach)
            ])->getBody()->getContents();
        } elseif ($pay_type == 3) {
            throw new \Exception('暂不支持数字人民币');
        } else {
            throw new \Exception('支付类型错误');
        }
        return $result;
    }

    #退款
    public static function refund($pay_type, $pay_amount, $order_no, $refund_order_no, $reason)
    {
        $config = config('payment');
        return match ($pay_type) {
            1 => \Yansongda\Pay\Pay::wechat($config)->refund([
                'out_trade_no' => $order_no,
                'out_refund_no' => $refund_order_no,
                'amount' => [
                    'refund' => (int)bcmul($pay_amount, 100, 2),
                    'total' => (int)bcmul($pay_amount, 100, 2),
                    'currency' => 'CNY',
                ],
                'reason' => $reason
            ]),
            default => throw new \Exception('支付类型错误'),
        };
    }

    /**
     * 转账
     * @param $pay_type *转账方式 1=微信,2=支付宝
     * @param $pay_amount
     * @param $ordersn
     * @param $mark
     * @param null $openid
     * @param null $aliaccount
     * @param null $ali_name
     * @return MessageInterface|Rocket|Collection|null
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public static function transfer($pay_type,$pay_amount, $ordersn, $mark , $openid = null , $aliaccount = null , $ali_name = null)
    {
        \Yansongda\Pay\Pay::config(config('payment'));
        if ($pay_type == 1){
            $params = [
                'transfer_scene_id' => '1000',
                'out_bill_no' => $ordersn,
                'openid' => $openid,
                'transfer_amount' => (int)bcmul($pay_amount, 100, 2),
                'transfer_remark' => $mark,
                'transfer_scene_report_infos' => [
                    [
                        'info_type' => '活动名称',
                        'info_content' => '提现'
                    ],
                    [
                        'info_type' => '奖励说明',
                        'info_content' => '提现申请'
                    ],
                ],
                'notify_url' =>'https://zhying.top/api/notify/transfer',
                '_type' => 'app'
            ];
            $allPlugins = \Yansongda\Pay\Pay::wechat()->mergeCommonPlugins([\Yansongda\Pay\Plugin\Wechat\V3\Marketing\MchTransfer\CreatePlugin::class]);

            return \Yansongda\Pay\Pay::wechat()->pay($allPlugins, $params);
        }else{
            $params = [
                'out_biz_no' => $ordersn,
                'trans_amount' => $pay_amount,
                'biz_scene' => 'DIRECT_TRANSFER',
                'product_code' => 'TRANS_ACCOUNT_NO_PWD',
                'order_title' => '提现',
                'payee_info' => [
                    'identity' => $aliaccount,
                    'identity_type' => 'ALIPAY_LOGON_ID',
                    'name' => $ali_name,
                ],
                'remark' => $mark,
                '_type' => 'app'
            ];

            $allPlugins = \Yansongda\Pay\Pay::wechat()->mergeCommonPlugins([\Yansongda\Pay\Plugin\Alipay\V2\Fund\Transfer\Fund\TransferPlugin::class]);

            return \Yansongda\Pay\Pay::alipay()->pay($allPlugins, $params);
        }

    }
}