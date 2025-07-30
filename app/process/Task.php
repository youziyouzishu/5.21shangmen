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
            $cosers = User::where('role', 2)->get();
            $today = Carbon::today();

            foreach ($cosers as $coser) {
                $yesterday = $today->copy()->subDay();

                // 获取昨天的所有时间段（时间 + 状态）
                $yesterdayRecords = $coser->times()
                    ->whereDate('time', $yesterday)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $key = Carbon::parse($item->time)->format('H:i');
                        return [
                            $key => [
                                'base_time' => Carbon::parse($item->time),
                                'status'    => $item->status,
                            ],
                        ];
                    });

                if ($yesterdayRecords->isEmpty()) {
                    echo "用户 {$coser->id} 昨天无服务时间记录，跳过。\n";
                    continue;
                }

                // 获取未来 4 天已有的 time 列表
                $existingTimes = $coser->times()
                    ->where('time', '>=', $today)
                    ->where('time', '<', $today->copy()->addDays(4))
                    ->get()
                    ->pluck('time')
                    ->map(fn($t) => $t->format('Y-m-d H:i:s'))
                    ->toArray();

                $newData = [];

                // 遍历昨天的时间段，在未来 4 天同步补全
                for ($day = 0; $day < 4; $day++) {
                    foreach ($yesterdayRecords as $timeKey => $record) {
                        // 同步到未来的同一时间段
                        $futureTime = $today->copy()->addDays($day)->setTimeFrom($record['base_time']);
                        $futureTimeStr = $futureTime->format('Y-m-d H:i:s');

                        if (!in_array($futureTimeStr, $existingTimes)) {
                            $newData[] = [
                                'time'   => $futureTime,
                                'status' => $record['status'] ?? 'available',
                            ];
                        }
                    }
                }

                if (!empty($newData)) {
                    $coser->times()->createMany($newData);
                    echo "用户 {$coser->id} 补全了 " . count($newData) . " 条时间段。\n";
                }
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