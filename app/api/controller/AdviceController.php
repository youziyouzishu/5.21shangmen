<?php

namespace app\api\controller;

use app\admin\model\Advice;
use app\admin\model\AdviceClass;
use app\api\basic\Base;
use support\Request;

class AdviceController extends Base
{
    /**
     * 获取意见反馈分类列表
     * @param Request $request
     * @return \support\Response
     */
    function getAdviceClassList(Request $request)
    {
        $rows = AdviceClass::all();
        return $this->success('获取成功', $rows);
    }

    /**
     * 提交意见反馈
     * @param Request $request
     * @return \support\Response
     */
    function postAdvice(Request $request)
    {
        $class_name = $request->post('class_name');
        $content = $request->post('content');
        $images = $request->post('images');
        $mobile = $request->post('mobile');
        $truename = $request->post('truename');
        $advice = Advice::create([
            'user_id' => $request->user_id,
            'class_name' => $class_name,
            'content' => $content,
            'images' => $images,
            'mobile' => $mobile,
            'truename' => $truename,
        ]);
        return $this->success('成功', $advice);
    }
}
