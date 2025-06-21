<?php

namespace app\api\controller;

use app\admin\model\Project;
use app\api\basic\Base;
use support\Request;

class ProjectController extends Base
{

    /**
     * 获取项目列表
     * @param Request $request
     */
    function select(Request $request)
    {
        $rows = Project::all();
        return $this->success('成功', $rows);
    }

    /**
     * 获取项目详情
     * @param Request $request
     */
    function detail(Request $request)
    {
        $id = $request->post('id');
        $row = Project::find($id);
        if (!$row) {
            return $this->fail('项目不存在');
        }
        return $this->success('成功', $row);
    }



}
