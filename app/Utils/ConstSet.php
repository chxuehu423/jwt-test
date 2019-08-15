<?php
/**
 * Created by PhpStorm.
 * User: weimin.xu
 * Date: 2019/05/13
 * Time: 下午2:56
 * 此处定义项目中的常量
 */

namespace App\Utils;


class ConstSet
{

    const TASK_MALL = "task";
    const WHOLESALE_MALL = "Wholesale";
    const RETAIL = "retail";

    //转售
    const RESALE = 'resale';
    //出售
    const SALE = 'sale';

    //现金支付
    const PAYMENT_MONEY = 1;
    //混合支付
    const PYMENT_MONEY_GOS = 2;
    //gos支付
    const PYMENT_GOS = 3;

}