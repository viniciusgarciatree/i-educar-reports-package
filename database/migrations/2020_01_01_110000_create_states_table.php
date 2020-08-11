<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(count(DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename = 'states');"))[0]=="false") {
            Schema::create(
                'public.states',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('country_id');
                    $table->string('name');
                    $table->string('abbreviation');
                    $table->integer('ibge_code')->nullable();
                    $table->timestamps();
                    $table->softDeletes();
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public.states');
    }
}
