<?php

namespace app\api\controller;

use app\admin\model\Area;
use app\admin\model\CoserReport;
use app\admin\model\Order;
use app\admin\model\OrderComment;
use app\admin\model\UserCollect;
use app\admin\model\Coser;
use app\admin\model\Costume;
use app\admin\model\Project;
use app\admin\model\User;
use app\admin\model\UserCostume;
use app\admin\model\UserMoneyLog;
use app\admin\model\UserTime;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Request;
use support\Response;

class CoserController extends Base
{


    /**
     * Coser列表
     * @param Request $request
     * @return \support\Response
     */
    function list(Request $request)
    {
        $city_id = $request->post('city_id');
        $status = $request->post('status');#0全部 1可预约 2服务中
        $lat = $request->post('lat');
        $lng = $request->post('lng');
        $keyword = $request->post('keyword');

        if (empty($lat) || empty($lng)){
            return $this->fail('请选择经纬度');
        }

        $baseQuery = User::where(['city_id' => $city_id, 'role' => 2]);
        $paginator = $baseQuery
            ->when(!empty($keyword),function ($query)use($keyword){
                $query->whereLike('name', $keyword);
            })
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->whereHas('times', function ($query) {
                        $query->gtNow()->where('status', 'available');
                    });
                }
                if ($status == 2) {
                    $query->whereHas('times', function ($query) {
                        $query->gtNow()->where('status', 'booked');
                    });
                }
            }, function ($query) {
                $query->whereHas('times', function ($query) {
                    $query->gtNow()->whereIn('status', ['available', 'booked']);
                });
            });

        // 计算距离并按距离排序
        $paginator = $paginator->selectRaw('*, 
    (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance',
            [$lat, $lng, $lat])
//            ->havingRaw('distance <= 50') // 可选：过滤一定距离内的骑手
            ->orderBy('distance', 'asc') // 按距离升序排序
            ->paginate(); // 保留分页

        // 对当前页的每一项进行处理
        $rows = $paginator->getCollection()->map(function (User $user) use ($request) {
            // 获取第一个时间段
            /** @var UserTime $firstTime */
            $firstTime = $user->times()->where('time', '>=',  Carbon::now())->orderBy('id')->whereIn('status', ['available', 'booked'])->first();
            return array_merge($user->toArray(), [
                'current_status' => $firstTime?->status,
                'first_time' => $firstTime?->time->format('H:i'),
                'is_collect' => UserCollect::where([
                    'user_id' => $request->user_id,
                    'coser_id' => $user->id
                ])->exists(),
//                'distance' => $user->distance // 添加距离信息
            ]);
        });
        return $this->success('成功', $rows);
    }


    /**
     * 详情
     * @param Request $request
     * @return \support\Response
     */
    function detail(Request $request)
    {
        $id = $request->post('id');
        $lat = $request->post('lat');
        $lng = $request->post('lng');
        if (empty($lat) || empty($lng)){
            return $this->fail('请选择经纬度');
        }
        $row = User::find($id);
        $distance = Area::getDistanceFromLngLat($lat, $lng, $row->lat, $row->lng);
        $row->is_collect = UserCollect::where([
            'user_id' => $request->user_id,
            'coser_id' => $id
        ])->exists();

        /**@var UserTime $firstTime */
        $firstTime = $row->times()->where('time', '>=',  Carbon::now())->orderBy('id')->whereIn('status', ['available', 'booked'])->first();
        $row->current_status = $firstTime?->status;
        $row->first_time = $firstTime?->time->format('H:i');
        $row->distance = $distance;
        return $this->success('成功', $row);
    }

    /**
     * 获取评价列表
     * @param Request $request
     * @return \support\Response
     */
    function getCommentList(Request $request)
    {
        $id = $request->post('id');
        $rows = OrderComment::where('coser_id', $id)
            ->with(['user'])
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }


    /**
     * 收藏|取消收藏
     * @param Request $request
     * @return \support\Response
     */
    function collect(Request $request)
    {
        $id = $request->post('id');
        $user = UserCollect::where(['user_id' => $request->user_id, 'coser_id' => $id])->first();
        if ($user) {
            $user->coser()->decrement('fans');
            $user->delete();
            return $this->success('成功', false);
        } else {
            $user = UserCollect::create([
                'user_id' => $request->user_id,
                'coser_id' => $id,
            ]);
            $user->coser()->increment('fans');
            return $this->success('成功', true);
        }
    }


    /**
     * 申请
     * @param Request $request
     * @return Response
     */
    function apply(Request $request)
    {
        $name = $request->post('name');
        $birthday = $request->post('birthday');
        $age = $request->post('age');
        $sex = $request->post('sex');
        $card_front = $request->post('card_front');
        $card_back = $request->post('card_back');
        $card_handheld = $request->post('card_handheld');
        $exist = Coser::where(['user_id' => $request->user_id, 'status' => 0])->first();
        if ($exist) {
            return $this->fail('平台审核中，请勿重复申请');
        }
        $exist = Coser::where(['user_id' => $request->user_id, 'status' => 1])->first();
        if ($exist) {
            return $this->fail('已通过平台审核，请勿重复申请');
        }
        Coser::create([
            'user_id' => $request->user_id,
            'name' => $name,
            'birthday' => $birthday,
            'age' => $age,
            'sex' => $sex,
            'card_front' => $card_front,
            'card_back' => $card_back,
            'card_handheld' => $card_handheld,
        ]);
        return $this->success('申请成功');
    }


    /**
     * Coser 首页
     * @param Request $request
     * @return Response
     */
    function index(Request $request)
    {
        $user = User::find($request->user_id);
        $firstTime = $user->times()->where('time', '>=',  Carbon::now())->orderBy('id')->first();
        if ($firstTime->status == 'available') {
            $status_text = '接单中';
        }
        if ($firstTime->status == 'unavailable') {
            $status_text = '休息中';
        }
        if ($firstTime->status == 'booked') {
            $status_text = '服务中';
        }
        $query = Order::where('coser_id', $request->user_id)->whereDate('end_service_time', Carbon::today());
        $today_amount = $query->sum('pay_amount');
        $project_amount = $query->sum('project_amount');
        $fare_amount = $query->sum('fare_amount');
        $count = $query->count();
        return $this->success('成功', [
            'status_text' => $status_text,
            'today_amount' => $today_amount,
            'project_amount' => $project_amount,
            'fare_amount' => $fare_amount,
            'count' => $count
        ]);

    }

    /**
     * 数据统计
     * @param Request $request
     * @return Response
     */
    function report(Request $request)
    {
        $start_date = $request->post('start_date');
        $end_date = $request->post('end_date');
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        $month_project_amount = CoserReport::where('coser_id', $request->user_id)->whereBetween('date', [$start_date, $end_date])->sum('project_amount');
        $month_fare_amount = CoserReport::where('coser_id', $request->user_id)->whereBetween('date', [$start_date, $end_date])->sum('fare_amount');
        $month_count = CoserReport::where('coser_id', $request->user_id)->whereBetween('date', [$start_date, $end_date])->sum('order_count');
        $month_amount = $month_project_amount + $month_fare_amount;
        $list = CoserReport::where('coser_id', $request->user_id)->whereBetween('date', [$start_date, $end_date])->orderByDesc('id')->get();
        return $this->success('成功', [
            'month_project_amount' => $month_project_amount,
            'month_fare_amount' => $month_fare_amount,
            'month_amount' => $month_amount,
            'month_count' => $month_count,
            'list' => $list,
        ]);
    }

    /**
     * 收益明细
     * @param Request $request
     * @return Response
     */
    function income(Request $request)
    {
        $date = $request->post('date');
        $date = Carbon::parse($date);
        $year = $date->year;
        $month = $date->month;
        $orders = Order::where('coser_id', $request->user_id)
            ->with(['items'])
            ->whereYear('end_service_time', $year)
            ->whereMonth('end_service_time', $month)
            ->orderByDesc('id')
            ->paginate()
            ->items();
        return $this->success('成功', $orders);
    }


}
