<?php

use App\Http\Controllers\PostDestroyController;
use App\Http\Controllers\PostEditController;
use App\Http\Controllers\PostIndexController;
use App\Http\Controllers\PostStoreController;
use App\Http\Controllers\PostUpdateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebSetting\TypeController;
use App\Http\Controllers\WebSetting\SeasonController;
use App\Http\Controllers\WebSetting\StudioController;
use App\Http\Controllers\WebSetting\LanguageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EpisodeController;
use Inertia\Inertia;

// Middleware Classes
use App\Http\Middleware\CheckUserPermission;
use App\Http\Middleware\CheckRolePermission;
use App\Http\Middleware\CheckPermissionPermission;

// ========================
// الصفحة الرئيسية
// ========================
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::get('/ar/home', [HomeController::class, 'index'])->name('home');

// ========================
// لوحة التحكم
// ========================
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    // عرض جميع البوستات
    Route::get('posts', PostIndexController::class)->name('posts.index')->middleware('can:page-post-or-admin');

    // إنشاء بوست
    Route::inertia('posts/create', 'posts/Create')
        ->name('posts.create')
        ->middleware('can:create-post-or-admin');

    Route::post('posts', PostStoreController::class)
        ->name('posts.store')
        ->middleware('can:create-post-or-admin');

    // تعديل بوست
    Route::get('posts/{post}/edit', PostEditController::class)
        ->name('posts.edit')
        ->middleware('can:update-post-or-admin,post');

    Route::put('posts/{post}', PostUpdateController::class)
        ->name('posts.update')
        ->middleware('can:update-post-or-admin,post');

    // حذف بوست
    Route::delete('posts/{post}', PostDestroyController::class)
        ->name('posts.destroy')
        ->middleware('can:delete-post-or-admin,post');
});


Route::prefix('web-settings')->name('web-settings.')->group(function () {
    // عرض كل الأنواع
    Route::get('types', [TypeController::class, 'index'])->name('types.index')->middleware('can:page-type-or-admin,type');

    // صفحة إنشاء نوع جديد
    Route::get('types/create', [TypeController::class, 'create'])->name('types.create')->middleware('can:create-type-or-admin');

    // حفظ نوع جديد
    Route::post('types', [TypeController::class, 'store'])->name('types.store')->middleware('can:create-type-or-admin');

    // صفحة تعديل نوع موجود
    Route::get('types/{type}/edit', [TypeController::class, 'edit'])->name('types.edit')->middleware('can:update-type-or-admin');

    // تحديث نوع موجود
    Route::put('types/{type}', [TypeController::class, 'update'])->name('types.update')->middleware('can:update-type-or-admin');

    // حذف نوع
    Route::delete('types/{type}', [TypeController::class, 'destroy'])->name('types.destroy')->middleware('can:delete-type-or-admin');

    // ========================
    // السيزونات Seasons
    // ========================
    // عرض كل السيزونات
    Route::get('seasons', [SeasonController::class, 'index'])->name('seasons.index')->middleware('can:page-season-or-admin');

    // صفحة إنشاء سيزون جديد
    Route::get('seasons/create', [SeasonController::class, 'create'])->name('seasons.create')->middleware('can:create-season-or-admin');

    // حفظ سيزون جديد
    Route::post('seasons', [SeasonController::class, 'store'])->name('seasons.store')->middleware('can:create-season-or-admin');

    // صفحة تعديل سيزون موجود
    Route::get('seasons/{season}/edit', [SeasonController::class, 'edit'])->name('seasons.edit')->middleware('can:update-season-or-admin');

    // تحديث سيزون موجود
    Route::put('seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update')->middleware('can:update-season-or-admin');

    // حذف سيزون
    Route::delete('seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy')->middleware('can:delete-season-or-admin');

    // ========================
    // الاستوديوهات Studios
    // ========================
    // عرض كل الاستوديوهات
    Route::get('studios', [StudioController::class, 'index'])->name('studios.index')->middleware('can:page-studio-or-admin');

    // صفحة إنشاء استوديو جديد
    Route::get('studios/create', [StudioController::class, 'create'])->name('studios.create')->middleware('can:create-studio-or-admin');

    // حفظ استوديو جديد
    Route::post('studios', [StudioController::class, 'store'])->name('studios.store')->middleware('can:create-studio-or-admin');

    // صفحة تعديل استوديو موجود
    Route::get('studios/{studio}/edit', [StudioController::class, 'edit'])->name('studios.edit')->middleware('can:update-studio-or-admin');

    // تحديث استوديو موجود
    Route::put('studios/{studio}', [StudioController::class, 'update'])->name('studios.update')->middleware('can:update-studio-or-admin');

    // حذف استوديو
    Route::delete('studios/{studio}', [StudioController::class, 'destroy'])->name('studios.destroy')->middleware('can:delete-studio-or-admin');

    // ========================
    // اللغات Languages
    // ========================
    // عرض كل اللغات
    Route::get('languages', [LanguageController::class, 'index'])->name('languages.index')->middleware('can:page-language-or-admin');

    // صفحة إنشاء لغة جديدة
    Route::get('languages/create', [LanguageController::class, 'create'])->name('languages.create')->middleware('can:create-language-or-admin');

    // حفظ لغة جديدة
    Route::post('languages', [LanguageController::class, 'store'])->name('languages.store')->middleware('can:create-language-or-admin');

    // صفحة تعديل لغة موجودة
    Route::get('languages/{language}/edit', [LanguageController::class, 'edit'])->name('languages.edit')->middleware('can:update-language-or-admin');

    // تحديث لغة موجودة
    Route::put('languages/{language}', [LanguageController::class, 'update'])->name('languages.update')->middleware('can:update-language-or-admin');

    // حذف لغة
    Route::delete('languages/{language}', [LanguageController::class, 'destroy'])->name('languages.destroy')->middleware('can:delete-language-or-admin');
});

