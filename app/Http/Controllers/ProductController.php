<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);


        $file = $request->file('file');
        // $path = $file->storeAs('public/products', time() . '.' . $file->getClientOriginalExtension());


        // Product::create([
        //     'name' => $request->name,
        //     'description' => $request->description,
        //     'price' => $request->price,
        //     'category_id' => $request->category_id,
        //     'subcategory_id' => $request->subcategory_id,
        //     'excel_file' => $path,
        // ]);

        // return response()->json(['message' => 'Product uploaded successfully'], 200);

        try {
            Excel::import(new ProductsImport(), $file);
            return response()->json([
                'status' => 'success',
                'message' => 'Product uploaded successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function getProduct(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);

        $products = Product::where('category_id', $validated['category_id'])
            ->where('subcategory_id', $validated['subcategory_id'])
            ->get();

        return response()->json($products);
    }
}
