<?php

namespace app\admin\controller;

use plugin\admin\app\model\AdminRole;
use support\Request;
use support\Response;
use app\admin\model\Admin;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 代理商管理 
 */
class AdminController extends Crud
{
    
    /**
     * @var Admin
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Admin;
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $ids = AdminRole::where('role_id', 3)->pluck('admin_id');
        $query = $this->doSelect($where, $field, $order)->whereIn('id', $ids)->with(['city']);
        if (in_array(3, admin('roles'))) {
            $query->where('id', admin_id());
        }
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('admin/index');
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
            $city_id = $request->post('city_id');
            if (!$city_id) {
                return $this->fail('请选择城市');
            }
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            $admin_role = new AdminRole;
            $admin_role->admin_id = $id;
            $admin_role->role_id = 3;
            $admin_role->save();
            return $this->json(0, 'ok', ['id' => $id]);
        }
        return view('admin/insert');
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
        return view('admin/update');
    }

}
