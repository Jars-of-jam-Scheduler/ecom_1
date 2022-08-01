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
		Schema::create('akeneo_product_supplier', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

			$table->string('akeneo_product_reference');
			$table->unsignedBigInteger('supplier_id');
			
			$table->foreign('akeneo_product_reference')->references('reference')->on('akeneo_products');
			$table->foreign('supplier_id')->references('id')->on('suppliers');
			$table->dateTime('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('akeneo_product_supplier');
    }
};
