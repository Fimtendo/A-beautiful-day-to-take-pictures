<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotoPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photo_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('created_by_username')->nullable();
            $table->unsignedBigInteger('marker_id')->nullable();
            $table->string('marker_name')->nullable();
            $table->double('lat', 15, 8)->nullable();
            $table->double('lng', 15, 8)->nullable();
            $table->string('image_url');
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('marker_id')->references('id')->on('markers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photo_posts');
    }
}
