<?php

namespace App\Providers;

use App\Utils\BLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 记录sql日志
        DB::listen(function ($query) {
            $sql = $query->sql;
            foreach ($query->bindings as $replace){
                $value = is_numeric($replace) ? $replace : "'".$replace."'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            BLogger::writeSqlLog("sql:",[
                'sql' => $sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });
    }
}
