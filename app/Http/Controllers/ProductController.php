<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\Log;

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


        $product->excel_file_url = asset('storage/' . str_replace('public/', '', $product->excel_file));

        return response()->json([
            'status' => 'success',
            'products' => $product,
        ]);
    }

    public function getExcelUrl($id)
    {

        $product = Product::find($id);

        if ($product) {
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

        // if (!$hasPurchased) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'You have not purchased this product',
        //     ], 403);
        // }

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
            'isAuthorized' => false
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

        $product->update([
            'last_purchased_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Product purchased successfully'], 200);
    }
    public function downloadProductAsPDF(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json(['message' => 'Produk tidak ditemukan'], 404);
            }

            if (!$product->excel_file || !Storage::exists($product->excel_file)) {
                return response()->json(['message' => 'File Excel tidak ditemukan'], 404);
            }

            
            $data = $request->input('data');
            if (empty($data)) {
                $filePath = storage_path('app/' . $product->excel_file);
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();
            }

            $html = '<h3 style="text-align: center; font-size: 20px;">Produk: ' . e($product->name) . '</h3>';
            $html .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;">';

            if (!empty($data)) {
                $html .= '<thead><tr>';
                foreach ($data[0] as $cell) {
                    $html .= '<th style="background-color: #f2f2f2; text-align: left; padding: 8px; border: 1px solid #ddd;">' . e($cell) . '</th>';
                }
                $html .= '</tr></thead>';

                $html .= '<tbody>';
                foreach (array_slice($data, 1) as $rowIndex => $row) {
                    $bgColor = ($rowIndex % 2 == 0) ? '#ffffff' : '#f9f9f9';
                    $html .= '<tr style="background-color: ' . $bgColor . ';">';
                    foreach ($row as $cell) {
                        $html .= '<td style="padding: 8px; border: 1px solid #ddd;">' . e($cell) . '</td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
            } else {
                $html .= '<tr><td colspan="100%" style="text-align: center; padding: 10px;">Tidak ada data tersedia</td></tr>';
            }

            $html .= '</table>';

            
            if (!$product->is_paid) {
                $html .= '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(45deg); font-size: 100px; color: rgba(0, 0, 0, 0.2); font-weight: bold;">UNPAID</div>';
            }

            $pdf = app(Pdf::class)->loadHTML($html)->setPaper('A4', 'portrait');
            $filename = 'produk_' . $product->id . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error saat membuat PDF untuk produk ID ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal membuat PDF. Silakan coba lagi nanti.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
