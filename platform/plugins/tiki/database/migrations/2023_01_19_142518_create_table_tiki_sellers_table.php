<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTikiSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiki_sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('name');
            $table->string('seller_name')->index()->comment('seller name');
            $table->string('store_name')->nullable()->comment('store name');
            $table->integer('seller_id')->index()->comment('seller id');
            $table->integer('store_id')->nullable()->index()->comment('store id');
            $table->string('store_level')->nullable()->comment('store level');
            $table->string('seller_type')->nullable()->comment('seller type');
            $table->string('storefront_label')->nullable()->comment('storefront label');
            $table->string('logo')->nullable()->comment('url logo');
            $table->string('seller_url')->nullable()->comment('seller url');
            $table->string('url_slug')->nullable()->comment('url slug');
            $table->timestamp('live_at')->nullable()->comment('Thời gian đăng ký thành viên');
            $table->string('status', 60)->index()->default('published');
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
        Schema::dropIfExists('sellers');
    }
}
