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
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'category_id' => $row['category_id'],
            'subcategory_id' => $row['subcategory_id'],
            'excel_file' => $this->excelFilePath,
        ]);
    }
}
