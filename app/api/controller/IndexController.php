<?php

namespace app\api\controller;

use app\admin\model\User;
use app\admin\model\UserTime;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;

class IndexController extends Base
{
    protected array $noNeedLogin = ['*'];

    public function index(Request $request)
    {
        $coser_id = 1;
        $date = '2025-07-29';
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
        $rows->append('time_formatted');
        return $this->success('成功',$rows);

    }

}
