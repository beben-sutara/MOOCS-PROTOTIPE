<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    /**
     * Handle image upload for Editor.js (instructors & admins).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $path = $request->file('image')->store('module-images', 'public');

        return response()->json([
            'success' => 1,
            'file'    => ['url' => asset('storage/' . $path)],
        ]);
    }
}
