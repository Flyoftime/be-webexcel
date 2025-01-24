<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:50000',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);


        $file = $request->file('file');
        $path = $file->storeAs('public/products', time() . '.' . $file->getClientOriginalExtension());

        $product = Auth::user()->products()->create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'excel_file' => $path
        ]);

        return response()->json(['message' => 'Product uploaded successfully'], 200);
    }

    public function getProduct()
    {
        // Fetch products with their relations
        $products = Product::with('user', 'category', 'subcategory')->get();

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

    public function getExcelData($id)
    {
        $user = Auth::user();
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        $hasPurchased = Order::where('user_id', $user)->where('product_id', $product->id)->exists();

        if (!$hasPurchased) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have not purchased this product',
            ], 403);
        }

        $filePath = storage_path('app/' . $product->excel_file);

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error reading Excel file: ',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function purchaseProduct($id)
    {
        $user = Auth::user();
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        $hasPurchased = Order::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($hasPurchased) {
            return response()->json(['status' => 'error', 'message' => 'You already purchased this product'], 400);
        }

        Order::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Update last purchased date
        $product->update([
            'last_purchased_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Product purchased successfully'], 200);
    }
}
