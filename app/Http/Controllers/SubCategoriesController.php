<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subcategory;

class SubCategoriesController extends Controller
{
    public function setSubCategories(Request $request)
    {
        if (!$request->name) {
            return response()->json([
                'status'=>'error',
                'message'=>'Subcategory name is required'
            ],403);
        }

        if (!$request->category_id) {
            return response()->json([
                'status'=>'error',
                'message'=>'Category is required'
            ],403);
        }

        $subcategories = SubCategory::where('name', $request->name)->where('category_id', $request->category_id)->get();

        if($subcategories) {
            return response()->json([
                'status'=>'error',
                'message'=>'Subcategory already exists'
            ],409);
        }

        $newSubCategories = SubCategory::create([
            'name'=>$request->name,
            'category_id'=>$request->category_id,
        ]);

        if($newSubCategories) {
            return response()->json([
                'status'=>'success',
                'message'=>'Subcategory created successfully'
            ],200);
        } else {
            return response()->json([
                'status'=>'error',
                'message'=>'Failed to create subcategory'
            ],500);
        }
    }

    public function getSubCategories(Request $request)
    {
        $subcategories = SubCategory::all();

        return response()->json([
            'status'=>'success',
            'subcategories'=>$subcategories
        ],200);
    }

    public function putSubCategories(Request $request)
    {
        $subcategories = SubCategory::find($request->id);

        if(!$subcategories) {
            return response()->json([
                'status'=>'error',
                'message'=>'Subcategory not found'
            ],404);
        }

        $subcategories->name = $request->name;
        $subcategories->category_id = $request->category_id;

        if($subcategories->save()) {
            return response()->json([
                'status'=>'success',
                'message'=>'Subcategory updated successfully'
            ],200);
        } else {
            return response()->json([
                'status'=>'error',
                'message'=>'Failed to update subcategory'
            ],500);
        }
    }

    public function deleteSubCategories(Request $request)
    {
        $subcategories = SubCategory::find($request->id);

        if(!$subcategories) {
            return response()->json([
                'status'=>'error',
                'message'=>'Subcategory not found'
            ],404);
        }

        if($subcategories->delete()) {
            return response()->json([
                'status'=>'success',
                'message'=>'Subcategory deleted successfully'
            ],200);
        } else {
            return response()->json([
                'status'=>'error',
                'message'=>'Failed to delete subcategory'
            ],500);
        }
    }
}
