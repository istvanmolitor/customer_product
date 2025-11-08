<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'customer_products',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('customer_id');
                $table->foreign('customer_id')->references('id')->on('customers');

                $table->unsignedBigInteger('product_id')->nullable();
                $table->foreign('product_id')->references('id')->on('products');

                $table->unsignedBigInteger('same_customer_product')->nullable();

                $table->string('sku');

                $table->unsignedBigInteger('product_unit_id');
                $table->foreign('product_unit_id')->references('id')->on('product_units');

                $table->string('url')->nullable();

                $table->decimal('price', 11)->nullable();
                $table->unsignedBigInteger('currency_id');
                $table->foreign('currency_id')->references('id')->on('currencies');

                $table->unsignedInteger('stock')->nullable();

                $table->string('keywords')->nullable();

                $table->timestamps();

                $table->unique(['customer_id', 'sku'], 'customer_sku_unique');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_products');
    }
}
