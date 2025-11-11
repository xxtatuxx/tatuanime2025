<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

// --- الإضافات المطلوبة ---
use Illuminate\Http\Request; // لإصلاح الوكيل (Proxy)
use Illuminate\Support\Facades\URL; // لإصلاح forceScheme
// -------------------------


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

public function register(): void
{
    // --- هذا هو التعديل النهائي الأكثر أماناً لحل مشكلة HTTPS ---
    Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_PROTO);
    // -----------------------------------------------------------
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // هذا السطر كان صحيحاً، وأضفنا له 'use' في الأعلى
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        JsonResource::withoutWrapping();

        // مشاركة المستخدم الحالي مع Inertia (الكود الخاص بك سليم)
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
