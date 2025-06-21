<?php

namespace app\api\controller;

use app\admin\model\UserTime;
use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;

class UserTimeController extends Base
{

    /**
     * 获取Coser时间列表
     * @param Request $request
     * @return \support\Response
     */
    function times(Request $request)
    {
        $coser_id = $request->post('coser_id');
        $date = $request->post('date');
        if (empty($coser_id)){
            $coser_id = $request->user_id;
        }
        $date = Carbon::parse($date);
        $now = Carbon::now();
        $rows = UserTime::whereDate('time',$date)
            ->where('user_id',$coser_id)
            ->orderBy('id','asc')
            ->get();
        $rows->each(function (UserTime $item)use($now){
            if ($item->time < $now) {
                // time 小于当前时间
                $item->status = 'unavailable'; // 标记为不可约
            }
        });
        return $this->success('成功',$rows);
    }

    /**
     * 设置时间
     * @param Request $request
     * @return \support\Response
     */
    function setTime(Request $request)
    {
        $id = $request->post('id');
        $row = UserTime::find($id);
        if (!$row) {
            return $this->fail('数据不存在');
        }
        if ($row->time < Carbon::now()){
            return $this->fail('不可修改过期时间');
        }
        if ($row->status == 'booked'){
            return $this->fail('不可修改已预约时间');
        }
        $row->status = $row->status === 'unavailable'?'available':'unavailable';
        $row->save();
        return $this->success('修改成功',$row->status);
    }


}
