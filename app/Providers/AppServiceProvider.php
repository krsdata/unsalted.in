<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;
use Route;
use Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $controllers = [];

        try{
            $main_menu = \DB::table('menus')->where('parent_id',0)
                    ->get()
                    ->transform(function($item,$key){
                        $item->sub_menu = \DB::table('menus')
                                ->where('parent_id',$item->id)
                                ->get();
                        return $item;

                    });
            $settings = \DB::table('settings')->pluck('field_value','field_key')->toArray();        
            $setting  = (object)$settings;

        }catch(\Illuminate\Database\QueryException $e){
            $main_menu = (object)[];
        } 
        
        View::share('main_menu',$main_menu??null); 
        View::share('setting',$setting??null);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
