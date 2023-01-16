<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('discount_codes_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->integer('discount_codes_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'discount_codes_id'], 'discount_codes_translations_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('discount_codes_translations');
    }
};
