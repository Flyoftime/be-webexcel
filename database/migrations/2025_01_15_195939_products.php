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
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('restrict');
            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('restrict');
            $table->foreignId('subcategory_id')
                ->constrained()
                ->onDelete('restrict');
            $table->string('excel_file');
            $table->timestamp('last_purchased_at')->nullable();
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
