<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

// --- الإضافات الضرورية لحل مشكلة HTTPS (مطلوبة) ---
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL; 
// --------------------------------------------------


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1. حل مشكلة الوكيل (Proxy) - الثابت الأكثر توافقاً
        // هذا يضمن أن Laravel سيكتشف أن الاتصال الأصلي كان https
        Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_PROTO);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 2. إجبار بروتوكول HTTPS بشكل مطلق (الملاذ الأخير الأقوى)
        // تمت إزالة شرط البيئة لضمان عمل HTTPS في جميع الظروف
        URL::forceScheme('https'); 
        
        JsonResource::withoutWrapping();

        // مشاركة المستخدم الحالي مع Inertia (الكود الأصلي الخاص بك)
        Inertia::share([
            'auth' => function () {
                $user = Auth::user();
                if ($user) {
                    $user->load('roles', 'permissions');

                    // تحويل الأدوار والصلاحيات إلى مصفوفة أسماء
                    $user->roles = $user->roles->pluck('name')->toArray();
                    $user->permissions = $user->permissions->pluck('name')->toArray();
                }
                return $user;
            },
        ]);
    }
}
