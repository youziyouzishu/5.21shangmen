<?php

namespace app\api\controller;

use app\admin\model\Costume;
use app\admin\model\UserCostume;
use app\api\basic\Base;
use support\Request;

class CostumeController extends Base
{

    /**
     * 获取Coser服饰照片
     * @param Request $request
     * @return \support\Response
     */
    function list(Request $request)
    {
        $coser_id = $request->post('coser_id');
        if (empty($coser_id)){
            $coser_id = $request->user_id;
        }
        $costume = Costume::orderByDesc('id')->get();
        foreach ($costume as $item) {
            $row = UserCostume::where('costume_id', $item->id)->where('user_id', $coser_id)->first();
            if ($row) {
                $item->image = $row->image;
            }
        }
        return $this->success('成功', $costume);
    }

    /**
     * 设置Coser服饰照片
     * @param Request $request
     * @return \support\Response
     */
    function set(Request $request)
    {
        $id = $request->post('id');
        $image = $request->post('image');
        $row = UserCostume::where(['user_id' => $request->user_id, 'costume_id' => $id])->first();
        if ($row) {
            $row->image = $image;
        } else {
            UserCostume::create([
                'user_id' => $request->user_id,
                'costume_id' => $id,
                'image' => $image,
            ]);
        }
        return $this->success('成功');
    }




}
