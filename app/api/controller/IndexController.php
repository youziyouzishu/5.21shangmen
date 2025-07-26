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
        $user = User::where('id',1)->first();
        /** @var UserTime $firstTime */
        $firstTime  = $user->times()->where('time', '>=',  Carbon::now())->orderBy('id')->first();
        dump($firstTime?->time->format('H:i'));
        return $this->success('成功', $firstTime);
    }

}
