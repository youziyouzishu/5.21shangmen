<?php

namespace app\admin\controller;

use Carbon\Carbon;
use support\Request;
use support\Response;
use app\admin\model\Coser;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * Coser申请记录 
 */
class CoserController extends Crud
{
    
    /**
     * @var Coser
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Coser;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('coser/index');
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
        return view('coser/insert');
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
            $id = $request->post('id');
            $status = $request->post('status');
            $row = $this->model->find($id);
            if ($row && $status == 1 && $row->status == 0) {
                //审核通过
                #更改身份
                $row->user->role = 2;
                $row->user->save();
                #创建时间
                $days = 4;
                $startDate = Carbon::today(); // 从今天开始
                $data = [];
                for ($i = 0; $i < $days; $i++) {
                    // 获取当前循环的日期
                    $currentDate = $startDate->copy()->addDays($i);

                    // 设置当天的时间段起点和终点
                    $start = Carbon::createFromTime(0, 0, 0, $currentDate->getTimezone());
                    $end = Carbon::createFromTime(23, 30, 0, $currentDate->getTimezone());
                    $start->setDate($currentDate->year, $currentDate->month, $currentDate->day);
                    $end->setDate($currentDate->year, $currentDate->month, $currentDate->day);

                    while ($start <= $end) {
                        // 判断时间段状态
                        if ($start >= Carbon::createFromTime(9, 0, 0, $currentDate->getTimezone()) && $start <= Carbon::createFromTime(18, 0, 0, $currentDate->getTimezone())) {
                            $status = 'available';
                        } else {
                            $status = 'unavailable';
                        }
                        $data[] = [
                            'time' =>  $start->copy(),
                            'status' => $status,
                        ];

                        $start->addMinutes(30);
                    }
                }
                // 批量插入数据
                $row->user->time()->createMany($data);
            }
            return parent::update($request);
        }
        return view('coser/update');
    }

}
