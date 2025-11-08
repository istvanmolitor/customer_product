<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductCategoryProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_product_category_products', function (Blueprint $table) {

            $table->unsignedBigInteger('customer_product_category_id');
            $table->foreign('customer_product_category_id', 'fk_category')->references('id')->on('customer_product_categories');

            $table->unsignedBigInteger('customer_product_id');
            $table->foreign('customer_product_id', 'fk_product')->references('id')->on('customer_products');

            $table->primary(['customer_product_category_id', 'customer_product_id'], 'pri_customer_product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_product_category_products');
    }
}
