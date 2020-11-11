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
        $arrDados['docentes_total']  = self::getDocentesTotal();

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
        $situacao = $this->args['situacao'] ?: 0;

        return "
            SELECT 
                  COUNT(*)::numeric  
                FROM 
			    pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE 
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON TRUE 
                AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON TRUE 
                AND escola_curso.ativo = 1 
                AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON TRUE 
                AND curso.cod_curso = escola_curso.ref_cod_curso 
                AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON TRUE 
                AND escola_serie.ativo = 1 
                AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON TRUE 
                AND serie.cod_serie = escola_serie.ref_cod_serie 
                AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON TRUE 
                AND turma.ref_ref_cod_escola = escola.cod_escola 
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso 
                AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie 
                AND turma.ativo = 1
            INNER JOIN pmieducar.matricula_turma ON TRUE 
                AND matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON TRUE 
                AND matricula.cod_matricula = matricula_turma.ref_cod_matricula 
                AND matricula.ativo = 1
            INNER JOIN relatorio.view_situacao ON TRUE 
                AND view_situacao.cod_matricula = matricula.cod_matricula 
                AND view_situacao.cod_turma = turma.cod_turma
                AND (CASE WHEN {$situacao} = 9 THEN TRUE ELSE view_situacao.cod_situacao = {$situacao} END)            
                AND matricula_turma.sequencial = view_situacao.sequencial
            INNER JOIN pmieducar.aluno ON TRUE 
                AND pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
            INNER JOIN cadastro.fisica ON TRUE 
                AND cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
			WHERE escola_ano_letivo.ano = {$ano}
			    AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano";
    }

    private function getBase($select = "*", $join = "", $group = "", $ordem = "")
    {
        $ano      = $this->args['ano'];
        $situacao = $this->args['situacao'] ?: 0;
        $consultaTotalAluno    = self::getTotal();
        return "
        SELECT 
            descricao,quantidade,round((quantidade*100/($consultaTotalAluno))::numeric,2) AS porcentagem 
        FROM (
            SELECT 
                  {$select} as descricao,
                  COUNT(*)::numeric as quantidade
                FROM 
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE 
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON TRUE 
                AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON TRUE 
                AND escola_curso.ativo = 1 
                AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON TRUE 
                AND curso.cod_curso = escola_curso.ref_cod_curso 
                AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON TRUE 
                AND escola_serie.ativo = 1 
                AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON TRUE 
                AND serie.cod_serie = escola_serie.ref_cod_serie 
                AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON TRUE 
                AND turma.ref_ref_cod_escola = escola.cod_escola 
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso 
                AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie 
                AND turma.ativo = 1
            INNER JOIN pmieducar.matricula_turma ON TRUE 
                AND matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON TRUE 
                AND matricula.cod_matricula = matricula_turma.ref_cod_matricula 
                AND matricula.ativo = 1
            INNER JOIN relatorio.view_situacao ON TRUE 
                AND view_situacao.cod_matricula = matricula.cod_matricula 
                AND view_situacao.cod_turma = turma.cod_turma
                AND (CASE WHEN {$situacao} = 9 THEN TRUE ELSE view_situacao.cod_situacao = {$situacao} END)            
                AND matricula_turma.sequencial = view_situacao.sequencial
            INNER JOIN pmieducar.aluno ON TRUE 
                AND pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
            INNER JOIN cadastro.fisica ON TRUE 
                AND cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
            {$join}
            WHERE escola_ano_letivo.ano = {$ano}
                AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
            GROUP BY {$group}
            ORDER BY {$ordem}
        ) as t
        ";
    }

    public function getNumeroMatricula()
    {
        $sql = self::getBase(" NULLIF(curso.nm_curso,'Não Definido') ", "","curso.nm_curso","curso.nm_curso" );
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getSexo()
    {
        $case = "CASE
                    WHEN fisica.sexo = 'M' THEN 'Masculino'
                    WHEN fisica.sexo = 'F' THEN 'Feminino'
                    ELSE 'Não Definido'
                END";
        $sql = self::getBase($case, "","fisica.sexo","fisica.sexo" );

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getCorRaca()
    {
        $join = " LEFT JOIN cadastro.fisica_raca on aluno.ref_idpes = fisica_raca.ref_idpes 
        LEFT JOIN cadastro.raca raca on fisica_raca.ref_cod_raca = raca.cod_raca \n";
        $case = "CASE
                    WHEN raca.nm_raca is null THEN 'Não Definido'
                    ELSE raca.nm_raca
                END";
        $sql = self::getBase($case, $join,"raca.nm_raca","raca.nm_raca" );

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getLocalizacao()
    {
        $case = "CASE
                    WHEN fisica.zona_localizacao_censo is null THEN 'Não Definido'
                    WHEN fisica.zona_localizacao_censo = 1 THEN 'Urbana'
                    WHEN fisica.zona_localizacao_censo = 2 THEN 'Rural'
                    ELSE 'Não Definido'
                END";
        $sql = self::getBase($case, "","fisica.zona_localizacao_censo","fisica.zona_localizacao_censo" );

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getTransporte()
    {
        $join = " LEFT JOIN modules.transporte_aluno ON (aluno.cod_aluno = transporte_aluno.aluno_id) \n";
        $case = "CASE
                    WHEN transporte_aluno.responsavel is null THEN 'Não Definido'
                    WHEN transporte_aluno.responsavel = 0 THEN 'Não Utiliza'
                    ELSE 'Utiliza'
                END";
        $sql = self::getBase($case, $join,"transporte_aluno.responsavel","transporte_aluno.responsavel" );

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getDocentes()
    {
        $ano      = $this->args['ano'];
        $vinculo  = $this->args['vinculo'];

        $sqltotal = "
        SELECT 
            SUM(quantidade)::numeric AS quantidade 
        FROM (
            SELECT
				   1 as quantidade
			FROM 
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE 
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON TRUE 
                AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON TRUE 
                AND escola_curso.ativo = 1 
                AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON TRUE 
                AND curso.cod_curso = escola_curso.ref_cod_curso 
                AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON TRUE 
                AND escola_serie.ativo = 1 
                AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON TRUE 
                AND serie.cod_serie = escola_serie.ref_cod_serie 
                AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON TRUE 
                AND turma.ref_ref_cod_escola = escola.cod_escola 
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso 
                AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie 
                AND turma.ativo = 1
            INNER JOIN modules.professor_turma ON TRUE 
                AND professor_turma.turma_id = turma.cod_turma
                AND professor_turma.funcao_exercida IN(1,5,6)
                AND professor_turma.ano = {$ano}
                AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE professor_turma.tipo_vinculo = {$vinculo} END)
            INNER JOIN pmieducar.servidor ON TRUE 
                AND servidor.cod_servidor = professor_turma.servidor_id
                AND servidor.ativo = 1
            INNER JOIN cadastro.pessoa ON TRUE 
                AND pessoa.idpes = servidor.cod_servidor
            INNER JOIN cadastro.fisica ON TRUE 
                AND fisica.idpes = servidor.cod_servidor
            INNER JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
			INNER JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
            WHERE escola_ano_letivo.ano = {$ano}
            GROUP BY
            servidor.cod_servidor,curso.nm_curso
                ) as u
        ";

        $sql = "   
            SELECT DISTINCT
                   nm_curso as descricao
				   ,COUNT(*)::numeric as quantidade
				   ,round((COUNT(*)::numeric*100/({$sqltotal}))::numeric,2) AS porcentagem 
			FROM (
			    SELECT servidor.cod_servidor,curso.nm_curso
                FROM 
                    pmieducar.instituicao
                INNER JOIN pmieducar.escola ON TRUE 
                    AND escola.ref_cod_instituicao = instituicao.cod_instituicao
                INNER JOIN pmieducar.escola_ano_letivo ON TRUE 
                    AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
                INNER JOIN pmieducar.escola_curso ON TRUE 
                    AND escola_curso.ativo = 1 
                    AND escola_curso.ref_cod_escola = escola.cod_escola
                INNER JOIN pmieducar.curso ON TRUE 
                    AND curso.cod_curso = escola_curso.ref_cod_curso 
                    AND curso.ativo = 1
                INNER JOIN pmieducar.escola_serie ON TRUE 
                    AND escola_serie.ativo = 1 
                    AND escola_serie.ref_cod_escola = escola.cod_escola
                INNER JOIN pmieducar.serie ON TRUE 
                    AND serie.cod_serie = escola_serie.ref_cod_serie 
                    AND serie.ativo = 1
                INNER JOIN pmieducar.turma ON TRUE 
                    AND turma.ref_ref_cod_escola = escola.cod_escola 
                    AND turma.ref_cod_curso = escola_curso.ref_cod_curso 
                    AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie 
                    AND turma.ativo = 1
                INNER JOIN modules.professor_turma ON TRUE 
                    AND professor_turma.turma_id = turma.cod_turma
                    AND professor_turma.funcao_exercida IN(1,5,6)
                    AND professor_turma.ano = {$ano}
                    AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE professor_turma.tipo_vinculo = {$vinculo} END)
                INNER JOIN pmieducar.servidor ON TRUE 
                    AND servidor.cod_servidor = professor_turma.servidor_id
                    AND servidor.ativo = 1
                INNER JOIN cadastro.pessoa ON TRUE 
                    AND pessoa.idpes = servidor.cod_servidor
                INNER JOIN cadastro.fisica ON TRUE 
                    AND fisica.idpes = servidor.cod_servidor
                WHERE escola_ano_letivo.ano = {$ano}            
                GROUP BY servidor.cod_servidor,curso.nm_curso
                ORDER BY servidor.cod_servidor,curso.nm_curso
            ) AS t
            GROUP BY nm_curso
			ORDER BY nm_curso
        ";
        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    public function getDocentesTotal()
    {
        $ano      = $this->args['ano'];
        $vinculo  = $this->args['vinculo'];

        $sql = "
        SELECT 
            SUM(quantidade)::numeric AS quantidade 
        FROM (
            SELECT
				   1 as quantidade
			FROM 
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE 
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON TRUE 
                AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON TRUE 
                AND escola_curso.ativo = 1 
                AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON TRUE 
                AND curso.cod_curso = escola_curso.ref_cod_curso 
                AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON TRUE 
                AND escola_serie.ativo = 1 
                AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON TRUE 
                AND serie.cod_serie = escola_serie.ref_cod_serie 
                AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON TRUE 
                AND turma.ref_ref_cod_escola = escola.cod_escola 
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso 
                AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie 
                AND turma.ativo = 1
            INNER JOIN modules.professor_turma ON TRUE 
                AND professor_turma.turma_id = turma.cod_turma
                AND professor_turma.funcao_exercida IN(1,5,6)
                AND professor_turma.ano = {$ano}
                AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE professor_turma.tipo_vinculo = {$vinculo} END)
            INNER JOIN pmieducar.servidor ON TRUE 
                AND servidor.cod_servidor = professor_turma.servidor_id
                AND servidor.ativo = 1
            INNER JOIN cadastro.pessoa ON TRUE 
                AND pessoa.idpes = servidor.cod_servidor
            INNER JOIN cadastro.fisica ON TRUE 
                AND fisica.idpes = servidor.cod_servidor
            INNER JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
			INNER JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
            WHERE escola_ano_letivo.ano = {$ano}
            GROUP BY
            pessoa.idpes
                ) as u
        ";
        $docente = Portabilis_Utils_Database::fetchPreparedQuery($sql);
        return isset($docente[0]['quantidade'])?($docente[0]['quantidade']):0;
    }
}