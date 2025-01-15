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

// Process file upload and import
$file = $request->file('file');
$path = $file->storeAs('public/products', time() . '.' . $file->getClientOriginalExtension());

// Save data to Product model
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
}