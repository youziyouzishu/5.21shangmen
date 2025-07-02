<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\UserCoupon;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 优惠券管理 
 */
class UserCouponController extends Crud
{
    
    /**
     * @var UserCoupon
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new UserCoupon;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('user-coupon/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return view('user-coupon/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('user-coupon/update');
    }

}
