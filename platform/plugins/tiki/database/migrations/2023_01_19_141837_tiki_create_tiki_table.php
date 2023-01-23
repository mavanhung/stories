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
        Schema::create('tikis', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('status', 60)->default('published');
            $table->timestamps();
        });

        Schema::create('tikis_translations', function (Blueprint $table) {
            $table->string('lang_code');
            $table->integer('tikis_id');
            $table->string('name', 255)->nullable();

            $table->primary(['lang_code', 'tikis_id'], 'tikis_translations_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tikis');
        Schema::dropIfExists('tikis_translations');
    }
};
