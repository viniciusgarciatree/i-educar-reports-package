<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsByDataReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-by-data';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
//        $this->addRequiredArg('escola');
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
        $escola = $this->args['escola'] ?: 0;
        $aluno = $this->args['aluno'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: date('Y');

        $return = "
           SELECT  aluno.cod_aluno AS cod_aluno,
		        fcn_upper(pessoa.nome) AS nome_aluno,
		        to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
		        instituicao.cidade,
                logradouro.nome as logradouro,
                bairro.nome as bairro,
                endpes.numero,
                endpes.complemento,
                endpes.cep,
                aluno.aluno_estado_id AS serie_ciasc,
                curso.nm_curso AS nome_curso,
                turma.nm_turma AS nome_turma,
                serie.nm_serie AS nome_serie,
                turma.cod_turma AS cod_turma,
                escola.cod_escola AS cod_escola,
                juridica.fantasia AS nm_escola,
                turma_turno.nome AS periodo,
                (
                    SELECT
                        infra_predio.nm_predio
                    FROM
                        pmieducar.infra_predio_comodo,
                        pmieducar.infra_comodo_funcao,
                        pmieducar.infra_predio
                    WHERE TRUE
                        AND infra_comodo_funcao.cod_infra_comodo_funcao = infra_predio_comodo.ref_cod_infra_comodo_funcao
                        AND infra_comodo_funcao.ref_cod_escola = escola.cod_escola
                        AND infra_predio.cod_infra_predio = infra_predio_comodo.ref_cod_infra_predio
                        AND infra_predio.ref_cod_escola = escola.cod_escola
                        AND infra_predio.ativo = 1
                        AND infra_predio_comodo.cod_infra_predio_comodo = turma.ref_cod_infra_predio_comodo
                ) AS predio,
                (
                    SELECT nm_comodo
                    FROM
                        pmieducar.infra_predio_comodo,
                        pmieducar.infra_comodo_funcao,
                        pmieducar.infra_predio
                    WHERE TRUE
                        AND infra_comodo_funcao.cod_infra_comodo_funcao = infra_predio_comodo.ref_cod_infra_comodo_funcao
                        AND infra_comodo_funcao.ref_cod_escola = escola.cod_escola
                        AND infra_predio.cod_infra_predio = infra_predio_comodo.ref_cod_infra_predio
                        AND infra_predio.ref_cod_escola = escola.cod_escola
                        AND infra_predio.ativo = 1
                        AND infra_predio_comodo.cod_infra_predio_comodo = turma.ref_cod_infra_predio_comodo
                ) AS sala,
                view_situacao.texto_situacao AS situacao,pessoa.email,
                trim(leading ' ' from concat(telefone_mae.ddd,'', to_char(telefone_mae.fone, '99999-9999'))::text) as mae_telefone,
                trim(leading ' ' from concat(celular_mae.ddd,'',to_char(celular_mae.fone, '9 9999-9999'))::text) as mae_celular,
                trim(leading ' ' from concat(telefone_pai.ddd,'',to_char(telefone_pai.fone, '99999-9999'))::text) as pai_telefone,
                trim(leading ' ' from concat(celular_pai.ddd,'',to_char(celular_pai.fone, '9 9999-9999'))::text) as pai_celular
                FROM
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE  AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON TRUE  AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON TRUE  AND escola_curso.ativo = 1 AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON TRUE  AND curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON TRUE  AND escola_serie.ativo = 1 AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON TRUE  AND serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON TRUE  AND turma.ref_ref_cod_escola = escola.cod_escola AND turma.ref_cod_curso = escola_curso.ref_cod_curso AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie AND turma.ativo = 1
            INNER JOIN pmieducar.matricula_turma ON TRUE  AND matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON TRUE  AND matricula.cod_matricula = matricula_turma.ref_cod_matricula AND matricula.ativo = 1
            INNER JOIN relatorio.view_situacao ON TRUE  AND view_situacao.cod_matricula = matricula.cod_matricula AND view_situacao.cod_turma = turma.cod_turma AND matricula_turma.sequencial = view_situacao.sequencial
            LEFT JOIN pmieducar.turma_turno ON TRUE  AND turma_turno.id = turma.turma_turno_id AND turma.cod_turma = matricula_turma.ref_cod_turma
            INNER JOIN pmieducar.aluno ON TRUE  AND pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
            INNER JOIN cadastro.fisica ON TRUE  AND cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
            LEFT JOIN cadastro.pessoa ON TRUE  AND cadastro.pessoa.idpes = cadastro.fisica.idpes
            LEFT JOIN cadastro.juridica ON TRUE  AND juridica.idpes = escola.ref_idpes
            LEFT JOIN cadastro.documento ON TRUE  AND documento.idpes = fisica.idpes
            LEFT JOIN modules.educacenso_cod_aluno ON TRUE  AND educacenso_cod_aluno.cod_aluno = aluno.cod_aluno
            LEFT JOIN cadastro.endereco_pessoa endpes ON endpes.idpes = aluno.ref_idpes
			LEFT JOIN public.logradouro ON logradouro.idlog = endpes.idlog
			LEFT JOIN public.bairro ON bairro.idbai = endpes.idbai			
            LEFT JOIN cadastro.fone_pessoa endpes_mae ON endpes_mae.idpes = fisica.idpes_mae
            LEFT JOIN cadastro.fone_pessoa endpes_pai ON endpes_pai.idpes = fisica.idpes_pai
            LEFT JOIN cadastro.fone_pessoa AS fone_mae ON fone_mae.idpes = fisica.idpes_mae
            LEFT JOIN cadastro.fone_pessoa AS fone_pai ON fone_pai.idpes = fisica.idpes_pai
            LEFT JOIN cadastro.fone_pessoa telefone_mae ON TRUE AND telefone_mae.idpes = fisica.idpes_mae  AND telefone_mae.tipo = 1
            LEFT JOIN cadastro.fone_pessoa celular_mae ON TRUE  AND celular_mae.idpes = fisica.idpes_mae AND celular_mae.tipo = 3
			LEFT JOIN cadastro.fone_pessoa telefone_pai ON TRUE AND telefone_pai.idpes = fisica.idpes_pai  AND telefone_pai.tipo = 1
            LEFT JOIN cadastro.fone_pessoa celular_pai ON TRUE  AND celular_pai.idpes = fisica.idpes_pai AND celular_pai.tipo = 3
            WHERE (CASE WHEN {$escola} = 0 THEN TRUE ELSE {$escola} = escola.cod_escola END)
              AND (CASE WHEN {$aluno} = 0 THEN TRUE ELSE {$aluno} = aluno.cod_aluno END)
              AND (CASE WHEN {$ano} = 0 THEN TRUE ELSE {$ano} = escola_ano_letivo.ano END)
              AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE {$curso} = curso.cod_curso END)
              AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE {$serie} = serie.cod_serie END)
              AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE {$turma} = turma.cod_turma END)
            GROUP BY
                nm_escola,
                nome_curso,
                nome_serie,
                nome_turma,
                turma.cod_turma,
                nome_aluno,
                aluno.cod_aluno,
                fisica.data_nasc,
                escola.cod_escola,
                turma_turno.nome,
                view_situacao.texto_situacao,
                instituicao.cidade,
                logradouro.nome,bairro.nome,endpes.numero,endpes.complemento,endpes.cep,
                pessoa.email,
                telefone_mae.ddd,telefone_mae.fone,celular_mae.ddd,celular_mae.fone,
                telefone_pai.ddd,telefone_pai.fone,celular_pai.ddd,celular_pai.fone
              ORDER BY
                nm_escola,
                nome_curso,
                nome_serie,
                nome_turma,
                cod_turma,
                instituicao.cidade,
                logradouro.nome,
                bairro.nome,
                endpes.numero,
                endpes.complemento,
                endpes.cep,
                nome_aluno
        ";
        return $return;
    }
}
