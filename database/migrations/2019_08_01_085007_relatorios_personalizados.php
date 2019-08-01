<?php

use App\Menu;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RelatoriosPersonalizados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->updateOrCreate([
            'old' => 999600,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Carteiras',
            'order' => 2,
            'parent_old' => 21127,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999600)->firstOrFail()->getKey(),
            'title' => 'Carteira de Transporte',
            'description' => null,
            'link' => '/module/Reports/StudentsTransportCard',
            'order' => 0,
            'old' => 999601,
            'process' => 999601,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999600)->firstOrFail()->getKey(),
            'title' => 'Carteira de Estudante',
            'description' => null,
            'link' => '/module/Reports/StudentsId',
            'order' => 0,
            'old' => 999602,
            'process' => 999602,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da média das turmas por disciplina e geral',
            'description' => null,
            'link' => '/module/Reports/AverageOfClassesPerDisciplineGeneral',
            'order' => 0,
            'old' => 999701,
            'process' => 999701,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'title' => 'Mapa do conselho de classe',
            'description' => null,
            'link' => '/module/Reports/ClassCouncilMap',
            'order' => 0,
            'old' => 999609,
            'process' => 999609,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da quantidade de alunos acima e abaixo da média por disciplina',
            'description' => null,
            'link' => '/module/Reports/NumberOfStudentsAboveAndBelowAverage',
            'order' => 0,
            'old' => 999702,
            'process' => 999702,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da média das escolas por disciplina',
            'description' => null,
            'link' => '/module/Reports/SchoolAverageBySubject',
            'order' => 0,
            'old' => 999703,
            'process' => 999703,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relação de horas alocadas por servidor',
            'description' => null,
            'link' => '/module/Reports/ServantAllocated',
            'order' => 0,
            'old' => 999704,
            'process' => 999704,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Alunos enturmados e não enturmados',
            'description' => null,
            'link' => '/module/Reports/StudentsAllocation',
            'order' => 0,
            'old' => 999108,
            'process' => 999108,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por escola, série e turno',
            'description' => null,
            'link' => '/module/Reports/StudentsByClassShiftSchool',
            'order' => 0,
            'old' => 999705,
            'process' => 999705,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por escola, curso, série e turno',
            'description' => null,
            'link' => '/module/Reports/StudentsByCoursesClassShiftSchool',
            'order' => 0,
            'old' => 999706,
            'process' => 999706,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por curso',
            'description' => null,
            'link' => '/module/Reports/StudentsByCourses',
            'order' => 0,
            'old' => 999707,
            'process' => 999707,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por bairro',
            'description' => null,
            'link' => '/module/Reports/StudentsByNeighborhood',
            'order' => 0,
            'old' => 999708,
            'process' => 999708,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por escola',
            'description' => null,
            'link' => '/module/Reports/StudentsBySchool',
            'order' => 0,
            'old' => 999709,
            'process' => 999709,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999905)->firstOrFail()->getKey(),
            'title' => 'Emissão de etiquetas',
            'description' => null,
            'link' => '/module/Reports/LibraryTags',
            'order' => 0,
            'old' => 999710,
            'process' => 999710,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999906)->firstOrFail()->getKey(),
            'title' => 'Quantidade de emprestimos por leitor',
            'description' => null,
            'link' => '/module/Reports/AmountOfLoanToReaders',
            'order' => 0,
            'old' => 999711,
            'process' => 999711,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Emissão de etiquetas de mala direta',
            'description' => null,
            'link' => '/module/Reports/DirectMailStudents',
            'order' => 0,
            'old' => 999712,
            'process' => 999712,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999301)->firstOrFail()->getKey(),
            'title' => 'Situação dos anos letivos',
            'description' => null,
            'link' => '/module/Reports/SchoolYears',
            'order' => 0,
            'old' => 999713,
            'process' => 999713,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relação de faltas e atrasos dos servidores',
            'description' => null,
            'link' => '/module/Reports/ShortagesDelaysServants',
            'order' => 0,
            'old' => 999714,
            'process' => 999714,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da média das escolas',
            'description' => null,
            'link' => '/module/Reports/SchoolAverage',
            'order' => 0,
            'old' => 999715,
            'process' => 999715,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relação de alunos usuários do transporte escolar',
            'description' => null,
            'link' => '/module/Reports/TransportationStudents',
            'order' => 0,
            'old' => 999716,
            'process' => 999716,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relação de usuários do transporte escolar por fornecedor',
            'description' => null,
            'link' => '/module/Reports/TransportOfUsersBySupplier',
            'order' => 0,
            'old' => 999717,
            'process' => 999717,
        ]);

        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'title' => 'Boletim Escolar Educação Infantil',
            'description' => null,
            'link' => '/module/Reports/ReportCardChildish',
            'order' => 0,
            'old' => 999718,
            'process' => 999718,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('old', 999600)->delete();
        Menu::query()->where('old', 999601)->delete();
        Menu::query()->where('old', 999602)->delete();
        Menu::query()->where('old', 999701)->delete();
        Menu::query()->where('old', 999609)->delete();
        Menu::query()->where('old', 999702)->delete();
        Menu::query()->where('old', 999703)->delete();
        Menu::query()->where('old', 999704)->delete();
        Menu::query()->where('old', 999108)->delete();
        Menu::query()->where('old', 999705)->delete();
        Menu::query()->where('old', 999706)->delete();
        Menu::query()->where('old', 999707)->delete();
        Menu::query()->where('old', 999708)->delete();
        Menu::query()->where('old', 999709)->delete();
        Menu::query()->where('old', 999710)->delete();
        Menu::query()->where('old', 999711)->delete();
        Menu::query()->where('old', 999712)->delete();
        Menu::query()->where('old', 999713)->delete();
        Menu::query()->where('old', 999714)->delete();
        Menu::query()->where('old', 999715)->delete();
        Menu::query()->where('old', 999716)->delete();
        Menu::query()->where('old', 999717)->delete();
        Menu::query()->where('old', 999718)->delete();
    }
}
