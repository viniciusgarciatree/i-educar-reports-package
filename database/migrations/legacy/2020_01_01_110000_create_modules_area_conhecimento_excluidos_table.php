<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesAreaConhecimentoExcluidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'modules' AND tablename = 'area_conhecimento_excluidos');"))[0]->exists == false) {
            Schema::create(
                'modules.area_conhecimento_excluidos',
                function (Blueprint $table) {
                    $table->integer('id');
                    $table->integer('instituicao_id');
                    $table->string('nome');
                    $table->string('secao')->nullable();
                    $table->integer('ordenamento_ac')->nullable();
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
        Schema::dropIfExists('modules.area_conhecimento_excluidos');
    }
}
