<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePmieducarDisciplinaDependenciaExcluidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((DB::select("select EXISTS (SELECT FROM pg_catalog.pg_tables WHERE schemaname = 'pmieducar' AND tablename = 'disciplina_dependencia_excluidos');"))[0]->exists == false) {
            Schema::create(
                'pmieducar.disciplina_dependencia_excluidos',
                function (Blueprint $table) {
                    $table->integer('cod_disciplina_dependencia');
                    $table->integer('ref_cod_matricula');
                    $table->integer('ref_cod_disciplina');
                    $table->integer('ref_cod_escola');
                    $table->integer('ref_cod_serie');
                    $table->text('observacao')->nullable();
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
        Schema::dropIfExists('pmieducar.disciplina_dependencia_excluidos');
    }
}
