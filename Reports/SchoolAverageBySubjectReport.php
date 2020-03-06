<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class SchoolAverageBySubjectReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'school-average-by-subject';
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
        $etapa = $this->args['etapa'] ?: 0;

        return "select pes.nome as escola, cc.nome as disciplina, avg(nota) as media from pmieducar.matricula mc
left outer join pmieducar.matricula_turma mt ON mt.ref_cod_matricula = mc.cod_matricula and mt.ativo = 1
left outer join pmieducar.turma t ON t.cod_turma = mt.ref_cod_turma
left outer join modules.nota_aluno na ON na.matricula_id = mc.cod_matricula
left outer join nota_componente_curricular ncc ON ncc.nota_aluno_id = na.id
left outer join pmieducar.escola esc on mc.ref_ref_cod_escola = esc.cod_escola
left outer join cadastro.pessoa pes on esc.ref_idpes = pes.idpes
left outer join componente_curricular cc ON cc.id = ncc.componente_curricular_id
where mc.ativo = 1 AND
        nota >= 0 AND
       mc.ano =   $ano  AND
       esc.ref_cod_instituicao =  $instituicao  ".($etapa == 0 ? "" : " AND
       ncc.etapa = '$etapa'")." group by 1,2;";
    }

}
