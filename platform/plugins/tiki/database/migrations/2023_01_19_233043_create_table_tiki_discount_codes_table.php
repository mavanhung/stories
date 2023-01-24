<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTikiDiscountCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiki_discount_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('seller_id')->index();
            $table->integer('categories_id')->index()->nullable()->comment('id danh mục của mã giảm giá');
            $table->bigInteger('coupon_id')->index()->comment('coupon id');
            $table->string('coupon_code')->index()->comment('mã giảm giá');
            $table->string('label')->index()->nullable();
            $table->string('tags')->nullable();
            $table->string('short_title')->nullable();
            $table->string('period')->nullable();
            $table->string('simple_action')->nullable();
            $table->string('coupon_type')->nullable();
            $table->bigInteger('discount_amount')->index()->nullable();
            $table->bigInteger('min_amount')->index()->nullable();
            $table->bigInteger('rule_id')->nullable();
            $table->text('short_description')->index()->nullable()->comment('mô tả ngắn điều kiện');
            $table->text('long_description')->nullable()->comment('mô tả chi tiết điều kiện');
            $table->dateTime('expired_at')->index()->nullable()->comment('Hạn sử dụng');
            $table->string('icon_url')->nullable()->comment('hình ảnh mã giảm giá');
            $table->tinyInteger('is_crawler_home')->default(0)->comment('mã giảm giá crawler ở trang url tiki.vn/khuyen-mai/ma-giam-gia = 1');
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
        Schema::dropIfExists('tiki_discount_codes');
    }
}
