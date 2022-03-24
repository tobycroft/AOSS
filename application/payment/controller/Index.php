<?php

namespace app\payment\controller;

use SendFile\SendFile;
use Shineupay\shineuPay;
use think\Request;
use app\index\model\AttachmentModel;
use app\index\model\ProjectModel;

class Index extends \think\Controller
{


    public function initialize()
    {

    }

    public function index()
    {
        dump(config('aliyun.'));
    }

    public function create_order()
    {
        $pay = new shineuPay();
        $pay->create_order("dingdanhao" . time(), 1.00, 12, "beizhushuoming");
    }

    public function succ($data = '成功', $code = 0)
    {
        echo json_encode([
            'code' => $code,
            'data' => $data,
        ], 320);
        exit(0);
    }

    public function fail($data = '失败', $code = 400)
    {
        $this->succ($data, $code);
    }

}
