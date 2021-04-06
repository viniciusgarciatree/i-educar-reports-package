<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TransportationStudentsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'transportation-students';
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
        $rota = $this->args['rota'] ?: 0;

        $return = "
        select
        f.nome as escola,
        pa.nome,
        nm_serie,
        c.nm_turma,
        g.nome as turno,
        idtlog,
        logradouro,
        numero,
        complemento,
        bairro,
        rte.descricao AS rota
        FROM pmieducar.matricula a
left outer join pmieducar.serie b on b.cod_serie = a.ref_ref_cod_serie
left outer join pmieducar.matricula_turma j on j.ref_cod_matricula = a.cod_matricula
left outer join pmieducar.turma c on j.ref_cod_turma = c.cod_turma
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
left outer join pmieducar.turma_turno g on g.id = c.turma_turno_id
left outer join pmieducar.aluno al on al.cod_aluno = a.ref_cod_aluno
left outer join cadastro.pessoa pa on al.ref_idpes = pa.idpes
right outer join modules.pessoa_transporte pt on pt.ref_idpes = pa.idpes
left outer join cadastro.v_endereco vie on pt.ref_idpes = vie.idpes
left outer join modules.rota_transporte_escolar rte ON (rte.cod_rota_transporte_escolar = pt.ref_cod_rota_transporte_escolar)
WHERE  a.ativo = 1 AND a.ano = {$ano} AND e.ref_cod_instituicao = {$instituicao}
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE a.ref_ref_cod_escola = {$escola} END)
        AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE a.ref_cod_curso = {$curso} END)
        AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE a.ref_ref_cod_serie = {$serie} END)
        AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE j.ref_cod_turma = {$turma} END)
        AND (CASE WHEN {$rota} = 0 THEN TRUE ELSE pt.ref_cod_rota_transporte_escolar = {$rota} END)
        ORDER BY f.nome, pa.nome, nm_serie, c.nm_turma, g.nome";

        return $return;
    }
}
