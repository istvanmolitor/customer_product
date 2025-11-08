<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_product_attributes', function (Blueprint $table) {

            $table->unsignedBigInteger('customer_product_id');
            $table->foreign('customer_product_id')->references('id')->on('customer_products');

            $table->unsignedBigInteger('product_field_option_id');
            $table->foreign('product_field_option_id')->references('id')->on('product_field_options');

            $table->integer('sort');

            $table->primary(['customer_product_id', 'product_field_option_id'], 'customer_product_field_value_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_product_attributes');
    }
}
