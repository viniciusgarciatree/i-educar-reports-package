<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class MonthlyClosingServantsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'monthly-closing-servants';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('data_inicial');
        $this->addRequiredArg('data_final');
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
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $funcao = $this->args['funcao'] ?: 0;
        $vinculo = $this->args['vinculo'] ?: 0;
//        $periodo = $this->args['periodo'] ?: 0;
//        $nao_emitir_afastados = $this->args['nao_emitir_afastados'];
        $dataInicial = $this->args['data_inicial'] ?: 0;
        $dataFinal = $this->args['data_final'] ?: 0;

        return "SELECT 
                     distinct
                     cod_escola,
                     nome_escola,
                     cod_servidor,
                     servidor,
                     nm_funcao,
                     nm_vinculo,
                     horario_trabalho,
                     faltas::varchar,
                     afastamento::varchar,
                     string_agg(case when aluno = '0' then '' else aluno end, '\n') OVER ( PARTITION BY cod_escola, nome_escola, cod_servidor, servidor, nm_funcao, nm_vinculo) as aluno,
                     string_agg(nm_turma, '\n') OVER ( PARTITION BY cod_escola, nome_escola, cod_servidor, servidor, nm_funcao, nm_vinculo) AS turma
                     FROM (
                    SELECT
                     cod_escola,
                     pe.nome as nome_escola,
                     cod_servidor,
                     pessoa.nome as servidor,
                     nm_funcao,
                     nm_vinculo,
                     (servidor_alocacao.hora_inicial||' às '||servidor_alocacao.hora_final) as horario_trabalho,
                    (select count(*) from pmieducar.falta_atraso where servidor.cod_servidor = falta_atraso.ref_cod_servidor and 
                    falta_atraso.data_falta_atraso >= '{$dataInicial}' and falta_atraso.data_falta_atraso <= '{$dataFinal}') as faltas,
                    (select count(*) from pmieducar.servidor_afastamento where servidor.cod_servidor = servidor_afastamento.ref_cod_servidor and 
                    servidor_afastamento.data_saida >= '{$dataInicial}' and servidor_afastamento.data_saida <= '{$dataFinal}') as afastamento,
                    (select count(*)::varchar as aluno from pmieducar.matricula_turma INNER JOIN pmieducar.matricula ON TRUE 
                                    AND matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                    AND matricula.ativo = 1 where matricula_turma.ref_cod_turma = turma.cod_turma),
                    nm_turma
                    FROM pmieducar.instituicao
                    INNER JOIN pmieducar.escola ON TRUE AND escola.ref_cod_instituicao = instituicao.cod_instituicao
                    LEFT OUTER JOIN cadastro.pessoa pe ON escola.ref_idpes = pe.idpes
                    
                    LEFT OUTER JOIN pmieducar.servidor_alocacao ON servidor_alocacao.ref_cod_escola = escola.cod_escola
                    LEFT OUTER JOIN pmieducar.servidor on servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
                    LEFT OUTER JOIN cadastro.pessoa on servidor.cod_servidor = pessoa.idpes
                    LEFT OUTER JOIN pmieducar.servidor_funcao ON (servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao)
                    LEFT OUTER JOIN portal.funcionario ON (servidor.cod_servidor = funcionario.ref_cod_pessoa_fj)
                    LEFT OUTER JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
                    LEFT OUTER JOIN pmieducar.funcao ON (servidor_funcao.ref_cod_funcao = funcao.cod_funcao)
                    
                    LEFT OUTER JOIN modules.professor_turma ON professor_turma.servidor_id = servidor.cod_servidor AND funcao.professor = 1
                    LEFT OUTER JOIN pmieducar.turma ON professor_turma.turma_id = turma.cod_turma
                    where 
                    (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
                    AND (CASE WHEN {$funcao} = 0 THEN TRUE ELSE funcao.cod_funcao = {$funcao} END)
                    AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE funcionario_vinculo.cod_funcionario_vinculo = {$vinculo} END)
                    AND servidor_alocacao.ano = {$ano}  
                    AND (professor_turma.ano = {$ano} or professor_turma.ano is null)
                    order by nome_escola, servidor, nm_turma
                    ) AS TMP 
                    order by nome_escola, servidor";
    }
}
