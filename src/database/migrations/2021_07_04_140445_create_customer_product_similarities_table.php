<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductSimilaritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_product_similarities', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id');

            $table->unsignedBigInteger('customer_product_1_id');

            $table->unsignedBigInteger('customer_product_2_id');

            $table->float('similarity');

            $table->primary(['product_id', 'customer_product_1_id', 'customer_product_2_id'], 'customer_product_similarities_pk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_product_similarities');
    }
}
