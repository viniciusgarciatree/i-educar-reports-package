<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class NumberOfStudentsAboveAndBelowAverageReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'number-of-students-above-and-below-average';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
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
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $etapa = $this->args['etapa'] ?: 0;

        return 'select cc.nome as disciplina,  (CASE WHEN ncc.nota >= ra.media THEN \'ACIMA\' ELSE \'ABAIXO\' END) as nota, count(*) as total from pmieducar.matricula mc
left outer join pmieducar.matricula_turma mt ON mt.ref_cod_matricula = mc.cod_matricula and mt.ativo = 1
left outer join pmieducar.turma t ON t.cod_turma = mt.ref_cod_turma
left outer join modules.nota_aluno na ON na.matricula_id = mc.cod_matricula
left outer join nota_componente_curricular ncc ON ncc.nota_aluno_id = na.id
left outer join pmieducar.escola esc on mc.ref_ref_cod_escola = esc.cod_escola
left outer join cadastro.pessoa pes on esc.ref_idpes = pes.idpes
left outer join componente_curricular cc ON cc.id = ncc.componente_curricular_id
left outer join modules.regra_avaliacao_serie_ano rasa ON rasa.serie_id = mc.ref_ref_cod_serie AND mc.ano = rasa.ano_letivo
left outer join modules.regra_avaliacao ra ON ra.id = rasa.regra_avaliacao_id
where mc.ativo = 1 AND
        nota >= 0 AND '.
            ($escola != 0 ? "esc.cod_escola = $escola AND " : ' ').
            ($curso != 0 ? "mc.ref_cod_curso = $curso AND " : ' ').
            ($serie != 0 ? "mc.ref_ref_cod_serie = $serie AND " : ' ').
            ($turma != 0 ? "mt.ref_cod_turma = $turma AND " : ' ').
            ($etapa != 0 ? "ncc.etapa = '$etapa' AND " : ' ').
       " mc.ano =   $ano  AND
       esc.ref_cod_instituicao =  $instituicao group by 1,2;";
    }
}
