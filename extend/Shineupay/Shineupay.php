<?php

namespace Shineupay;

/****006支付应用类 OK****/
class Shineupay
{

    /***配置参数***/
    var $merchantId; //商户编号
    var $secret_key;  //商户密钥
    var $pay_notify_url;  //代收回调域名
    var $pay_callbackUrl; //代收跳转域名
    var $df_notify_url; //代付回调域名
    var $tixian;        //提现密钥

    /****构造函数*****/
    public function __construct()
    {
        $this->merchantId = "BFURJK9KB0N45734"; //商户编号;
        $this->secret_key = 'ae88b583d79b4ccab28c63592e05ca46'; //商户密钥
        $this->pay_notify_url = 'http://upload.tuuz.cc:81/payment/index/huidiao'; //代收回调域名
        $this->pay_callbackUrl = 'http://upload.tuuz.cc:81/payment/index/tiaozhuan'; //代收跳转域名
        $this->df_notify_url = 'http://upload.tuuz.cc:81/payment/index/daifu';   //代付回调域名
        $this->tixian = 'e10adc3949ba59abbe56e057f20f883e';                 //提现密钥
    }

    public function create_order($order, $money, $user_id, $remark)
    {
        $key = $this->secret_key; //商户密钥
        $url = "https://testgateway.shineupay.com/pay/create"; //网关地址
        $params["orderId"] = $order;                           //订单号
        $params["amount"] = $money; //支付金额
        $getMillisecond = $this->getMillisecond(); //毫秒时间戳
        $params["details"] = $remark; //支付商品说明
        $params["userId"] = $user_id;    //商户会员标识
        $data['body'] = $params;
        $data['merchantId'] = $this->merchantId;
        $data['timestamp'] = "$getMillisecond";
        $sign = $this->sign($key, $data, $getMillisecond);
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "Api-Sign:$sign");
        $json = $this->curlPost($url, $data, 5, $headers, $getMillisecond);
        $res = json_decode($json, true);

