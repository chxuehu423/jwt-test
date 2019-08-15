<?php

namespace App\Http\Controllers;

use App\Utils\BLogger;
use App\Utils\JsonBase;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $currentUser;

    public $currentUserType;

    public $sessionId;

    public $currentMemcached;

    public $appType;

    public $appVersion;

    public $platform;

    public $requestInfo;

    public $locale;

    public function __construct(ServerRequestInterface $serverRequest) {
        //记录请求日志
        $app = resolve('app');
        $this->currentUser = $app->make('App\Models\User');
        //容错处理，当token过期，走RefreshToken中间件时，CurrentUserProvider无法获取用户信息，通过RefreshToken的刷新和单次允许登录，可以通过以下方法获取到用户信息
        if (!$this->currentUser){
            $this->currentUser = auth('api')->user();
            //dd($this->currentUser);
            BLogger::writeInfoLog('BaseController:'.json_encode($this->currentUser));
        }
        $this->_setRequestInfo($serverRequest);
    }

    public function renderJsonWithSuccess($data = [], $bizMsg = 'ok', $returnStatus = 0, $bizAction = 0, $status = 200)
    {
        $this->_saveResponseLog($data);
        //dd($data);
        return JsonBase::renderJsonBase($data, $bizMsg, $returnStatus, $bizAction, $status);
    }

    public function renderJsonWithFail($bizMsg , $data = [], $bizAction = 1, $returnStatus = 1002, $status = 200)
    {
        $this->_saveResponseLog($data);
        return JsonBase::renderJsonBase($data, $bizMsg, $returnStatus, $bizAction, $status);
    }

    private function _setRequestInfo(ServerRequestInterface $serverRequest)
    {
        $this->requestInfo = [
            'session_id' => $this->sessionId,
            'target' => $serverRequest->getRequestTarget(),
            'method' => $serverRequest->getMethod(),
            'body' => $serverRequest->getParsedBody(),
            'ip' => $this->getClientIp(),
            'params' => $serverRequest->getQueryParams(),
            'authenticate_token' => $serverRequest->getHeaderLine('X-Auth-Token'),
            'request_time' => microtime(true),
            'app_type' => $serverRequest->getHeaderLine('X-App-Type'),
            'locale' => strtolower($serverRequest->getHeaderLine('X-Locale'))
        ];
    }

    private function _saveResponseLog($data)
    {
        $responseTime = microtime(true);
        $this->requestInfo = array_merge($this->requestInfo, [
            'response_time' => $responseTime,
            'duration' => $responseTime - $this->requestInfo['request_time'],
            'data' => $data
        ]);
        if(!in_array($this->requestInfo['target'],['/api/address/region'])){
            BLogger::writeInfoLog("",$this->requestInfo);
        }
    }


    /**
     * 获取客户端IP
     * @return [type] [description]
     */
    public function getClientIp(){
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return($ip);
    }

    public function getOneClientIp() {
        $ip = $this->getClientIp();
        return(explode(", ", $ip)[count(explode(", ", $ip)) - 1]);
    }


    /**
     * 获取系统信息
     */
    public function getOs(){
        $user_agent = request()->server('HTTP_USER_AGENT');
        if (!empty($user_agent)) {
            $os = $user_agent;
            if (preg_match('/win/i', $os)) {
                $os = 'Windows';
            } else if (preg_match('/mac/i', $os)) {
                $os = 'MAC';
            } else if (preg_match('/linux/i', $os)) {
                $os = 'Linux';
            } else if (preg_match('/unix/i', $os)) {
                $os = 'Unix';
            } else if (preg_match('/bsd/i', $os)) {
                $os = 'BSD';
            } else {
                $os = 'Other';
            }
            return $os;
        } else {
            return 'unknow';
        }
    }

    /**
     * 获取浏览器信息
     */
    public function getBrowseInfo(){
        $user_agent = request()->server('HTTP_USER_AGENT');
        if (!empty($user_agent)) {
            if (preg_match('/MSIE/i', $user_agent)) {
                $br = 'MSIE';
            } else if (preg_match('/Firefox/i', $user_agent)) {
                $br = 'Firefox';
            } else if (preg_match('/Chrome/i', $user_agent)) {
                $br = 'Chrome';
            } else if (preg_match('/Safari/i', $user_agent)) {
                $br = 'Safari';
            } else if (preg_match('/Opera/i', $user_agent)) {
                $br = 'Opera';
            } else {
                $br = 'Other';
            }
            return $br;
        } else {
            return 'unknow';
        }
    }
}
