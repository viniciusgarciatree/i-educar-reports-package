<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TransportationStudentsUseReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'transportation-students-use';
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

        $return = "
        SELECT * FROM (
        select
        pa.nome,
		f.nome as escola,
		CASE
        WHEN al.veiculo_transporte_escolar is NULL THEN 'Não'
        ELSE 'Sim'
          END
          AS usa_transporte,
		CASE
        WHEN al.veiculo_transporte_escolar is NULL THEN 1
        ELSE 0
          END
          AS usa_transporte_nao,
        CASE
        WHEN al.veiculo_transporte_escolar is NULL THEN 0
        ELSE 1
          END
          AS usa_transporte_sim,
regexp_replace(trim(CONCAT(CASE WHEN ARRAY[1] <@ al.veiculo_transporte_escolar THEN 'Vans/Kombis' ELSE '' END,'  ',
CASE WHEN ARRAY[2] <@ al.veiculo_transporte_escolar THEN 'Microônibus' ELSE '' END,'  ',
CASE WHEN ARRAY[3] <@ al.veiculo_transporte_escolar THEN 'Ônibus' ELSE '' END,'  ',
CASE WHEN ARRAY[4] <@ al.veiculo_transporte_escolar THEN 'Bicicleta' ELSE '' END,'  ',
CASE WHEN ARRAY[5] <@ al.veiculo_transporte_escolar THEN 'Tração animal' ELSE '' END,'  ',
CASE WHEN ARRAY[6] <@ al.veiculo_transporte_escolar THEN 'Outro' ELSE '' END,'  ',
CASE WHEN ARRAY[7] <@ al.veiculo_transporte_escolar THEN 'Aquaviário/Embarcação - Capacidade de até 5 alunos' ELSE '' END,'  ',
CASE WHEN ARRAY[8] <@ al.veiculo_transporte_escolar THEN 'Aquaviário/Embarcação - Capacidade entre 5 a 15 alunos' ELSE '' END,'  ',
CASE WHEN ARRAY[9] <@ al.veiculo_transporte_escolar THEN 'Aquaviário/Embarcação - Capacidade entre 15 a 35 alunos' ELSE '' END,'  ',
CASE WHEN ARRAY[10] <@ al.veiculo_transporte_escolar THEN 'Aquaviário/Embarcação - Capacidade acima de 35 alunos' ELSE '' END,'  ',
CASE WHEN ARRAY[11] <@ al.veiculo_transporte_escolar THEN 'Ferroviário - Trem/Metrô' ELSE '' END))
	   ,'(  ){2,}', ' - ', 'g') AS tipo
        FROM pmieducar.matricula a
left outer join pmieducar.serie b on b.cod_serie = a.ref_ref_cod_serie
left outer join pmieducar.matricula_turma j on j.ref_cod_matricula = a.cod_matricula
left outer join pmieducar.turma c on j.ref_cod_turma = c.cod_turma
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
left outer join pmieducar.turma_turno g on g.id = c.turma_turno_id
left outer join pmieducar.aluno al on al.cod_aluno = a.ref_cod_aluno
left outer join cadastro.pessoa pa on al.ref_idpes = pa.idpes
left outer join modules.transporte_aluno ta on ta.aluno_id = al.cod_aluno
left outer join modules.pessoa_transporte pt on pt.ref_idpes = pa.idpes
WHERE  a.ativo = 1 AND a.ano = {$ano} AND e.ref_cod_instituicao = {$instituicao}
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE a.ref_ref_cod_escola = {$escola} END)
        AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE a.ref_cod_curso = {$curso} END)
        AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE a.ref_ref_cod_serie = {$serie} END)
        AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE j.ref_cod_turma = {$turma} END)
        ) as t
        ORDER BY escola,usa_transporte DESC, nome";
        return $return;
    }
}
