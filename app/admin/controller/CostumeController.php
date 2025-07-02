<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\Costume;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * Coser服饰 
 */
class CostumeController extends Crud
{
    
    /**
     * @var Costume
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Costume;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('costume/index');
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
        return view('costume/insert');
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
        return view('costume/update');
    }

}
