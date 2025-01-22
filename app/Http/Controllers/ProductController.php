<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function upload(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);

        // Handle file upload
        $file = $request->file('file');
        $path = $file->storeAs('public/products', time() . '.' . $file->getClientOriginalExtension());

        // Create the product record
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
        // Fetch products with their relations
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

        // Generate a public URL for the Excel file
        $product->excel_file_url = asset('storage/' . str_replace('public/', '', $product->excel_file));

        return response()->json([
            'status' => 'success',
            'products' => $product,
        ]);
    }

    public function getExcelUrl($id)
    {
        // Cari produk berdasarkan ID
        $product = Product::find($id);

        // Jika produk ditemukan
        if ($product) {
            // Buat URL publik untuk file Excel
            $fileUrl = asset('storage/' . str_replace('public/', '', $product->excel_file));

            return response()->json([
                'status' => 'success',
                'url' => $fileUrl,
            ]);
        }

        // Jika produk tidak ditemukan
        return response()->json([
            'status' => 'error',
            'message' => 'Product not found'
        ], 404);
    }
}