<?php

/**
 * Sdk
 * @version     v1.0
 */
class Sdk
{
    /**
     * 接⼝统⼀请求地址
     */
    public $baseUrl = 'Please contact technical support';

    /**
     * API 访问密钥
     */
    public $accessKey = 'Please contact technical support';

    /**
     * 签名认证加密所使⽤的密钥
     */
    public $secretKey = 'Please contact technical support';

    /**
     * 生成UUID
     * 标准的UUID格式为：xxxxxxxx-xxxx-xxxx-xxxxxx-xxxxxxxxxx(8-4-4-4-12)
     * @return   string
     */
    public function generateUUID()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $uuid;
    }

    /**
     * 生成时间戳-毫秒级
     * @return   string
     */
    public function generateTimestamp()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * 生成签名
     * 使用Base64(HMAC_SHA1(mapString, secretKey))进行加密
     * @param    string     $timestamp    时间戳
     * @param    string     $nonce        UUID
     * @param    array      $params       参数列表
     * @param    string     $reqMethod    请求方法
     * @return   string
     */
    public function generateSign(string $timestamp = '', string $nonce = '', array $params = [], string $reqMethod = '')
    {
        $map = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce
        ];
        // GET方法请求的参数需加在加签函数中
        if ($reqMethod == 'GET') {
            $map = array_merge($params, $map);
        }
        // 参数键名按照 ASCII 码从小到大排序（字典序）
        ksort($map);
        // 加签字符串
        $mapString = '';
        foreach ($map as $k => $v) {
            $mapString = $mapString . $k . '=' . $v . '&';
        }
        // ※最后一个&符号需去除
        $mapString = substr($mapString, 0, strlen($mapString) - 1);
        return base64_encode(hash_hmac('sha1', $mapString, $this->secretKey, true));
    }

    /**
     * 发送Post请求
     * @param    string     $url         请求url
     * @param    array      $headers     请求头部
     * @param    array      $postData    请求参数
     */
    public function sendPost(string $url = '', array $headers = [], array $postData = [])
    {
        // post指定Content-type类型
        $header[] = 'Content-type: application/json;charset=UTF-8';
        // curl的HTTP头字段需要数组格式
        foreach ($headers as $k => $v) {
            $header[] = $k . ': ' . $v;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }

    /**
     * 发送Post请求
     * @param    string     $url         请求url
     * @param    array      $headers     请求头部
     * @param    array      $params      请求参数
     */
    public function sendGet(string $url = '', array $headers = [], array $params = [])
    {
        // curl的HTTP头字段需要数组格式
        foreach ($headers as $k => $v) {
            $header[] = $k . ': ' . $v;
        }
        // GET参数追加到url后
        if (count($params) > 0) {
            $url = $url . '?';
            foreach ($params as $k => $v) {
                $url = $url . $k . '=' . $v . '&';
            }
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $contents = curl_exec($curl);
        curl_close($curl);
        return $contents;
    }

    /**
     * 查询商户账户列表
     */
    public function getAccountList()
    {
        // 获取时间戳
        $timestamp = $this->generateTimestamp();
        // 获取UUID
        $nonce = $this->generateUUID();
        // 接口地址
        $requestUrl = $this->baseUrl . '/api/v1/cashier/accounts';
        // 请求方式
        $reqMethod = 'GET';
        // 请求参数
        $params = [];
        // 生成签名
        $sign = $this->generateSign($timestamp, $nonce, $params, $reqMethod);
        // 头部指定必传参数
        $headers = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign
        ];
        $data = $this->sendGet($requestUrl, $headers, $params);
        return $data;
    }

    /** 
     * 新增收款单
     */
    public function createInvoice()
    {
        // 获取时间戳
        $timestamp = $this->generateTimestamp();
        // 获取UUID
        $nonce = $this->generateUUID();
        // 接口地址
        $requestUrl = $this->baseUrl . '/api/v1/cashier/product';
        // 请求方式
        $reqMethod = 'POST';
        // 请求参数
        $params = [
            // 商户名
            'storeName' => 'Store',
            // 收款金额
            'productsAmount' => 100,
            // 法币类型-枚举值见API文档
            'currencyType' => 'CNY'
        ];
        // 生成签名
        $sign = $this->generateSign($timestamp, $nonce, $params, $reqMethod);
        // 头部指定必传参数
        $headers = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign
        ];
        $data = $this->sendPost($requestUrl, $headers, $params);
        return $data;
    }

    /**
     * 查询收款单列表
     */
    public function getInvoiceList()
    {
        // 获取时间戳
        $timestamp = $this->generateTimestamp();
        // 获取UUID
        $nonce = $this->generateUUID();
        // 接口地址
        $requestUrl = $this->baseUrl . '/api/v1/cashier/products';
        // 请求方式
        $reqMethod = 'GET';
        // 请求参数
        $params = [];
        // 生成签名
        $sign = $this->generateSign($timestamp, $nonce, $params, $reqMethod);
        // 头部指定必传参数
        $headers = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign
        ];
        $data = $this->sendGet($requestUrl, $headers, $params);
        return $data;
    }

    /**
     * 查询流⽔明细列表
     */
    public function getTransactionList()
    {
        // 获取时间戳
        $timestamp = $this->generateTimestamp();
        // 获取UUID
        $nonce = $this->generateUUID();
        // 接口地址
        $requestUrl = $this->baseUrl . '/api/v1/cashier/books';
        // 请求方式
        $reqMethod = 'GET';
        // 请求参数
        $params = [
            'page' => 1, 
            'size' => 50
        ];
        // 生成签名
        $sign = $this->generateSign($timestamp, $nonce, $params, $reqMethod);
        // 头部指定必传参数
        $headers = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign
        ];
        $data = $this->sendGet($requestUrl, $headers, $params);
        return $data;
    }

    /** 
     * 新增充值订单
     */
    public function createRecharge()
    {
        // 获取时间戳
        $timestamp = $this->generateTimestamp();
        // 获取UUID
        $nonce = $this->generateUUID();
        // 接口地址
        $requestUrl = $this->baseUrl . '/api/v1/cashier/add/recharge';
        // 请求方式
        $reqMethod = 'POST';
        // 请求参数
        $params = [
            // 付款⾦额
            'amount' => 100,
            // 主链类型-枚举值见API文档
            'assetType' => 102,
            // 代币类型-枚举值见API文档
            'tokenType' => 901
        ];
        // 生成签名
        $sign = $this->generateSign($timestamp, $nonce, $params, $reqMethod);
        // 头部指定必传参数
        $headers = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign
        ];
        $data = $this->sendPost($requestUrl, $headers, $params);
        return $data;
    }

    /** 
     * 新增代付订单
     */
    public function createTransfer()
    {
        // 获取时间戳
        $timestamp = $this->generateTimestamp();
        // 获取UUID
        $nonce = $this->generateUUID();
        // 接口地址
        $requestUrl = $this->baseUrl . '/api/v1/cashier/add/transfer';
        // 请求方式
        $reqMethod = 'POST';
        // 请求参数
        $params = [
            // 付款⾦额
            'amount' => 100,
            // 主链类型-枚举值见API文档
            'assetType' => 102,
            // 代币类型-枚举值见API文档
            'tokenType' => 901,
            // 收款地址
            'addressTo' => '0xb05ac0796a3bdaccc7e7a32a3b6df5454498a771'
        ];
        // 生成签名
        $sign = $this->generateSign($timestamp, $nonce, $params, $reqMethod);
        // 头部指定必传参数
        $headers = [
            'access_key' => $this->accessKey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'sign' => $sign
        ];
        $data = $this->sendPost($requestUrl, $headers, $params);
        return $data;
    }
}
