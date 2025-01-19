<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
        $path = $file->storeAs('public/products', time() . '.' . $file->getClientOriginalExtension());


        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'excel_file' => $path,
        ]);

        return response()->json(['message' => 'Product uploaded successfully'], 200);
    }


    public function getProduct()
    {
        $products = Product::with('category', 'subcategory')->get();

        return response()->json([
            'status' => 'success',
            'products' => $products
        ]);
    }

    public function getProductById($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        // Tambahkan URL lengkap untuk file Excel
        $product->excel_file_url = url('storage/' . str_replace('public/', '', $product->excel_file));

        return response()->json([
            'status' => 'success',
            'products' => $product
        ]);
    }
}
