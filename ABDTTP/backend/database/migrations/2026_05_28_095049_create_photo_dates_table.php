<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhotoDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('photo_dates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('capacity')->nullable();
            $table->unsignedBigInteger('marker_id')->nullable();
            $table->string('marker_name')->nullable();
            $table->double('lat', 15, 8)->nullable();
            $table->double('lng', 15, 8)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('created_by_username')->nullable();
            $table->json('attendees')->default(json_encode([]));
            $table->timestamps();

            $table->foreign('marker_id')->references('id')->on('markers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photo_dates');
    }
}
