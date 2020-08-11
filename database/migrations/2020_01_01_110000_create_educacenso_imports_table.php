<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEducacensoImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(count(DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename = 'educacenso_imports');"))[0]=="false") {
            Schema::create(
                'public.educacenso_imports',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->integer('year');
                    $table->string('school');
                    $table->integer('user_id');
                    $table->boolean('finished');
                    $table->boolean('error')->default(false);
                    $table->timestamps();
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
        Schema::dropIfExists('public.educacenso_imports');
    }
}
