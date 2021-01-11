<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsByNeighborhoodReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return $this->args['separar'] == 1 ? 'students-by-neighborhood-school' : 'students-by-neighborhood';
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
        return $this->args['separar'] === 'true'
            ? $this->getSqlReportSchool()
            : $this->getSqlReport();
    }

    private function getSqlReportSchool()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "select count(*) as total, f.nome as escola, bairro, cidade FROM pmieducar.matricula d 
left outer join pmieducar.aluno a on a.cod_aluno = d.ref_cod_aluno
left outer join cadastro.pessoa b on a.ref_idpes = b.idpes
left outer join cadastro.v_endereco c on a.ref_idpes = c.idpes
left outer join pmieducar.escola e on d.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
WHERE  d.ativo = 1 AND
       d.ano = $ano AND
       e.ref_cod_instituicao = $instituicao "
            .($situacao == 9 ? '' : " AND
       d.aprovado = $situacao"). ' '.($escola == 0 ? '' : " AND cod_escola = $escola").'
       group by 2,3,4  order by 2,4,3;';
    }

    private function getSqlReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "select count(*) as total, bairro, cidade FROM pmieducar.matricula d 
left outer join pmieducar.aluno a on a.cod_aluno = d.ref_cod_aluno
left outer join cadastro.pessoa b on a.ref_idpes = b.idpes
left outer join cadastro.v_endereco c on a.ref_idpes = c.idpes
left outer join pmieducar.escola e on d.ref_ref_cod_escola = e.cod_escola
WHERE  d.ativo = 1 AND
       d.ano = $ano AND
       e.ref_cod_instituicao = $instituicao "
       .($situacao == 9 ? '' : " AND
       d.aprovado = $situacao"). ' '.($escola == 0 ? '' : " AND cod_escola = $escola").'
       group by 2,3  order by 3,2;';
    }
}
