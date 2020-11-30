<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateComponenteCurricularTurma extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('modules.componente_curricular_turma', 'carga_horaria_auxiliar')) {
            Schema::table('modules.componente_curricular_turma', function (Blueprint $table)
            {
                if (Schema::hasColumn('modules.componente_curricular_turma', 'carga_horaria_auxiliar')) {
                    $table->string('carga_horaria_auxiliar',8)->nullable();
                }

                if (Schema::hasColumn('modules.hora_aula', 'carga_horaria_auxiliar')) {
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
