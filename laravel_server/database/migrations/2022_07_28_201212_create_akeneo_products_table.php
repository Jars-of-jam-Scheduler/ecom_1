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
        Schema::create('akeneo_products', function (Blueprint $table) {
            $table->timestamps();

			$table->string('code')->comment('Akeneo identifier');
			$table->string('reference')->primary();
			$table->text('name')->nullable();
			$table->text('description')->nullable();
			$table->float('price_with_taxes')->nullable();
			$table->enum('type', ['simple_product', 'service']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('akeneo_products');
    }
};
