<?php

namespace app\payment\controller;

use SendFile\SendFile;
use Shineupay\Shineupay;
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
        $pay = new Shineupay();
        $ret = $pay->create_order("dingdanhao" . time(), 1.00, 12, "beizhushuoming");
        if ($ret["status"] == true) {
            //流水号
            $liu_shui_hao = $ret["trans_sn"];
            //支付地址
            $pay_url = $ret["pay_url"];
            echo $pay_url;
        } else {
            echo $ret["msg"];
        }
    }

    public function huidiao()
    {
        $pay = new Shineupay();
        $ret = $pay->create_order("dingdanhao" . time(), 1.00, 12, "beizhushuoming");
        if ($ret["status"] == true) {
            //流水号
            $liu_shui_hao = $ret["trans_sn"];
            //支付地址
            $pay_url = $ret["pay_url"];
        }
    }


    public function tiaozhuan()
    {
        $pay = new Shineupay();
        $ret = $pay->create_order("dingdanhao" . time(), 1.00, 12, "beizhushuoming");
        if ($ret["status"] == true) {
            //流水号
            $liu_shui_hao = $ret["trans_sn"];
            //支付地址
            $pay_url = $ret["pay_url"];
        }
    }


    public function daifu()
    {
        $pay = new Shineupay();
        $ret = $pay->create_order("dingdanhao" . time(), 1.00, 12, "beizhushuoming");
        if ($ret["status"] == true) {
            //流水号
            $liu_shui_hao = $ret["trans_sn"];
            //支付地址
            $pay_url = $ret["pay_url"];
        }
    }


}
