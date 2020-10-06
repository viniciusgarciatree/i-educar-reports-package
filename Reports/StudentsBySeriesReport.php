<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsBySeriesReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * Retorna o nome do template (arquivo .jrxml) que será utilizado como
     * template para a renderização.
     *
     * @return string
     */
    public function templateName()
    {
        return $this->args['separar'] == 1 ? 'students-by-series-school' : 'students-by-series';
    }

    /**
     * Adiciona os parâmetros obrigatórios a serem passados ao renderizador.
     *
     * @return void
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
        ];
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        return $this->args['separar'] === "true"
            ? $this->getSqlReportSchool()
            : $this->getSqlReport();
    }

    private function getSqlReportSchool()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "select count(*) as total, f.nome as escola, nm_curso,s.nm_serie FROM pmieducar.matricula a
left outer join pmieducar.curso b on b.cod_curso = a.ref_cod_curso
left outer join pmieducar.serie s on s.ref_cod_curso = b.cod_curso
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
WHERE  a.ativo = 1 AND
       a.ano = $ano AND
       e.ref_cod_instituicao = $instituicao "
            .($situacao == 9 ? "" : " AND
    a.aprovado = $situacao"). " ".($escola == 0 ? "" : " AND cod_escola = $escola")."
       group by 2,3,4  order by 2,3,4;";

    }

    private function getSqlReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "
select 
  count(*) as total, 
  b.nm_curso,
  s.nm_serie
FROM pmieducar.matricula a
left outer join pmieducar.curso b on b.cod_curso = a.ref_cod_curso
left outer join pmieducar.serie s on s.ref_cod_curso = b.cod_curso
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
WHERE  a.ativo = 1 AND
       a.ano = $ano AND
       e.ref_cod_instituicao = $instituicao ".($situacao == 9 ? "" : " AND
    a.aprovado = $situacao")." ".($escola == 0 ? "" : " AND cod_escola = $escola")."
       group by 2,3  order by 2,3;";

    }
}