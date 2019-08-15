<?php
/**
 * Created by PhpStorm.
 * User: xuweimin
 * Date: 2019/3/12
 * Time: 下午1:20
 */

namespace App\Utils;


class CommonString
{

    public static function createShopToken()
    {
        $charid = md5(uniqid(mt_rand(), true));
        $uuid = substr($charid, 0, 4);
        return strtoupper($uuid);
    }

//    public static function uniqueKey($prefix=null, $level = 'short')
//    {
//        switch($level)
//        {
//            case 'middle':
//                return md5(date('YmdHis', time()).(Encryption::hex(3)));
//                break;
//            case 'short':
//                return uniqid($prefix);
//                break;
//            default:
//                return uniqid($prefix);
//                break;
//        }
//    }

    /*
     * 随机生成 $length 位长的字符串，只包含数字，不足的部分会用0补充
     * eg: CommonString::randNumStr(4) # => 0323
     */
    public static function randNumStr($length)
    {
        return sprintf('%0'.$length.'d', rand(0, pow(10, $length) - 1));
    }

    public static function createRandomStr($length){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $strlen = 62;
        while($length > $strlen){
            $str .= $str;
            $strlen += 62;
        }
        $str = str_shuffle($str);
        return substr($str,0,$length);
    }


    public static function getCountryCode($country = 'MY')
    {
        switch ($country) {
            case 'MY':
                $code = '60';
                break;
            case 'CN':
                $code = '86';
                break;
            default:
                $code = '60';
        }
        return $code;
    }

    /**
     * 验证是否是假的手机号码
     * @param $mobile
     * @return bool
     */
    public static function checkCounterfeitMobile($mobile){
        $str = substr($mobile, 0, 1);
        if ($str=='m'){
            return true;
        }else {
            return false;
        }
    }

    public static function secToTime($seconds) {
        if(intval($seconds) < 60)
            $result = "00:00:".sprintf("%02d", intval($seconds % 60));
        if(intval($seconds) >= 60){
            $m =sprintf("%02d", intval($seconds / 60));
            $s =sprintf("%02d", intval($seconds % 60));
            if($s == 60){
                $s = sprintf("%02d", 0);
                ++$m;
            }
            $t = "00";
            if($m == 60){
                $m = sprintf("%02d", 0);
                ++$t;
            }
            if($t){
                $t  = sprintf("%02d",$t);
            }
            $result = $t.":".$m.":".$s;
        }
        if(intval($seconds) >= 60*60){
            $h= sprintf("%02d", intval($seconds / 3600));
            $m =sprintf("%02d", intval($seconds / 60) - $h * 60);
            $s =sprintf("%02d", intval($seconds % 60));
            if($s == 60){
                $s = sprintf("%02d", 0);
                ++$m;
            }
            if($m == 60){
                $m = sprintf("%02d", 0);
                ++$h;
            }
            if($h){
                $h  = sprintf("%02d", $h);
            }
            $result = $h.":".$m.":".$s;
        }
        return $result;
    }

    public static function secToHMTime($seconds) {
        return substr(self::secToTime($seconds), -5);
    }

    /**
     * 中英文混编字符串长度获取
     * 中文1字2长度，英文1字1长度
     * @param $str
     * @param string $charset
     * @return float
     */
    public static function getStrLengthTwo($str, $charset = 'utf-8') {
        if ($charset == 'utf-8')
            $str = iconv('utf-8', 'GBK//TRANSLIT//IGNORE', $str);
        $num = strlen($str);
        $cnNum = 0;
        for ($i = 0; $i < $num; $i++) {
            if (ord(substr($str, $i + 1, 1)) > 127) {
                $cnNum++;
                $i++;
            }
        }
        $enNum = $num - ($cnNum * 2);
        //$number = ($enNum / 2) + $cnNum;
        $number = $enNum + $cnNum * 2;
        return ceil($number);
    }

    /**
     * 生成订单号
     * @param $prefix
     * @return string
     */
    public static function createUniqidNum($prefix=''){
        $order_id_main = date('YmdHis') . rand(10000000,99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for($i=0; $i<$order_id_len; $i++){
            $order_id_sum += (int)(substr($order_id_main,$i,1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
        return $prefix.$order_id;
    }

    /**
     * 生成15位的订单号
     * @return string 订单号
     */
    public static function create15Num($prefix=''){

        $year_code = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $date_code = ['0','1', '2', '3', '4', '5', '6', '7', '8', '9', 'A','C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'S','R', 'T', 'U', 'V', 'W', 'X', 'Y'];
        //一共15位订单号,同一秒内重复概率1/10000000,26年一次的循环
        $order_sn = $year_code[(intval(date('Y')) - 2010) % 26] . //年 1位
        strtoupper(dechex(date('m'))) . //月(16进制) 1位
        $date_code[intval(date('d'))] . //日 1位
        substr(time(), -5) . substr(microtime(), 2, 5) . //秒 5位 // 微秒 5位
        sprintf('%02d', rand(0, 99)); //  随机数 2位
        return $prefix.$order_sn;
    }


    /**
     *
     * @param $string
     * @return bool|string
     */
    public static function base58_encode($string){
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);
        if (is_string($string) === false) {
            return false;
        }
        if (strlen($string) === 0) {
            return '';
        }
        $bytes = array_values(unpack('C*', $string));
        $decimal = $bytes[0];
        for ($i = 1, $l = count($bytes); $i < $l; $i++) {
            $decimal = bcmul($decimal, 256);
            $decimal = bcadd($decimal, $bytes[$i]);
        }
        $output = '';
        while ($decimal >= $base) {
            $div = bcdiv($decimal, $base, 0);
            $mod = bcmod($decimal, $base);
            $output .= $alphabet[$mod];
            $decimal = $div;
        }
        if ($decimal > 0) {
            $output .= $alphabet[$decimal];
        }
        $output = strrev($output);
        foreach ($bytes as $byte) {
            if ($byte === 0) {
                $output = $alphabet[0] . $output;
                continue;
            }
            break;
        }
        return (string) $output;
    }


    public static function base58_decode($base58){
        $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        $base = strlen($alphabet);
        if (is_string($base58) === false) {
            return false;
        }
        if (strlen($base58) === 0) {
            return '';
        }
        $indexes = array_flip(str_split($alphabet));
        $chars = str_split($base58);
        foreach ($chars as $char) {
            if (isset($indexes[$char]) === false) {
                return false;
            }
        }
        $decimal = $indexes[$chars[0]];
        for ($i = 1, $l = count($chars); $i < $l; $i++) {
            $decimal = bcmul($decimal, $base);
            $decimal = bcadd($decimal, $indexes[$chars[$i]]);
        }
        $output = '';
        while ($decimal > 0) {
            $byte = bcmod($decimal, 256);
            $output = pack('C', $byte) . $output;
            $decimal = bcdiv($decimal, 256, 0);
        }
        foreach ($chars as $char) {
            if ($indexes[$char] === 0) {
                $output = "\x00" . $output;
                continue;
            }
            break;
        }
        return $output;
    }

    public static function isUrlHttp($url){
        $preg = "/^http(s)?:\\/\\/.+/";
        if(preg_match($preg,$url)){
            return true;
        }else{
            return false;
        }
    }
}