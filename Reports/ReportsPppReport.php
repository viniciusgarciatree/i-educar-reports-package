<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'Reports/Tipos/TipoBoletim.php';
require_once 'App/Model/IedFinder.php';

class ReportsPppReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @return string
     *
     * @throws App_Model_Exception
     */
    public function templateName()
    {
        return "reports-ppp";
    }

    public function getJsonData()
    {

        $arrDados = [];

        $arrDados['matriculas'] = self::getNumeroMatricula();
        $arrDados['sexo'] = self::getSexo();
        $arrDados['cor_raca'] = self::getCorRaca();
        $arrDados['localizacao'] = self::getLocalizacao();
        $arrDados['transporte'] = self::getTransporte();

        $queryHeaderReport = $this->getSqlHeaderReport();

        return array_merge([
            'main'   => $arrDados,
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ]);
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
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'];
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $turma = $this->args['turma'] ?: 0;

        return "";
    }

    private function getWhere(){
        $ano = $this->args['ano'];
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        $where = " Where m.ano = {$ano} ";
        $where .= " AND m.ativo = 1 ";
        $where .= " AND (CASE WHEN {$situacao} = 9 THEN true ELSE m.aprovado = {$situacao} END) ";
        //$where .= " AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END) ";
        //$where .= " AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE escola.cod_escola = {$curso} END) ";
        //$where .= " AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE escola.cod_escola = {$serie} END) ";
        //$where .= " AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE escola.cod_escola = {$turma} END) ";

        return  $where;
    }

    public function getNumeroMatricula()
    {
        $where = self::getWhere();

        $sql = "
        SELECT 
            COUNT(*)::integer AS total_curso,NULLIF(curso,'Não Definido') AS curso 
        FROM (
            SELECT 
                distinct m.cod_matricula AS matricula,h.nm_curso as curso
            FROM pmieducar.matricula m
            LEFT JOIN pmieducar.curso h ON h.cod_curso = m.ref_cod_curso
            $where
        ) as t
        GROUP BY curso
        ORDER BY curso
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getSexo()
    {
        $where = self::getWhere();

        $sql = "
        SELECT 
            count(*) as total_sexo
            ,CASE
                WHEN f.sexo = 'M' THEN 'Masculino'
                WHEN f.sexo = 'F' THEN 'Feminino'
                ELSE 'Não Definido'
            END as sexo
        FROM pmieducar.matricula m
        LEFT JOIN pmieducar.aluno a on a.cod_aluno = m.ref_cod_aluno
        LEFT JOIN cadastro.fisica f on a.ref_idpes = f.idpes
        $where
        GROUP BY f.sexo
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getCorRaca()
    {
        $where = self::getWhere();

        $sql = "
        SELECT 
            count(*) as total_raca,
			CASE
                WHEN r.nm_raca is null THEN 'Não Definido'
                ELSE r.nm_raca
            END as raca
        FROM pmieducar.matricula m
        LEFT JOIN pmieducar.aluno a on a.cod_aluno = m.ref_cod_aluno
        LEFT JOIN cadastro.fisica_raca fr on a.ref_idpes = fr.ref_idpes
        LEFT JOIN cadastro.raca r on fr.ref_cod_raca = r.cod_raca
        $where
        GROUP BY r.nm_raca
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getLocalizacao()
    {
        $where = self::getWhere();

        $sql = "
        SELECT 
            count(*) as total_localizacao,
            CASE
                WHEN b.zona_localizacao is null THEN 'Não Definido'
                WHEN b.zona_localizacao = 1 THEN 'Urbana'
                ELSE 'Rural'
            END as zona
        FROM pmieducar.matricula m
        LEFT JOIN pmieducar.aluno a on a.cod_aluno = m.ref_cod_aluno
        LEFT JOIN cadastro.endereco_pessoa ep ON ep.idpes = a.ref_idpes
        LEFT JOIN  public.bairro b ON ep.idbai = b.idbai
        $where
        GROUP BY b.zona_localizacao
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getTransporte()
    {
        $where = self::getWhere();

        $sql = "
        SELECT 
            count(*) as total_transporte,
            CASE
                WHEN transporte_aluno.responsavel is null THEN 'Não Definido'
                WHEN transporte_aluno.responsavel = 0 THEN 'Não Utiliza'
                ELSE 'Utiliza'
            END as transporte
        FROM pmieducar.matricula m
        LEFT JOIN pmieducar.aluno a on a.cod_aluno = m.ref_cod_aluno
        LEFT JOIN modules.transporte_aluno ON (a.cod_aluno = transporte_aluno.aluno_id)
        $where
        GROUP BY transporte_aluno.responsavel
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getSubCabecalho()
    {

    }

}