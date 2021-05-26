<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    // public const HOME = '/home';

    /**
    * The controller namespace for the application.
    *
    * When present, controller route declarations will automatically be prefixed with this namespace.
    *
    * @var string|null
    */
    protected $namespace = 'App\Http\Controllers';
    
    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        // $this->configureRateLimiting();

        // $this->routes(function () {
        //     Route::prefix('api')
        //         ->middleware('api')
        //         ->namespace($this->namespace)
        //         ->group(base_path('routes/api.php'));

        //     Route::middleware('web')
        //         ->namespace($this->namespace)
        //         ->group(base_path('routes/web.php'));
        // });
        parent::boot();
    }

    public function map()
    {
        $this->mapAdminApiRoutes(); //後端請求路由
    }

    protected function mapAdminApiRoutes()
    {
        $domain = config('app.subAdminApi').'.'.config('app.domain');

        //經營者操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/userOperator.php'));
        //系統使用者操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/userSystem.php'));
        //使用者操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/user.php'));
        //子帳號操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/userSub.php'));
        // 助理操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/userAssistant.php'));
        // 服務提供者操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/userDesigner.php'));
        //群組操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/group.php'));
        //預設權限操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/permission.php'));
        // 圖片操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/image.php'));
        // 分類操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/category.php'));
        // 服務操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/storeService.php'));
        // 標籤操作
        Route::domain($domain)
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi/tag.php'));
        // 會員操作
        Route::domain($domain)
        ->namespace($this->namespace)
        ->group(base_path('routes/adminApi/userMember.php'));
        // 點數訂單操作
        Route::domain($domain)
        ->namespace($this->namespace)
        ->group(base_path('routes/adminApi/pointOrder.php'));
        // 聊天室操作
        Route::domain($domain)
        ->namespace($this->namespace)
        ->group(base_path('routes/adminApi/channel.php'));
        // 簡訊商操作
        Route::domain($domain)
        ->namespace($this->namespace)
        ->group(base_path('routes/adminApi/sms.php'));
    }

    
    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    // protected function configureRateLimiting()
    // {
    //     RateLimiter::for('api', function (Request $request) {
    //         return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    //     });
    // }
}
