<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

// Check for existing product with the same name, price, or file
$existingProduct = Product::where('name', $request->name)
->where('price', $request->price)
->whereHas('excel_file', function ($query) use ($request) {
// Extract the filename from the uploaded file and check if it exists
$fileName = time() . '.' . $request->file('file')->getClientOriginalExtension();
$query->where('excel_file', 'like', "%$fileName%");
})
->first();

if ($existingProduct) {
return response()->json(['message' => 'Product with the same name, price, or file already exists.'], 400);
}

// Handle file upload
$file = $request->file('file');
$path = $file->storeAs('public/products', time() . '.' . $file->getClientOriginalExtension());

// Create the product record with 'approved' status
Product::create([
'name' => $request->name,
'description' => $request->description,
'price' => $request->price,
'category_id' => $request->category_id,
'subcategory_id' => $request->subcategory_id,
'excel_file' => $path,
'status' => 'approved', // Auto-approve the product
]);

return response()->json(['message' => 'Product uploaded and approved successfully'], 200);
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

// If product not found
return response()->json([
'status' => 'error',
'message' => 'Product not found'
], 404);
}

public function getExcelData($id)
{
$product = Product::find($id);

if (!$product) {
return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
}

$filePath = storage_path('app/' . $product->excel_file);

try {
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();
} catch (\Exception $e) {
return response()->json(['status' => 'error', 'message' => 'Error reading Excel file: ' . $e->getMessage()], 500);
}

return response()->json([
'status' => 'success',
'data' => $data,
]);
}
}