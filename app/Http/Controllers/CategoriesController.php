<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function setCategories(Request $request)
    {
        // Validasi jika nama kategori tidak ada
        if (!$request->name) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category name is required'
            ], 403);
        }

        // Pengecekan apakah kategori dengan nama yang sama sudah ada
        $categories = Category::where('name', $request->name)->first();

        if ($categories) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category already exists'
            ], 409);
        }

        // Menambahkan kategori baru
        $newCategories = Category::create([
            'name' => $request->name,
        ]);

        // Menangani hasil penyimpanan
        if ($newCategories) {
            return response()->json([
                'status' => 'success',
                'message' => 'Category created successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category'
            ], 500);
        }
    }


    public function getCategories()
    {
        $categories = Category::with('subcategories')->get();

        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ], 200);
    }

    public function putCategories(Request $request)
    {
        $categories = Category::find($request->id);

        if (!$categories) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        $categories->name = $request->name;

        if ($categories->save()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update category'
            ], 500);
        }
    }

    public function deleteCategories(Request $request)
    {
        $categories = Category::find($request->id);

        if (!$categories) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }

        if ($categories->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete category'
            ], 500);
        }
    }
}
