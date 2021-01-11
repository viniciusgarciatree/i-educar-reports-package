<?php

use App\Menu;
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

        Menu::query()->updateOrCreate([
            'old' => 999601
        ], [
            'parent_id' => Menu::query()->where('old', 999600)->firstOrFail()->getKey(),
            'title' => 'Carteira de Transporte',
            'description' => null,
            'link' => '/module/Reports/StudentsTransportCard',
            'order' => 0,
            'old' => 999601,
            'process' => 999601,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999602
        ], [
            'parent_id' => Menu::query()->where('old', 999600)->firstOrFail()->getKey(),
            'title' => 'Carteira de Estudante',
            'description' => null,
            'link' => '/module/Reports/StudentsId',
            'order' => 0,
            'old' => 999602,
            'process' => 999602,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999701
        ], [
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da média das turmas por disciplina e geral',
            'description' => null,
            'link' => '/module/Reports/AverageOfClassesPerDisciplineGeneral',
            'order' => 0,
            'old' => 999701,
            'process' => 999701,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999609
        ], [
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'title' => 'Mapa do conselho de classe',
            'description' => null,
            'link' => '/module/Reports/ClassCouncilMap',
            'order' => 0,
            'old' => 999609,
            'process' => 999609,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999702
        ], [
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da quantidade de alunos acima e abaixo da média por disciplina',
            'description' => null,
            'link' => '/module/Reports/NumberOfStudentsAboveAndBelowAverage',
            'order' => 0,
            'old' => 999702,
            'process' => 999702,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999703
        ], [
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da média das escolas por disciplina',
            'description' => null,
            'link' => '/module/Reports/SchoolAverageBySubject',
            'order' => 0,
            'old' => 999703,
            'process' => 999703,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999704
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relação de horas alocadas por servidor',
            'description' => null,
            'link' => '/module/Reports/ServantAllocated',
            'order' => 0,
            'old' => 999704,
            'process' => 999704,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999108
        ], [
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Alunos enturmados e não enturmados',
            'description' => null,
            'link' => '/module/Reports/StudentsAllocation',
            'order' => 0,
            'old' => 999108,
            'process' => 999108,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999705
        ], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por escola, série e turno',
            'description' => null,
            'link' => '/module/Reports/StudentsByClassShiftSchool',
            'order' => 0,
            'old' => 999705,
            'process' => 999705,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999706
        ], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por escola, curso, série e turno',
            'description' => null,
            'link' => '/module/Reports/StudentsByCoursesClassShiftSchool',
            'order' => 0,
            'old' => 999706,
            'process' => 999706,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999707
        ], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por curso',
            'description' => null,
            'link' => '/module/Reports/StudentsByCourses',
            'order' => 0,
            'old' => 999707,
            'process' => 999707,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999708
        ], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por bairro',
            'description' => null,
            'link' => '/module/Reports/StudentsByNeighborhood',
            'order' => 0,
            'old' => 999708,
            'process' => 999708,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999709
        ], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Alunos matriculados por escola',
            'description' => null,
            'link' => '/module/Reports/StudentsBySchool',
            'order' => 0,
            'old' => 999709,
            'process' => 999709,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999710
        ], [
            'parent_id' => Menu::query()->where('old', 999905)->firstOrFail()->getKey(),
            'title' => 'Emissão de etiquetas',
            'description' => null,
            'link' => '/module/Reports/LibraryTags',
            'order' => 0,
            'old' => 999710,
            'process' => 999710,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999711
        ], [
            'parent_id' => Menu::query()->where('old', 999906)->firstOrFail()->getKey(),
            'title' => 'Quantidade de emprestimos por leitor',
            'description' => null,
            'link' => '/module/Reports/AmountOfLoanToReaders',
            'order' => 0,
            'old' => 999711,
            'process' => 999711,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999712
        ], [
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Emissão de etiquetas de mala direta',
            'description' => null,
            'link' => '/module/Reports/DirectMailStudents',
            'order' => 0,
            'old' => 999712,
            'process' => 999712,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999713
        ], [
            'parent_id' => Menu::query()->where('old', 999301)->firstOrFail()->getKey(),
            'title' => 'Situação dos anos letivos',
            'description' => null,
            'link' => '/module/Reports/SchoolYears',
            'order' => 0,
            'old' => 999713,
            'process' => 999713,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999714
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relação de faltas e atrasos dos servidores',
            'description' => null,
            'link' => '/module/Reports/ShortagesDelaysServants',
            'order' => 0,
            'old' => 999714,
            'process' => 999714,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999715
        ], [
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico comparativo da média das escolas',
            'description' => null,
            'link' => '/module/Reports/SchoolAverage',
            'order' => 0,
            'old' => 999715,
            'process' => 999715,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999716
        ], [
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relação de alunos usuários do transporte escolar',
            'description' => null,
            'link' => '/module/Reports/TransportationStudents',
            'order' => 0,
            'old' => 999716,
            'process' => 999716,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999717
        ], [
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relação de usuários do transporte escolar por fornecedor',
            'description' => null,
            'link' => '/module/Reports/TransportOfUsersBySupplier',
            'order' => 0,
            'old' => 999717,
            'process' => 999717,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999718
        ], [
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'title' => 'Boletim escolar educação infantil',
            'description' => null,
            'link' => '/module/Reports/ReportCardChildish',
            'order' => 0,
            'old' => 999718,
            'process' => 999718,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999719
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de alocações dos servidores',
            'description' => null,
            'link' => '/module/Reports/ServantsAllocations',
            'order' => 0,
            'old' => 999719,
            'process' => 999719,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999720
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de dados pessoais dos servidores',
            'description' => null,
            'link' => '/module/Reports/PersonalDataServants',
            'order' => 0,
            'old' => 999720,
            'process' => 999720,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999721
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório vinculos dos servidores',
            'description' => null,
            'link' => '/module/Reports/ServantsBindings',
            'order' => 0,
            'old' => 999721,
            'process' => 999721,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999722
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório cargo x função dos servidores',
            'description' => null,
            'link' => '/module/Reports/ServantsOfficeFunction',
            'order' => 0,
            'old' => 999722,
            'process' => 999722,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999723
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de escolaridade dos servidores',
            'description' => null,
            'link' => '/module/Reports/ServantsSchooling',
            'order' => 0,
            'old' => 999723,
            'process' => 999723,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999724
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de lotação x exercício dos servidores',
            'description' => null,
            'link' => '/module/Reports/ServantsCapacityExercise',
            'order' => 0,
            'old' => 999724,
            'process' => 999724,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999725
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de cursos dos servidores',
            'description' => null,
            'link' => '/module/Reports/ServantsCourses',
            'order' => 0,
            'old' => 999725,
            'process' => 999725,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999726
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Quadro de frequência dos servidores',
            'description' => null,
            'link' => '/module/Reports/MonthlyClosingServants',
            'order' => 0,
            'old' => 999726,
            'process' => 999726,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999727
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relação de servidores afastados',
            'description' => null,
            'link' => '/module/Reports/AbsenceServants',
            'order' => 0,
            'old' => 999727,
            'process' => 999727,
        ]);

        Menu::query()->updateOrCreate([
            'old' => 999728
        ], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de docentes por turma',
            'description' => null,
            'link' => '/module/Reports/TeachersByClass',
            'order' => 0,
            'old' => 999728,
            'process' => 999728,
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
        Menu::query()->where('old', 999719)->delete();
        Menu::query()->where('old', 999720)->delete();
        Menu::query()->where('old', 999721)->delete();
        Menu::query()->where('old', 999722)->delete();
        Menu::query()->where('old', 999723)->delete();
        Menu::query()->where('old', 999724)->delete();
        Menu::query()->where('old', 999725)->delete();
        Menu::query()->where('old', 999726)->delete();
        Menu::query()->where('old', 999727)->delete();
        Menu::query()->where('old', 999728)->delete();
    }
}
