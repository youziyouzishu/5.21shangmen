<?php

namespace app\api\controller;

use app\admin\model\Sms;
use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\OpenPlatform\Application;
use plugin\admin\app\common\Util;
use support\Request;
use support\Response;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{
    /**
     * 登录
     * @param Request $request
     * @return Response
     * @throws InvalidArgumentException
     */
    function login(Request $request)
    {
        $login_type = $request->post('login_type');#登陆方式:1=手机号,2=微信
        $code = $request->post('code');
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        if ($login_type == 1) {
            if (!$mobile) {
                return $this->fail('请输入手机号');
            }
            if (!$captcha) {
                return $this->fail('请输入验证码');
            }
            $captchaResult = Sms::check($mobile, $captcha, 'login');
            if (!$captchaResult) {
                return $this->fail('验证码错误');
            }
            $user = User::where(['mobile' => $mobile])->first();
        }elseif ($login_type == 2){
            $config = config('wechat');
            $app = new Application($config);
            $oauth = $app->getOauth();
            try {
                $response = $oauth->userFromCode($code);
            }catch (\Throwable $e){
                return  $this->fail($e->getMessage());
            }
            $openid = $response->getId();
            $user = User::where(['openid' => $openid])->first();
        }else{
            return $this->fail('请选择登录方式');
        }
        if (!$user) {

            if ($login_type == 1){
                $user = User::create([
                    'username' => $mobile,
                    'nickname' => $mobile,
                    'mobile' => $mobile,
                    'avatar' => '/app/admin/avatar.png',
                    'join_time' => Carbon::now(),
                    'join_ip' => $request->getRealIp(),
                    'last_time' => Carbon::now(),
                    'last_ip' => $request->getRealIp(),
                ]);
            }else{
                $user = User::create([
                    'nickname' => '用户'.mt_rand(100000,999999),
                    'avatar' => '/app/admin/avatar.png',
                    'join_time' => Carbon::now(),
                    'join_ip' => $request->getRealIp(),
                    'last_time' => Carbon::now(),
                    'last_ip' => $request->getRealIp(),
                    'openid' => $openid ?? null,
                ]);
            }

        }else{
            $user->last_time = Carbon::now();
            $user->last_ip = $request->getRealIp();
            $user->save();
        }

        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE
        ]);
        return $this->success('登录成功', ['user' => $user, 'token' => $token]);
    }

    /**
     * 刷新令牌
     * @param Request $request
     * @return Response
     */
    function refreshToken(Request $request)
    {
        $res = JwtToken::refreshToken();
        return $this->success('刷新成功', $res);
    }




}
