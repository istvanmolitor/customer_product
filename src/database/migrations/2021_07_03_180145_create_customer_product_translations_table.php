<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'customer_product_translations',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('customer_product_id');
                $table->foreign('customer_product_id')->references('id')->on('customer_products');

                $table->unsignedBigInteger('language_id');
                $table->foreign('language_id')->references('id')->on('languages');

                $table->string('name');
                $table->text('description')->nullable();
                $table->string('keywords')->nullable();
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
        Schema::dropIfExists('customer_product_translations');
    }
}
