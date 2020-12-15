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

        $arrDados['matriculas']  = self::getNumeroMatricula();
        $arrDados['sexo']        = self::getSexo();
        $arrDados['cor_raca']    = self::getCorRaca();
        $arrDados['localizacao'] = self::getLocalizacao();
        $arrDados['transporte']  = self::getTransporte();
        $arrDados['docentes']  = self::getDocentes();
        $arrDados['docentes_total'] = self::getDocentesTotal();
        $arrDados['cod_escola'] = $this->args['escola'] == 0 ? 0 : $this->args['escola'];

        $queryHeaderReport = $this->getSqlHeaderReport();

        return array_merge(
            [
                'main'   => $arrDados,
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    private function getTotal()
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] == 9 ? "" : " AND matricula.aprovado = " . $this->args['situacao'] . " ";
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";

        return "
SELECT
  count(*)::numeric
FROM (
  SELECT
	DISTINCT aluno.cod_aluno
	FROM pmieducar.aluno
	INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
		WHERE aluno.ativo = 1
	AND matricula.ano = {$ano}
	AND matricula.ativo = 1
	{$situacao}
	AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
	GROUP BY aluno.cod_aluno
) AS G
";
    }

    public function getNumeroMatricula()
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] == 9 ? "" : " AND matricula.aprovado = " . $this->args['situacao'] . " ";
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";

        $consultaTotalAluno    = self::getTotal();
        $sql = "
SELECT
    descricao,quantidade::numeric,round((quantidade*100/($consultaTotalAluno))::numeric,2) AS porcentagem
FROM (
    SELECT
        descricao,COUNT(*)::numeric as quantidade
    FROM (
        SELECT
            DISTINCT aluno.cod_aluno
            ,curso.nm_curso as descricao
        FROM pmieducar.aluno
        INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
        LEFT JOIN pmieducar.matricula_turma ON matricula.cod_matricula = matricula_turma.ref_cod_matricula
        LEFT JOIN pmieducar.turma ON matricula_turma.ref_cod_turma = turma.cod_turma
        LEFT JOIN pmieducar.escola_curso ON turma.ref_cod_curso = escola_curso.ref_cod_curso
        LEFT JOIN pmieducar.curso ON curso.cod_curso = escola_curso.ref_cod_curso
        WHERE aluno.ativo = 1
            AND matricula.ano = {$ano}
            AND matricula.ativo = 1
            AND matricula_turma.ativo = 1
            AND turma.ativo = 1
            AND turma.ano = {$ano}
            AND escola_curso.ativo = 1
            AND curso.ativo = 1
            {$situacao}
            AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
        GROUP BY aluno.cod_aluno,curso.nm_curso
    ) AS t
        GROUP BY descricao
) AS t
        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getSexo()
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] == 9 ? "" : " AND matricula.aprovado = " . $this->args['situacao'] . " ";
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";
        $consultaTotalAluno    = self::getTotal();
        $sql = "
SELECT
    descricao,quantidade::numeric,round((quantidade*100/($consultaTotalAluno))::numeric,2) AS porcentagem
FROM (
    SELECT
        descricao,COUNT(*)::numeric as quantidade
    FROM (
        SELECT
        DISTINCT aluno.cod_aluno
        ,CASE
            WHEN fisica.sexo = 'M' THEN 'Masculino'
            WHEN fisica.sexo = 'F' THEN 'Feminino'
            ELSE 'Não Definido'
        END as descricao
        FROM pmieducar.aluno
        INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
        INNER JOIN cadastro.fisica ON cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
        WHERE aluno.ativo = 1
        AND matricula.ano = {$ano}
        AND matricula.ativo = 1
        {$situacao}
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
        GROUP BY aluno.cod_aluno,fisica.sexo
    ) AS t
    GROUP BY descricao
) AS t
        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getCorRaca()
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] == 9 ? "" : " AND matricula.aprovado = " . $this->args['situacao'] . " ";
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";
        $consultaTotalAluno    = self::getTotal();
        $sql = "