        if ($res['body']['contentType'] == '0') {
            return array('status' => true, 'pay_url' => $res['body']['content'], 'trans_sn' => $res['body']['transactionId'], 'msg' => '创建成功');
        } else {
            return array('status' => false, 'msg' => '创建失败');
        }
    }

    //查询代收订单
    public function pay_check($trans_sn)
    {
        $key = $this->secret_key; //商户密钥
        $url = "https://testgateway.shineupay.com/pay/query"; //网关地址
        $params["orderId"] = $trans_sn;    //订单号
        $params["details"] = "details"; //支付商品说明
        $params['userId'] = "57899";
        $getMillisecond = $this->getMillisecond(); //毫秒时间戳
        $data['body'] = $params;
        $data['merchantId'] = $this->merchantId;
        $data['timestamp'] = "$getMillisecond";
        $sign = $this->sign($key, $data);
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "Api-Sign:$sign");
        $json = $this->curlPost($url, $data, 5, $headers, $getMillisecond);
        $res = json_decode($json, true);
        if ($res['body']['status'] == 1) {
            $code = array('code' => '200', 'trans_sn' => $res['data']['platOrderId'], 'status' => "PAY_SUCCESS", 'msg' => '支付成功');
        } else if ($res['body']['status'] == 0) {
            $code = array('code' => '200', 'trans_sn' => $res['data']['platOrderId'], 'status' => "create_order", 'msg' => '创建订单');
        } else if ($res['body']['status'] == 2) {
            $code = array('code' => '0', 'msg' => '支付失败');
        } else if ($res['body']['status'] == 3) {
            $code = array('code' => '200', 'trans_sn' => $res['data']['platOrderId'], 'status' => "PAY_ING", 'msg' => '正在支付中');
        }
    }

    //回调代收订单
    public function pay_notify()
    {
        $contents = file_get_contents('php://input');
        $secret_key = $this->secret_key; //商户密钥
        $str = $contents . "|" . $secret_key;
        $signr = MD5($str);
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        $sign = $headers['Api-Sign'];
        if ($sign != $signr) {
            return ["status" => false, "msg" => "签名出错"];
        }
        $post = json_decode($contents, true);
        $params['orderId'] = $post['body']['orderId']; //商户单号
        $params['platformOrderId'] = $post['body']['platformOrderId']; //第三方单号
        $status = $params['status'] = $post['body']['status']; //支付状态     0	尚未付款，订单已创建1	付款成功2	付款失败，请重新支付（二维码过期，超时付款等）3	付款中，表示等待付款中91	金额异常，支付订单金额出现异常
        if ($post['status'] == 2) $params['message'] = $post['body']['message']; //消息通知
        $params['amount'] = $post['body']['amount']; //支付金额
        $params['payType'] = $post['body']['payType']; //支付通道
        if ($params['status'] == 1) $params['PayTime'] = $post['PayTime']; //支付时间
        if ($signr == $sign) {
            return ["status" => true, "data" => $params];
        } else {
            return ["status" => false, "msg" => ""];
        }
    }


    /****查询钱包余额****/
    public function check_balance()
    {
        $key = $this->secret_key; //商户密钥
        $url = "https://testgateway.shineupay.com/withdraw/balance"; //网关地址
        $getMillisecond = $this->getMillisecond(); //毫秒时间戳
        $data['body'] = (object)null;
        $data['merchantId'] = $this->merchantId;
        $data['timestamp'] = "$getMillisecond";
        $sign = $this->sign($key, $data);
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "Api-Sign:$sign");
        $json = $this->curlPost($url, $data, 5, $headers, $getMillisecond);
        $res = json_decode($json, true);
        if ($res['body']['status'] == '0') {
            $code = array('status' => true, 'balance' => $res['body']['amount']);
        } else {
            return ["status" => false, "msg" => ""];
        }
    }


    /****创建代付订单****/
    public function df_create()
    {
//接受提现ID
        $tx_id = $_GET['tx_id']; //接受提现ID
        $tx_key = $_GET['tx_key']; //请求密钥
        $my_key = md5($tx_id . '48lQBCAp'); //加密算法

//判断是否为空
        if ($tx_id == '' || $tx_key == '') {
            $code = array('code' => '0', 'msg' => '参数错误');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }


//判断密钥是否正确
        if ($tx_key != $my_key) {
            $code = array('code' => '0', 'msg' => '密钥错误');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

//链接MYSQL
        $conn = database::Get_Mysql();

//向指定表插入数据 #1是提现的id
        $sql = "insert into id_user_bank_card_withdrawal_id set id=$tx_id";
        $stmt = $conn->prepare($sql); //预处理SQL
        $stmt->execute(); //执行

//判断是否添加失败
        if ($stmt->affected_rows == 0) {
            $code = array('code' => '0', 'msg' => '提现订单已经提交，请勿重复提交');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }


//获取订单信息
        $sql = "SELECT id,received_money,a16,a22,a21,a20,audit_status,paid_status,order_id,bank_branch_name FROM `id_user_bank_card_withdrawal` where id=$tx_id";
        $stmt = $conn->prepare($sql); //预处理SQL
        $stmt->execute(); //执行
        $result = $stmt->get_result(); //获取结果资源
        if ($result->num_rows > 0) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $down_sn = $row['order_id']; //商户订单号
            $amount = $row['received_money']; //代付金额
            $bank_account = $row['a16'];      //收款人准确姓名
            $bank_cardno = $row['a22']; //银行卡号/upi收款账户
            $idno = $row['a21']; //收款人准确电子邮箱
            $mobile = $row['a20']; //电话号码 收款人准确 电话号码(去除+91， 纯数字电话号码)
            $audit_status = $row['audit_status']; //审核状态:0待审核 1审核通过 2审核失败
            $paid_status = $row['paid_status']; //代付状态：0未提交 3已提交 1支付成功 2支付失败
            $bank_branch_name = $row['bank_branch_name']; //ifsccoded
        } else {
            $code = array('code' => '0', 'msg' => '订单不存在');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

//拦截未审核状态的订单
        if ($audit_status == 2) {
            $code = array('code' => '0', 'msg' => '订单未通过审核');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

//拦截代付状态  成功的订单
        if ($paid_status == 1) {
            $code = array('code' => '0', 'msg' => '订单已支付');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }

//处理电话号码
        $mobile = str_replace("+91", "", $mobile);
//组装数据
        $params['version'] = "1.0.0"; //默认传1.0.0
        $params['advPasswordMd5'] = $this->tixian; //string	是	交易密码的md5值（32位小写），详看交易密码说明
        $params['orderId'] = $down_sn; //string	是	商户订单编号，请确保唯一，最多允许200个字符
        $params['amount'] = $amount / 100; //float	是	提现金额
        $params['details'] = "details"; //string		提现说明
        $params['notifyUrl'] = $this->df_notify_url; //string		异步通知地址
        $params['receiveCurrency'] = "INR"; //string		收款人收款货币 印度传INR 巴西传BRL
        $params['settlementCurrency'] = "INR";       //string		订单结算币种 INR,BRL,IUSDT,BUSDT
        $params['prodName'] = "ind.bankcard.payout"; //string		代付类型编码

//银行卡信息
        $bankCardInfo["userName"] = $bank_account; //string	是	银行卡的持卡人
        $bankCardInfo['bankCardNumber'] = $bank_cardno; //string	是	银行卡的卡号
        $bankCardInfo['IFSC'] = $bank_branch_name; //String 	是	银行卡持卡人IFSC码
        $bankCardInfo['phone'] = $mobile; //string	是	银行卡的预留手机号
        $bankCardInfo['email'] = $idno; //String	是	用户邮箱
        $bankCardInfo['province'] = ""; //String	否	银行所在省名称
        $bankCardInfo['city'] = "";     //String	否	银行所在城市名称
        $bankCardInfo['address'] = ""; //String	否	所在地区名称
        $params['extInfo'] = $bankCardInfo; //收款人银行信息信息

        $key = $this->secret_key; //商户密钥
        $url = "https://testgateway.shineupay.com/v2/Withdraw/Create"; //网关地址
        $getMillisecond = $this->getMillisecond(); //毫秒时间戳
//组装数据
        $data['body'] = $params;
        $data['merchantId'] = $this->merchantId;
        $data['timestamp'] = "$getMillisecond";
//报文签名
        $sign = $this->sign($key, $data);
//封装请求头
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "Api-Sign:$sign");
//发起post请求
        $json = $this->curlPost($url, $data, 5, $headers, $getMillisecond);
        $res = json_decode($json, true);

//返回值
// 参数名	      类型	 说明
// merchant	     string	商户号
// orderId	     string	商户订单号
// platOrderId	 string	平台订单号
// amount	     string	金额
// msg	         string	处理消息
// status	     string	交易状态，值见数据字典
// sign	         string	签名

//判断是否成功
        if ($res['status'] == 0) {
//成功创建 更改订单代付状态 为已提交=3
            $sql = "UPDATE `id_user_bank_card_withdrawal` SET `paid_status` = '3' WHERE `id_user_bank_card_withdrawal`.`id` =$tx_id";
            $stmt = $conn->prepare($sql); //预处理SQL
            $stmt->execute(); //执行
//返回字符串
            $code = array('code' => '200', 'amount' => "", 'settle_sn' => $res['body']['platformOrderId'], 'down_sn' => '', 'msg' => '提交成功');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        } else {
//删除刚刚提交的值
            $sql = "delete from id_user_bank_card_withdrawal_id where id=$tx_id";
            $stmt = $conn->prepare($sql); //预处理SQL
            $stmt->execute(); //执行
//返回失败值
            $code = array('code' => '0', 'msg' => $res['msg']);
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }


    /****查询代付订单****/
    public function df_query()
    {
//接受参数
        $order = $_GET['order']; //商户订单号

//判断参数是否为空
        var_empty($order, '0', '商户订单号不能为空');

//判断参数类型
        var_num($order, '0', '商户订单号是数字');

//接口验证签名 【整合系统时候使用.暂时不用管】
        $hkey = md5($_GET['order'] . $_GET['money']);
// if ($_GET['key'] != $hkey) {
//     info('0', '签名错误');
// }

        $key = $this->secret_key; //商户密钥
        $url = "https://testgateway.shineupay.com/withdraw/query"; //网关地址
        $params["orderId"] = $order; //订单号
        $getMillisecond = $this->getMillisecond(); //毫秒时间戳
//组装数据
        $data['body'] = $params;
        $data['merchantId'] = $this->merchantId;
        $data['timestamp'] = "$getMillisecond";
//报文签名
        $sign = $this->sign($key, $data);
//封装请求头
        $headers = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache", "Api-Sign:$sign");
//发起post请求
        $json = $this->curlPost($url, $data, 5, $headers, $getMillisecond);
        $res = json_decode($json, true);
        /*
        交易状态：status
        0	创建成功，尚未汇款
        1	汇款成功
        2	汇款失败，详看错误原因请查看message字段，
        交易状态：status
        create_order	创建订单
        PAY_ING	    正在支付中
        PAY_FAIL	支付失败
        PAY_SUCCESS	支付成功

        //返回值
        参数名	     类型	  说明
        platformOrderId   string   平台订单编号
        createTime   string   时间格式
        status       int      交易状态
        amount       float    金额
        merchantId   string   商户号
        timestamp    int      毫秒级时间戳
        */

//判断是否成功
        if ($res['status'] == 1) {
            $code = array('code' => '200', 'trans_sn' => $res['bofy']['platformOrderId'], 'status' => "PAY_SUCCESS", 'msg' => '支付成功');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else if ($res['status'] == 0) {
            $code = array('code' => '200', 'trans_sn' => $res['bofy']['platformOrderId'], 'status' => "create_order", 'msg' => '创建订单');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else if ($res['status'] == 2) {
            $code = array('code' => '0', 'msg' => '查询失败');
            echo json_encode($code, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }


    /****回调代付订单****/
    public function df_notify()
    {


//接受Post值
        $contents = file_get_contents('php://input');
        file_put_contents("pay_notify.txt", $contents, FILE_APPEND);
//全局参数
        $secret_key = $this->secret_key; //商户密钥
//报文加密
        $str = $contents . "|" . $secret_key;
        $signr = MD5($str);
//转成数组
        $post = json_decode($contents, true);
//接受参数
        $params['orderId'] = $post['body']['orderId']; //商户单号
        $platformOrderId = $params['platformOrderId'] = $post['body']['platformOrderId']; //第三方单号
        $params['status'] = $post['body']['status']; //支付状态     0	尚未付款，订单已创建1	付款成功2	付款失败，请重新支付（二维码过期，超时付款等）3	付款中，表示等待付款中91	金额异常，支付订单金额出现异常
        if ($post['status'] == 2) $params['message'] = $post['body']['message']; //消息通知

//接受头部header信息
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
//获取签名MD5值
        $sign = $headers['Api-Sign'];
//存储头部信息
        file_put_contents("pay_notify.txt", json_encode($headers), FILE_APPEND);

//判断签名是否正确
        if ($signr == $sign) {

//链接MYSQL
            $conn = database::Get_Mysql();

//获取当前订单状态代付状态
            $sql = "SELECT paid_status FROM `id_user_bank_card_withdrawal` where order_id ='$platformOrderId'";
            $stmt = $conn->prepare($sql); //预处理SQL
            $stmt->execute(); //执行
            $result = $stmt->get_result(); //获取结果资源
            if ($result->num_rows > 0) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $paid_status = $row['paid_status']; //代付状态：0未提交 3已提交 1支付成功 2支付失败
            }

//代付状态 必须不等于支付成功，如果已经支付成功了，则不执行
            if ($post['status'] != 1) {
//执行支付成功
                if ($params['status'] == 1) {
//更改提现订单状态 改为1=支付成功
                    $sql = "UPDATE `id_user_bank_card_withdrawal` SET `paid_status` = '1' WHERE order_id ='$platformOrderId'";
                    $stmt = $conn->prepare($sql); //预处理SQL
                    $stmt->execute(); //执行
                }
//执行支付失败
                if ($params['status'] == 2) {
//更改提现订单状态 改为2=支付失败
                    $sql = "UPDATE `id_user_bank_card_withdrawal` SET `paid_status` = '2' WHERE order_id ='$platformOrderId'";
                    $stmt = $conn->prepare($sql); //预处理SQL
                    $stmt->execute(); //执行
                }
            }

//返回结果
            echo 'success'; //成功
// $str = '第三方订单号：' . $settle_sn;
// $str .= '商户单号：' . $down_sn;
// $str .= '支付状态：' . $status;
// $str .= '支付金额：' . $amount;
// file_put_contents("pay_sites.txt", $str);  //临时记录日志，海乐正式对接时候请删除
        } else {
            echo 'FAIL'; // 失败
        }
    }


//获取毫秒数
    public function getMillisecond()
    {
        list($microsecond, $time) = explode(' ', microtime()); //' '中间是一个空格
        return (float)sprintf('%.0f', (floatval($microsecond) + floatval($time)) * 1000);
    }


//签名
    public function sign($key, $params)
    {
        $params = array_filter($params);
        $str = json_encode($params) . "|" . $key;
        $sign = MD5($str);
        return $sign;
    }


//asc排序
    public function asc_sort($params = array())
    {
        if (!empty($params)) {
            $p = ksort($params);
            if ($p) {
                $str = '';
                foreach ($params as $k => $val) {
                    $str .= $k . '=' . $val . '&';
                }
                $strs = rtrim($str, '&');
                return $strs;
            }
        }
        return false;
    }

//post请求方式
    public function curlPost($url, $data, $timeout, $headers, $getMillisecond)
    {
        $data = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl); //捕抓异常
        }
        curl_close($curl);
        return $output;
    }
}
