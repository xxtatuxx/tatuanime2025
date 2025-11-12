<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
// إضافة الاستخدامات الضرورية
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL; 


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. الحل الأول: الثقة برأس PROTO من الوكيل
        Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_PROTO);
    }

    public function boot(): void
    {
        // 2. الحل الثاني: الإجبار المطلق على HTTPS
        // (تمت إزالة شرط الإنتاج لضمان التطبيق الفوري)
        URL::forceScheme('https'); 
        
        JsonResource::withoutWrapping();

        Inertia::share([
            'auth' => function () {
                $user = Auth::user();
                if ($user) {
                    $user->load('roles', 'permissions');
                    $user->roles = $user->roles->pluck('name')->toArray();
                    $user->permissions = $user->permissions->pluck('name')->toArray();
                }
                return $user;
            },
        ]);
    }
}
