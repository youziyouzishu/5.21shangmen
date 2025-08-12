<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use support\Request;
use support\Response;
use app\admin\model\AdminWithdraw;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 代理商提现 
 */
class AdminWithdrawController extends Crud
{
    
    /**
     * @var AdminWithdraw
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new AdminWithdraw;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('admin-withdraw/index');
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
            if (!in_array(3, admin('roles'))) {
                return $this->fail('无权限');
            }
            $amount = $request->post('amount');
            $name = $request->post('name');
            $bank_name = $request->post('bank_name');
            $bank_account = $request->post('bank_account');
            $admin_id = admin_id();
            $admin = Admin::find($admin_id);
            if ($admin->money < $amount) {
                return $this->fail('余额不足');
            }
            Admin::changeMoney(-$amount, $admin_id, '提现');
            $request->setParams('post',[
                'admin_id' => $admin_id,
            ]);
            return parent::insert($request);
        }
        return view('admin-withdraw/insert');
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
            $id = $request->post('id');
            $status = $request->post('status');
            $row = $this->model->find($id);
            if ($row->status == 0 && $status == 1) {
                //打款成功
            }
            if ($row->status == 0 && $status == 2) {
                //打款失败
                Admin::changeMoney($row->amount, $row->admin_id, '提现失败退回');
            }
            return parent::update($request);
        }
        return view('admin-withdraw/update');
    }

}
