<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsByCoursesClassShiftSchoolReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-by-courses-class-shift-school';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
//        $this->addRequiredArg('escola');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "select count(*) as total, f.nome as escola, nm_curso, nm_serie, g.nome as turno FROM pmieducar.matricula a
left outer join pmieducar.serie b on b.cod_serie = a.ref_ref_cod_serie
left outer join pmieducar.matricula_turma j on j.ref_cod_matricula = a.cod_matricula
left outer join pmieducar.turma c on j.ref_cod_turma = c.cod_turma
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
left outer join pmieducar.turma_turno g on g.id = c.turma_turno_id
left outer join pmieducar.curso h on h.cod_curso = a.ref_cod_curso
WHERE  a.ativo = 1 "
            .($situacao == 9 ? "" : " AND
       a.aprovado = $situacao").
       " AND a.ano =   $ano  AND
       e.ref_cod_instituicao =  $instituicao group by 2,3,4,5  order by 2,3,4,5";
    }

}
