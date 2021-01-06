<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsByCoursesReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return $this->args['separar'] == 1 ? 'students-by-courses-school' : 'students-by-courses';
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

        return "select count(*) as total, f.nome as escola, nm_curso FROM pmieducar.matricula a
left outer join pmieducar.curso b on b.cod_curso = a.ref_cod_curso
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
WHERE  a.ativo = 1 AND
       a.ano = $ano AND
       e.ref_cod_instituicao = $instituicao "
            .($situacao == 9 ? '' : " AND
    a.aprovado = $situacao"). ' '.($escola == 0 ? '' : " AND cod_escola = $escola").'
       group by 2,3  order by 2,3;';
    }

    private function getSqlReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "select count(*) as total, nm_curso FROM pmieducar.matricula a
left outer join pmieducar.curso b on b.cod_curso = a.ref_cod_curso
left outer join pmieducar.escola e on a.ref_ref_cod_escola = e.cod_escola
left outer join cadastro.pessoa f on e.ref_idpes = f.idpes
WHERE  a.ativo = 1 AND
       a.ano = $ano AND
       e.ref_cod_instituicao = $instituicao ".($situacao == 9 ? '' : " AND
    a.aprovado = $situacao").' '.($escola == 0 ? '' : " AND cod_escola = $escola").'
       group by 2  order by 2;';
    }
}
