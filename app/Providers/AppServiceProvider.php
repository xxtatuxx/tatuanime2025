<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        // إجبار Laravel على استخدام HTTPS في بيئة الإنتاج أو على الاستضافات مثل Railway
        if (request()->header('x-forwarded-proto') === 'https' || app()->environment('production')) {
            URL::forceScheme('https');
        }

        // مشاركة المستخدم الحالي مع Inertia
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
