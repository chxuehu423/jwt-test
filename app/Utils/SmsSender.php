<?php
/**
 * Created by PhpStorm.
 * User: weimin.xu
 * Date: 2019/3/14
 * Time: 下午1:35
 */

namespace App\Utils;

use App\Models\SmsRecord;
use App\Models\SmsTemplate;
use Illuminate\Support\Arr;


class SmsSender
{


    //接口返回错误码
    const RESPONSE_CODE = [
        '00' => "发送成功",
        '1'  => '参数不完整，请检查所带的参数名是否都正确',
        '2'  => '鉴权失败，一般是用户名密码不对',
        '3'  => '号码数量超出 50 条',
        '4'  => '发送失败',
        '5'  => '余额不足',
        '6'  => '发送内容含屏蔽词',
        '7'  => '短信内容超出 350 个字',
        '72'  => '内容被审核员屏蔽',
        '8:OverLimit!'  => '号码列表中没有合法的手机号码或手机号为黑名单或验证码类每小时超过限制条数',
        '9'  => '夜间管理，不允许一次提交超过 20 个号码',
        '10'  => '{txt}不应当出现在提交数据中，请修改[模板类账号](适用于 模板类帐户)',
        '11'   => '模板匹配成功[模板类必审、免审账号](适用于模板类帐户)',
        '12'   => '未匹配到合适模板，已提交至审核员审核[模板类必审账号(]',
        '13'   => '未匹配到合适模板，无法下发',
        '14'   => '该账户没有模板[模板类账号]',
        '15'   => '操作失败[模板类账号]',
        '16'   => '模板编号格式错误',
        '02'   => '手机号码黑名单',
        '81'   => '手机号码错误，请检查手机号是否正确',
        'ERR'   => 'IP 验证未通过，请联系管理员增加鉴权 IP',
        '005'   => '余额不足',
        '0072'   => '内容被审核员屏蔽',
        '0002'   => '模板账户手机黑名单',
        '0081'   => '手机号码错误，请检查手机号是否正确',
    ];

    /**
     * @param $templateName 模版名称
     * @param $mobile       手机号
     * @param $params       替换的变量集合
     * @param string $countryCode   国家代码
     * @param int $mid      模版
     * @param string $content_msg  没有模版直接发送内容
     * @return array
     */
    private static function send($templateName,$mobile,$params,$countryCode = '86',$mid = 0,$content_msg='') {

        if($templateName){
            $content = SmsTemplate::getSmsContent($templateName,$params);
        }else{
            $content = $content_msg;
        }
        if(empty($content) || empty($mid)){
            throw new \Exception("发送短信内容为空");
        }

        $request_id = uniqid('sms');
        $record = new SmsRecord();
        $record->country = $countryCode;
        $record->mobile = $mobile;
        $record->content = $content;
        $record->request_id = $request_id;
        $record->updated_at = time();
        $record->created_at = time();
        $record->save();
        if(env('SMS_TEST',0) == 1){
            return true;
        }
        $host = config('gos.sms.host');
        $data['username'] = config('gos.sms.user_name');
        $data['password'] = md5(config('gos.sms.pwd'));
        $data['phone'] = $mobile;
        $data['epid'] = config('gos.sms.epid');

        if(empty($host) || empty($data['username']) || empty($data['password']) || empty($data['epid'])){
            throw new \Exception('请配置发送短信账号');
        }

        $data['linkid'] = $request_id;
        $data['message'] = self::writeutf8($content);
        $data['mid'] = $mid;
        $url = $host."?".http_build_query($data);
        $response = self::curlGet($url);
        $method_log['mobile'] = $mobile;
        $method_log['url'] = $url;
        $method_log['response'] = $response;
        $method_log['response_text'] = Arr::get(self::RESPONSE_CODE,$response,"not kown");
        BLogger::writeInfoLog("send sms ",$method_log);
        if($response == "00"){
            $record->update(['state' => 1]);
            return [
                'code' => 1000,
                'msg' => 'ok'
            ];
        }elseif(in_array($response,['6','7','81','02'])){
            return [
                'code' => 1001,
                'msg' => Arr::get(self::RESPONSE_CODE,$response,"not kown"),
            ];
        }else{
            return [
                'code' => 1001,
                'msg' => '发送失败'
            ];
        }
    }

