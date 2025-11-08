<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerProductImageTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'customer_product_image_translations',
            function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('customer_product_image_id');
                $table->foreign('customer_product_image_id', 'product_image_id')->references('id')->on('customer_product_images');

                $table->unsignedBigInteger('language_id');
                $table->foreign('language_id')->references('id')->on('languages');

                $table->integer('sort');
                $table->string('title')->nullable();

                $table->timestamps();
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
        Schema::dropIfExists('customer_product_image_translations');
    }
}