SELECT
    descricao,quantidade::numeric,round((quantidade*100/($consultaTotalAluno))::numeric,2) AS porcentagem
FROM (
    SELECT
        descricao,COUNT(*)::numeric as quantidade
    FROM (
        SELECT
        DISTINCT aluno.cod_aluno
        ,CASE
            WHEN raca.nm_raca is null THEN 'Não Definido'
            ELSE raca.nm_raca
        END as descricao
        FROM pmieducar.aluno
        INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
        INNER JOIN cadastro.fisica ON cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
        LEFT JOIN cadastro.fisica_raca on aluno.ref_idpes = fisica_raca.ref_idpes
        LEFT JOIN cadastro.raca raca on fisica_raca.ref_cod_raca = raca.cod_raca
        WHERE aluno.ativo = 1
        AND matricula.ano = {$ano}
        AND matricula.ativo = 1
        {$situacao}
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
        GROUP BY aluno.cod_aluno,raca.nm_raca
    ) AS t
    GROUP BY descricao
) AS t
        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getLocalizacao()
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] == 9 ? "" : " AND matricula.aprovado = " . $this->args['situacao'] . " ";
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";
        $consultaTotalAluno    = self::getTotal();
        $sql = "
SELECT
    descricao,quantidade::numeric,round((quantidade*100/($consultaTotalAluno))::numeric,2) AS porcentagem
FROM (
    SELECT
        descricao,COUNT(*)::numeric as quantidade
    FROM (
        SELECT
        DISTINCT aluno.cod_aluno
        ,CASE
            WHEN fisica.zona_localizacao_censo is null THEN 'Não Definido'
            WHEN fisica.zona_localizacao_censo = 1 THEN 'Urbana'
            WHEN fisica.zona_localizacao_censo = 2 THEN 'Rural'
            ELSE 'Não Definido'
        END as descricao
        FROM pmieducar.aluno
        INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
        INNER JOIN cadastro.fisica ON cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
        WHERE aluno.ativo = 1
        AND matricula.ano = {$ano}
        AND matricula.ativo = 1
        {$situacao}
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
        GROUP BY aluno.cod_aluno,fisica.zona_localizacao_censo
    ) AS t
    GROUP BY descricao
) AS t
        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getTransporte()
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] == 9 ? "" : " AND matricula.aprovado = " . $this->args['situacao'] . " ";
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";
        $consultaTotalAluno    = self::getTotal();
        $sql = "
SELECT
    descricao,quantidade::numeric,round((quantidade*100/($consultaTotalAluno))::numeric,2) AS porcentagem
FROM (
    SELECT
        descricao,COUNT(*)::numeric as quantidade
    FROM (
        SELECT
        DISTINCT aluno.cod_aluno
        ,CASE
            WHEN transporte_aluno.responsavel is null THEN 'Não Definido'
            WHEN transporte_aluno.responsavel = 0 THEN 'Não Utiliza'
            ELSE 'Utiliza'
        END as descricao
        FROM pmieducar.aluno
        INNER JOIN pmieducar.matricula ON matricula.ref_cod_aluno = aluno.cod_aluno
        LEFT JOIN modules.transporte_aluno ON (aluno.cod_aluno = transporte_aluno.aluno_id)
        WHERE aluno.ativo = 1
        AND matricula.ano = {$ano}
        AND matricula.ativo = 1
        {$situacao}
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
        GROUP BY aluno.cod_aluno,transporte_aluno.responsavel
    ) AS t
    GROUP BY descricao
) AS t
        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getDocentes()
    {
        $ano      = $this->args['ano'];
        $vinculo  = $this->args['vinculo'];
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";

        $consultaTotal = "
SELECT
  count(*)::numeric
FROM (
    SELECT
        DISTINCT servidor.cod_servidor,curso.nm_curso as descricao
    FROM
    pmieducar.servidor
    INNER JOIN modules.professor_turma ON servidor.cod_servidor = professor_turma.servidor_id
        AND professor_turma.funcao_exercida IN(1,5,6)
        AND professor_turma.ano = {$ano}
        AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE professor_turma.tipo_vinculo = {$vinculo} END)
    INNER JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
    INNER JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
    INNER JOIN pmieducar.turma ON professor_turma.turma_id = turma.cod_turma AND turma.ano = {$ano} AND turma.ativo = 1
    INNER JOIN pmieducar.escola_curso ON turma.ref_cod_curso = escola_curso.ref_cod_curso AND escola_curso.ativo = 1
    INNER JOIN pmieducar.curso ON curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1
    WHERE servidor_alocacao.ano = {$ano}
    AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola_curso.ref_cod_escola = {$escola} END)
    AND servidor_alocacao.ativo = 1
    GROUP BY servidor.cod_servidor,curso.nm_curso
) AS t
        ";

        $sql = "
