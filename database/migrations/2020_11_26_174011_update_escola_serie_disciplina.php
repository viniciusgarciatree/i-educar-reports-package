<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEscolaSerieDisciplina extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('pmieducar.escola_serie_disciplina', 'carga_horaria_auxiliar')) {
            Schema::table('pmieducar.escola_serie_disciplina', function (Blueprint $table)
            {
                if (Schema::hasColumn('pmieducar.escola_serie_disciplina', 'carga_horaria_auxiliar')) {
                    $table->string('carga_horaria_auxiliar',8)->nullable();
                }
                if (Schema::hasColumn('pmieducar.escola_serie_disciplina', 'aulas_semanais')) {
                    $table->integer('aulas_semanais')->nullable();
                }

                if (Schema::hasColumn('pmieducar.escola_serie_disciplina', 'hora_aula')) {
                    $table->integer('hora_aula')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
