<?php

namespace App\Utils;

/**
 * Created by PhpStorm.
 * User: zhengyibin
 * Date: 6/12/17
 * Time: 11:40 AM
 */

use Illuminate\Http\JsonResponse;

class JsonBase
{
    public static function renderJsonWithSuccess($data = [], $bizMsg = 'ok', $returnStatus = 0, $bizAction = 0, $status = 200)
    {
        return self::renderJsonBase($data, $bizMsg, $returnStatus, $bizAction, $status);
    }

    public static function renderJsonWithFail($bizMsg , $data = [], $bizAction = 1, $returnStatus = 1002, $status = 200)
    {
        return self::renderJsonBase($data, $bizMsg, $returnStatus, $bizAction, $status);
    }

    public static function renderJsonBase($data, $bizMsg, $returnStatus, $bizAction, $status)
    {
        //dd($data);
        return new JsonResponse([
            'biz_action'    => $bizAction,
            'biz_msg'       => $bizMsg,
            'return_status' => $returnStatus,
            'server_time'   => time(),
            'data'          => (object)$data
        ],
        $status);
    }
}