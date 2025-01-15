<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
protected $excelFilePath;

public function __construct($excelFilePath = null)
{
$this->excelFilePath = $excelFilePath;
}

public function model(array $row)
{
return new Product([
'name' => $row['name'], // Sesuaikan dengan nama kolom di file Excel
'description' => $row['description'],
'price' => $row['price'],
'category_id' => $row['category_id'], // ID kategori yang sesuai
'subcategory_id' => $row['subcategory_id'], // ID subkategori yang sesuai
'excel_file' => $this->excelFilePath, // Menyimpan path file Excel
]);
}
}