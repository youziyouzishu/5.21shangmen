<?php

namespace app\process;

use app\admin\model\CoserReport;
use app\admin\model\User;
use app\admin\model\UserTime;
use Carbon\Carbon;
use Workerman\Crontab\Crontab;

class Task
{
    public function onWorkerStart()
    {
        // 每天的7点50执行，注意这里省略了秒位
        new Crontab('50 7 * * *', function () {
            $coser = User::where('role', 2)->get();
            #增加服务时间
            foreach ($coser as $item) {
                $rows = $item->time()->orderBy('id')->take(48)->get();
                $data = [];
                $rows->each(function (UserTime $row)use(&$data) {
                    $data[] = [
                        'time' => $row->time->addDays(1),
                        'status' => $row->status == 'booked' ? 'available' : $row->status,
                    ];
                });
                $item->time()->createMany($data);
            }

            $yesterday = Carbon::yesterday();
            #每日统计
            foreach ($coser as $item) {
                $item->report()->create([
                    'date'=>$yesterday,
                    'project_amount' =>  $item->orders()->whereDate('finish_time',$yesterday)->whereIn('status',[6,7])->sum('project_amount'),
                    'fare_amount' => $item->orders()->whereDate('finish_time',$yesterday)->whereIn('status',[6,7])->sum('fare_amount'),
                    'order_count' => $item->orders()->whereDate('finish_time',$yesterday)->whereIn('status',[6,7])->count(),
                ]);
            }




        });


    }
}