    public static function writeutf8($source) {
        try{
            return(iconv('UTF-8','GBK//IGNORE',$source));
        }
        catch(Exception $e){
            return $source;
        }
    }

    /**
     * 发短信demo
     * @return mixed
     */
    private static function curlGet($URL){
        $ch = curl_init($URL) ;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ;
        $output = curl_exec($ch) ;
        curl_close($ch) ;
        return $output;
    }

    /**
     * 提货后发送短信
     * @param $mobile
     * @param $paramss
     */
    public static function sendTakeDeliveryOfGoodsAfter( $mobile,$params ) {
        $mid = 13209;
        return self::send('Take_delivery_of_goods_after',$mobile,$params,86,$mid);
    }

    /**
     * 购买提货卡
     */
    public static function sendBuyCard($mobile='',$order_sn,$pay_amount=0,$gos_amount=0,$platform=""){
//        您购买的商品提货卡订单号{order_sn} 支付{amount},订单已交易成功。
        if(empty($mobile) || (empty($pay_amount) && empty($gos_amount))){
            return false;
        }
        $mid = 13865;
        $gos_amount = round($gos_amount,4);
        $params['{order_sn}'] = $order_sn;
        $params['{platform}'] = $platform;
        if($pay_amount > 0 && $gos_amount > 0 ){
            $params['{amount}'] = $pay_amount."元+".$gos_amount."GOS";
        }elseif($pay_amount > 0 && $gos_amount == 0){
            $params['{amount}'] = $pay_amount.'元';
        }elseif($pay_amount == 0 && $gos_amount > 0){
            $params['{amount}'] = $gos_amount.'GOS';
        }
        return self::send('send_buy_card',$mobile,$params,86,$mid);
    }

    /**
     * 提货卡出售成功
     * @param $mobile
     * @param $order_sn
     * @param int $pay_amount
     * @param int $gos_amount
     * @return array
     * @throws \Exception
     */
    public static function sendSellCard($mobile,$order_sn,$pay_amount=0,$gos_amount=0,$platform=""){
//        您托管的商品提货卡订单号{order_sn}收入{amount},订单已交易成功
        $mid = 13866;
        $params['{order_sn}'] = $order_sn;
        $gos_amount = round($gos_amount,4);
        $params['{platform}'] = $platform;
        if($pay_amount > 0 && $gos_amount > 0 ){
            $params['{amount}'] = $pay_amount."元+".$gos_amount."GOS";
        }elseif($pay_amount > 0 && $gos_amount == 0){
            $params['{amount}'] = $pay_amount.'元';
        }elseif($pay_amount == 0 && $gos_amount > 0){
            $params['{amount}'] = $gos_amount.'GOS';
        }
        return self::send('send_sell_card',$mobile,$params,86,$mid);
    }


    /**
     * 橱窗出租成功
     * @param $mobile
     * @param $win_sn
     * @return array
     * @throws \Exception
     */
    public static function sendLeaseShopWindow($mobile,$count){
//        您的橱窗{win_sn}已成功出租，详情请到国客商城查看 13867
//        您的橱窗刚刚成功出租XX个，详情请到国客商城查看。
        $mid = 13928;
//        $params['{win_sn}'] = $win_sn;
        $params['{num}'] = $count;
        return self::send('send_lease_shopWindow',$mobile,$params,86,$mid);
    }

    /**
     * 租赁橱窗成功发短信
     */
    public static function sendRentShopWindow($mobile,$num){
        $mid = 13929;
        $params['{num}'] = $num;
        return self::send('send_rent_shopwindows',$mobile,$params,86,$mid);
    }

    /**
     * * 转售提醒通知
     * @param $mobile
     * @param $params
     * @param $mid
     * @return array
     * @throws \Exception
     */
    public static function sendShelfNotice($mobile, $params,$mid)
    {
        return self::send('send_shelf_notice', $mobile, $params, 86, $mid);
    }
}
