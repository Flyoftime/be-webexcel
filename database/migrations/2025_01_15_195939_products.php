<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Nama produk
            $table->text('description');  // Deskripsi produk
            $table->decimal('price', 10, 2);  // Harga produk
            $table->foreignId('category_id')  // Relasi ke categories
                ->constrained()
                ->onDelete('restrict');  // Jika kategori dihapus, produk tidak akan dihapus
            $table->foreignId('subcategory_id')  // Relasi ke subcategories
                ->constrained()
                ->onDelete('restrict');
            $table->string('excel_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
