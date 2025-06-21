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
        dump(Carbon::parse('2025-06-21'),Carbon::today());
    }

}
