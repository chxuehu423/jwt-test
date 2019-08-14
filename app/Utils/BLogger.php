<?php
/**
 * Description:记录日志处理类
 * User: lijun
 * Date: 2017/6/7
 * Time: 11:30
 */

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class BLogger
{
    // 所有的LOG都要求在这里注册

    /**
     * 记录sql日志
     * @param $msg
     * @param array $content
     */
    public static function writeSqlLog($msg,$content=[]){
        $msg = str_replace(PHP_EOL,"",$msg);
        Log::channel('sql')->info($msg,$content);
    }


    /**
     * 写日志
     * @param $msg
     * @param array $content
     */
    public static function writeInfoLog($msg,$content=[]){
        $msg = str_replace(PHP_EOL,"",$msg);
        Log::channel('info')->info($msg,$content);
    }

    /**
     * 写错误日志
     * @param $msg
     * @param array $content
     */
    public static function writeErrorLog($msg,$content=[]){
        $msg = str_replace(PHP_EOL,"",$msg);
        //MailSend::sendErrorMsg($msg."\r\n".print_r($content,true));
        Log::channel('error')->info($msg,$content);
    }

}