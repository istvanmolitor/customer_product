<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductCategoryTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_product_category_translaions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('customer_product_category_id');
            $table->foreign('customer_product_category_id', 'cateory_id')->references('id')->on('customer_product_categories');

            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('keywords')->nullable();

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
        Schema::dropIfExists('customer_product_category_translaions');
    }
}