// ========================
// المستخدمون Users (محمي حسب الأدوار/الصلاحيات)
// ========================
Route::middleware(['auth', 'verified', CheckUserPermission::class])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// ========================
// الأدوار Roles (محمي حسب صلاحية admin أو manage-roles)
// ========================
Route::middleware(['auth', 'verified', CheckRolePermission::class])->group(function () {
    Route::resource('roles', RoleController::class);
});

// ========================
// الصلاحيات Permissions (محمي حسب صلاحية admin أو manage-permissions)
// ========================
Route::middleware(['auth', 'verified', CheckPermissionPermission::class])->group(function () {
    Route::resource('permissions', PermissionController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('animes', [AnimeController::class, 'index'])->name('animes.index');
    Route::get('animes/create', [AnimeController::class, 'create'])->name('animes.create');
    Route::post('animes', [AnimeController::class, 'store'])->name('animes.store');
    Route::get('animes/{anime}', [AnimeController::class, 'show'])->name('animes.show');
    Route::get('animes/{anime}/edit', [AnimeController::class, 'edit'])->name('animes.edit');
    Route::put('animes/{anime}', [AnimeController::class, 'update'])->name('animes.update');
    Route::delete('animes/{anime}', [AnimeController::class, 'destroy'])->name('animes.destroy');

    // ========================
    // الحلقات Episodes
    // ========================
    Route::get('episodes', [EpisodeController::class, 'index'])->name('episodes.index');
    Route::get('episodes/create', [EpisodeController::class, 'create'])->name('episodes.create');
    Route::post('episodes', [EpisodeController::class, 'store'])->name('episodes.store');
    Route::get('episodes/{episode}', [EpisodeController::class, 'show'])->name('episodes.show');
    Route::get('episodes/{episode}/edit', [EpisodeController::class, 'edit'])->name('episodes.edit');
    Route::put('episodes/{episode}', [EpisodeController::class, 'update'])->name('episodes.update');
    Route::delete('episodes/{episode}', [EpisodeController::class, 'destroy'])->name('episodes.destroy');
});





// ========================
// تحديث البروفايل
// ========================
Route::patch('/settings/profile', [ProfileController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('profile.update');

Route::middleware(['auth', 'verified'])->group(function () {

    // عرض جميع التصنيفات
    Route::get('categories', [CategoryController::class, 'index'])
        ->name('categories.index')
        ->middleware('can:page-category-or-admin');

    // إنشاء تصنيف
    Route::inertia('categories/create', 'Categories/Create')
        ->name('categories.create')
        ->middleware('can:create-category-or-admin');

    Route::post('categories', [CategoryController::class, 'store'])
        ->name('categories.store')
        ->middleware('can:create-category-or-admin');

    // تعديل تصنيف
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])
        ->name('categories.edit')
        ->middleware('can:update-category-or-admin,category');

    Route::put('categories/{category}', [CategoryController::class, 'update'])
        ->name('categories.update')
        ->middleware('can:update-category-or-admin,category');

    // حذف تصنيف
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
        ->name('categories.destroy')
        ->middleware('can:delete-category-or-admin,category');
});// ========================

// ========================
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
