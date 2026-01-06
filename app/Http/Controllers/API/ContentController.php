<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * GET: /api/content
     * Ambil semua konten
     */
    public function index(Request $request)
    {
        $groupId = $this->requestGroupId($request) ?? 'default';

        $contents = Content::forGroup($groupId)->get();

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $contents
        ], 200);
    }


    /**
     * GET: /api/content/category?category=xxx
     * Ambil konten berdasarkan kategori
     */
    public function byCategory(Request $request)
    {
        $category = $request->query('category');
        $groupId = $this->requestGroupId($request) ?? 'default';

        if (!$category) {
            return response()->json([
                'code' => 400,
                'status' => 'Bad Request',
                'message' => 'Category parameter is required'
            ], 400);
        }

        $contents = Content::forGroup($groupId)
            ->where('category', $category)
            ->get();

        if ($contents->isEmpty()) {
            return response()->json([
                'code' => 404,
                'status' => 'Not Found',
                'message' => "No contents found for category: {$category}"
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'status' => 'OK',
            'data' => $contents
        ], 200);
    }
}
