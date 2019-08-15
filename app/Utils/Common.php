<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 2018/1/8
 * Time: 15:20
 */

namespace App\Utils;


use Illuminate\Support\Collection;

class Common
{

    public static function getEnvName()
    {
        $name = "";
        switch (config("app.env")) {
            case "local":
                $name = "本地开发";
                break;
            case "test":
                $name = "测试环境";
                break;
            case "production":
                $name = "生产环境";
                break;
            default:
                break;
        }
        return env("APP_NAME") . $name;
    }

    /**
     * 过滤任何非主流字符
     * @param type $strParam
     * @return type
     */
    public static function replaceSpecialChar($strParam)
    {
        $regex = "/[^\x{4e00}-\x{9fa5}0-9a-zA-Z|\【|\】|\-|\,|\.|\，|\:|\。\！\→\‘\’\/\?\=\#]/iu";
        return preg_replace($regex, "", $strParam);
    }

    public static function mergeArray($array1, $array2)
    {
        return Collection::make($array1)->merge($array2);
    }
}