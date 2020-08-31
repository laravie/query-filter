<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->morphs('notable');
            $table->string('title');
            $table->text('content');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
