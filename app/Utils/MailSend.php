<?php
/**
 * Created by PhpStorm.
 * User: weimin.xu
 * Date: 2019/1/8
 * Time: 15:07
 */

namespace App\Utils;


use Illuminate\Support\Facades\Mail;

class MailSend {


    /**
     * 发送错误邮箱
     * @param $message
     */
    public static function sendErrorMsg($message){
        $head_array = ['USER','HOME','HTTP_COOKIE','HTTP_ACCEPT_LANGUAGE','HTTP_ACCEPT_ENCODING',
            'HTTP_ACCEPT','HTTP_UPGRADE_INSECURE_REQUESTS','HTTP_USER_AGENT','HTTP_CACHE_CONTROL',
            'HTTP_CONNECTION','HTTP_X_FORWARDED_FOR','HTTP_HOST','REDIRECT_STATUS','LOCAL_ADDR',
            'SERVER_NAME','SERVER_PORT','SERVER_ADDR','REMOTE_PORT','REMOTE_ADDR','SERVER_SOFTWARE','GATEWAY_INTERFACE',
            'REQUEST_SCHEME','SERVER_PROTOCOL','DOCUMENT_ROOT','DOCUMENT_URI','REQUEST_URI','SCRIPT_NAME','CONTENT_LENGTH','CONTENT_TYPE',
            'REQUEST_METHOD','QUERY_STRING','SCRIPT_FILENAME','FCGI_ROLE','PHP_SELF','REQUEST_TIME_FLOAT','REQUEST_TIME'
        ];
        $result = [];
        $request = app()->make('request');
        //信息太多需要过滤
        foreach ($request->server as $key=>$item) {
            if(in_array($key,$head_array)){
                $result[$key] = $item;
            }
        }
        $message =print_r($result,true)."\r\n".$message;
        Mail::raw($message,function ($msg){
            $msg->subject(Common::getEnvName().'异常');
            $msg->to(config("mail.error_list"));
        });
    }

    public static function sendSmsErrorMsg($subject,$message){
        Mail::raw($message,function ($msg) use($subject){
            $msg->subject(Common::getEnvName().$subject,'异常');
            $msg->to(config("mail.error_list"));
        });
    }
}