<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\Sms;
use app\admin\model\User;
use app\admin\model\UserCollect;
use app\admin\model\UserCoupon;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\OpenPlatform\Application;
use support\Request;
use support\Response;
use Tinywan\Jwt\Exception\JwtRefreshTokenExpiredException;
use Tinywan\Validate\Facade\Validate;

class UserController extends Base
{

    /**
     * 用户详情
     * @param Request $request
     * @return \support\Response
     */
    function getUserInfo(Request $request)
    {
        $row = User::withCount(['coupon','collect'])->find($request->user_id);
        if (!$row) {
            throw new JwtRefreshTokenExpiredException();
        }


        return $this->success('成功', $row);
    }

    /**
     * 编辑用户信息
     * @param Request $request
     * @return Response
     */
    function editUserInfo(Request $request)
    {
        $param = $request->post();
        $fields = ['nickname', 'avatar', 'sex', 'birthday', 'sign', 'photo'];
        foreach ($param as $key => $value) {
            if (!in_array($key, $fields)) {
                unset($param[$key]);
            }
        }
        $row = User::find($request->user_id);
        $row->fill($param);
        $row->save();
        return $this->success('成功');
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return Response
     */
    function bindMobile(Request $request)
    {
        $mobile = $request->post('mobile');
        $captcha = $request->post('captcha');
        if (!$mobile || !Validate::checkRule($mobile, 'mobile')) {
            return $this->fail('手机号不正确');
        }
        $smsResult = Sms::check($mobile, $captcha, 'changemobile');
        if (!$smsResult) {
            return $this->fail('验证码不正确');
        }
        $user = User::find($request->user_id);
        $user->mobile = $mobile;
        $user->username = $mobile;
        $user->save();
        return $this->success();
    }

    /**
     * 绑定微信
     * @param Request $request
     * @return Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    function bindWechat(Request $request)
    {
        $code = $request->post('code');
        $confirm = $request->post('confirm', 'no');
        $config = config('wechat');
        $app = new Application($config);
        $oauth = $app->getOauth();
        try {
            $response = $oauth->userFromCode($code);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        $user = User::where(['openid' => $response->getId()])->first();
        if ($user && $confirm == 'no') {
            return $this->fail('此微信已被其他账号占用,是否解除绑定');
        }
        $user = User::find($request->user_id);
        $user->openid = $response->getId();
        $user->save();
        return $this->success('绑定成功');
    }


//    /**
//     * 获取相册
//     * @param Request $request
//     * @return Response
//     */
//    function getPhotos(Request $request)
//    {
//        $rows = UserPhoto::where('user_id', $request->user_id)->get();
//        return $this->success('成功', $rows);
//    }
//
//    /**
//     * 添加相册
//     * @param Request $request
//     * @return Response
//     */
//    function addPhoto(Request $request)
//    {
//        $photo = $request->post('photo');
//        UserPhoto::create([
//            'user_id' => $request->user_id,
//            'photo' => $photo,
//        ]);
//        return $this->success('成功');
//    }
//
//    /**
//     * 删除相册
//     * @param Request $request
//     * @return Response
//     */
//    function deletePhoto(Request $request)
//    {
//        $id = $request->post('id');
//        UserPhoto::find($id)->delete();
//        return $this->success('成功');
//    }

    /**
     * 收藏列表
     * @param Request $request
     * @return Response
     */
    function collect(Request $request)
    {
        $lat = $request->post('lat');
        $lng = $request->post('lng');
        $rows = UserCollect::where('user_id', $request->user_id)
            ->with(['coser'])
            ->orderByDesc('id')
            ->paginate()
            ->getCollection();
        foreach ($rows as $row) {
            $distance = Area::getDistanceFromLngLat($lat, $lng, $row->coser->lat, $row->coser->lng);
            $firstTime = $row->coser->times()->where('time', '>=',  Carbon::now())->whereIn('status', ['available', 'booked'])->orderBy('id')->first();
            $row->coser->current_status = $firstTime ? $firstTime->status : null;
            $row->coser->first_time = $firstTime ? $firstTime->time : null;
            $row->coser->first_time = $firstTime?->time->format('H:i');
            $row->coser->distance = $distance;
        }

        return $this->success('成功', $rows);
    }

    /**
     * 获取优惠券列表
     * @param Request $request
     * @return Response
     */
    function coupon(Request $request)
    {
        $status = $request->post('status');#1：可使用 2：已使用 3：已过期
        $rows = UserCoupon::where('user_id', $request->user_id)->where(function ($query) use ($status) {
            if ($status == 1) {
                $query->where('expire_time', '>', Carbon::now());
            }
            if ($status == 2) {
                $query->onlyTrashed();
            }
            if ($status == 3) {
                $query->where('expire_time', '<', Carbon::now());
            }
        })->get();
        return $this->success('成功', $rows);
    }

}
