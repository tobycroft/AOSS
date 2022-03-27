<?php

namespace app\payment\controller;

use Shineupay\Shineupay;
use app\index\model\LogModel;

class Index extends \think\Controller
{
    public function create_order()
    {
        $pay = new Shineupay();
        $ret = $pay->create_order("dingdanhao" . time(), "INR", 100, 12, "beizhushuoming");
        if ($ret["status"] == true) {
            //流水号
            $liu_shui_hao = $ret["trans_sn"];
            //支付地址
//            $pay_url = $ret["pay_url"];
//            echo $pay_url;
        } else {
//            echo $ret["msg"];
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
//            echo $pay_url;
        } else {
//            echo $ret["msg"];
        }
    }

    public function check_balance()
    {
        $pay = new Shineupay();
        $ret = $pay->check_balance();
        if ($ret["status"] == true) {
            $balance = $ret["balance"];
//            echo $balance;
        } else {
//            echo $ret["msg"];
        }
    }

    //代收的回调地址
    public function daishou_huitiao()
    {
        $pay = new Shineupay();
        $ret = $pay->pay_notify();
        \app\payment\model\LogModel::create([
            'log' => json_encode($ret)
//            'log' => $ret
        ]);
        if ($ret["status"] == true) {
            //调用$ret["order_id"]你写道数据库里面去，匹配单子成功的order_id
            echo 'success';
        } else {
            echo 'FAIL';
        }
    }


    public function tiaozhuan()
    {
//        echo "这是个跳转页面";
        echo 'success';
    }

    public function create_daifu()
    {
        $pay = new Shineupay();
        $ret = $pay->create_daifu("dingdanhao" . time(), "INR", 100, 12, "beizhushuoming");
        if ($ret["status"] == true) {
            //流水号
            $liu_shui_hao = $ret["trans_sn"];
            //支付地址
//            $pay_url = $ret["pay_url"];
//            echo $pay_url;
        } else {
//            echo $ret["msg"];
        }
    }

    public function daifu_huitiao()
    {
//        echo "这是个跳转页面";
        echo 'success';
    }


}
