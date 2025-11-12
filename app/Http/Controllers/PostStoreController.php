<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostStoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
 public function __invoke(Request $request)
{
    $data = $request->validate([
        'title' => 'required',
        'content' => 'required',
        'image' => 'required|image',
    ]);

    $data['slug'] = str($data['title'])->slug();

    if ($request->hasFile('image')) {
        $file = $request->file('image');

        // جلب الاسم الأصلي للملف (بدون تغييرات)
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();

        // إعادة بناء الاسم بنفس اسم الملف الأصلي + الامتداد
        $filename = $originalName . '.' . $extension;

        // تخزين الصورة بنفس الاسم الأصلي في مجلد animes داخل القرص public
        $data['image'] = $file->storeAs('animes', $filename, 'public');
    }

    $request->user()->posts()->create($data);

    return to_route('posts.index')->with('success', 'Post created successfully');
}

}