SELECT
    descricao,quantidade::numeric,round((quantidade*100/($consultaTotal))::numeric,2) AS porcentagem
FROM (
    SELECT
        descricao,COUNT(*)::numeric as quantidade
    FROM (
        SELECT
            DISTINCT servidor.cod_servidor,curso.nm_curso as descricao
        FROM
        pmieducar.servidor
        INNER JOIN modules.professor_turma ON servidor.cod_servidor = professor_turma.servidor_id
            AND professor_turma.funcao_exercida IN(1,5,6)
            AND professor_turma.ano = {$ano}
            AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE professor_turma.tipo_vinculo = {$vinculo} END)
        INNER JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
        INNER JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
        INNER JOIN pmieducar.turma ON professor_turma.turma_id = turma.cod_turma AND turma.ano = {$ano} AND turma.ativo = 1
        INNER JOIN pmieducar.escola_curso ON turma.ref_cod_curso = escola_curso.ref_cod_curso AND escola_curso.ativo = 1
        INNER JOIN pmieducar.curso ON curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1
        WHERE servidor_alocacao.ano = {$ano}
        AND servidor_alocacao.ativo = 1
        AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola_curso.ref_cod_escola = {$escola} END)
        GROUP BY servidor.cod_servidor,curso.nm_curso
      ) AS t
    GROUP BY descricao
) AS t
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getDocentesTotal()
    {
        $ano      = $this->args['ano'];
        $vinculo  = $this->args['vinculo'];
        $escola   = $this->args['escola'] == 0 ? " 0 " : " " . $this->args['escola'] . " ";

        $consultaTotal = "
SELECT
  count(*)::numeric
FROM (
    SELECT
        DISTINCT servidor.cod_servidor,curso.nm_curso as descricao
    FROM
    pmieducar.servidor
    INNER JOIN modules.professor_turma ON servidor.cod_servidor = professor_turma.servidor_id
        AND professor_turma.funcao_exercida IN(1,5,6)
        AND professor_turma.ano = {$ano}
        AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE professor_turma.tipo_vinculo = {$vinculo} END)
    INNER JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
    INNER JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
    INNER JOIN pmieducar.turma ON professor_turma.turma_id = turma.cod_turma AND turma.ano = {$ano} AND turma.ativo = 1
    INNER JOIN pmieducar.escola_curso ON turma.ref_cod_curso = escola_curso.ref_cod_curso AND escola_curso.ativo = 1
    INNER JOIN pmieducar.curso ON curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1
    WHERE servidor_alocacao.ano = {$ano}
    AND servidor_alocacao.ativo = 1
    AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola_curso.ref_cod_escola = {$escola} END)
    GROUP BY servidor.cod_servidor,curso.nm_curso
) AS t
        ";
        $docente = Portabilis_Utils_Database::fetchPreparedQuery($consultaTotal);
        return isset($docente[0]['quantidade'])?($docente[0]['quantidade']):0;
    }
}
