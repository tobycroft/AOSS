<?php

namespace app\payment\controller;

use SendFile\SendFile;
use Shineupay\Shineupay;
use think\Request;
use app\index\model\AttachmentModel;
use app\index\model\LogModel;

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

    //查询代收订单
    public function chaxundaishou()
    {
        $pay = new Shineupay();
        $ret = $pay->pay_check("商户订单号，商户的不是你系统的");
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

    public function check_balance()
    {
        $pay = new Shineupay();
        $ret = $pay->check_balance();
        if ($ret["status"] == true) {
            $balance = $ret["balance"];
            echo $balance;
        } else {
            echo $ret["msg"];
        }
    }

    //代收的回调地址
    public function daishou_huitiao()
    {
        $pay = new Shineupay();
        $ret = $pay->pay_notify("商户订单号，商户的不是你系统的");
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
        $ret = $pay->pay_notify();
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
