<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsByRelativesReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-by-relatives';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
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
        $escola = $this->args['escola'] ?: 0;
        $aluno = $this->args['aluno'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: date('Y');

        $return = "
           SELECT
  cod_escola,
  aluno.cod_aluno                         AS cod_aluno,
  fcn_upper(pessoa.nome)                  AS nome_aluno,
  to_char(fisica.data_nasc, 'dd/mm/yyyy') AS data_nasc,
  coalesce(juridica.fantasia,'')          AS nm_escola,
  coalesce(curso.nm_curso,'')             AS nome_curso,
  turma.cod_turma,
  coalesce(turma.nm_turma,'')             AS nome_turma,
  coalesce(serie.nm_serie,'')             AS nome_serie,
  coalesce(turma_turno.nome,'')           AS periodo,
  coalesce((
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
           ), '')                         AS sala,
  coalesce(pes_pai.nome, '')              as pai_nome,
  coalesce(pes_mae.nome, '')              as mae_nome
FROM
  pmieducar.instituicao
  INNER JOIN pmieducar.escola ON TRUE AND escola.ref_cod_instituicao = instituicao.cod_instituicao
  INNER JOIN pmieducar.escola_ano_letivo
    ON TRUE AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
  INNER JOIN pmieducar.escola_curso
    ON TRUE AND escola_curso.ativo = 1 AND escola_curso.ref_cod_escola = escola.cod_escola
  INNER JOIN pmieducar.curso ON TRUE AND curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1
  INNER JOIN pmieducar.escola_serie
    ON TRUE AND escola_serie.ativo = 1 AND escola_serie.ref_cod_escola = escola.cod_escola
  INNER JOIN pmieducar.serie ON TRUE AND serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1
  INNER JOIN pmieducar.turma ON TRUE AND turma.ref_ref_cod_escola = escola.cod_escola AND
                                turma.ref_cod_curso = escola_curso.ref_cod_curso AND
                                turma.ref_ref_cod_serie = escola_serie.ref_cod_serie AND turma.ativo = 1
  INNER JOIN pmieducar.matricula_turma ON TRUE AND matricula_turma.ref_cod_turma = turma.cod_turma
  INNER JOIN pmieducar.matricula
    ON TRUE AND matricula.cod_matricula = matricula_turma.ref_cod_matricula AND matricula.ativo = 1
  LEFT JOIN pmieducar.turma_turno
    ON TRUE AND turma_turno.id = turma.turma_turno_id AND turma.cod_turma = matricula_turma.ref_cod_turma
  INNER JOIN pmieducar.aluno ON TRUE AND pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
  INNER JOIN cadastro.fisica ON TRUE AND cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
  LEFT JOIN cadastro.pessoa ON TRUE AND cadastro.pessoa.idpes = cadastro.fisica.idpes
  LEFT JOIN cadastro.juridica ON TRUE AND juridica.idpes = escola.ref_idpes
  LEFT JOIN cadastro.pessoa pes_mae ON pes_mae.idpes = fisica.idpes_mae
  LEFT JOIN cadastro.pessoa pes_pai ON pes_pai.idpes = fisica.idpes_pai
WHERE (CASE WHEN {$escola} = 0 THEN TRUE ELSE {$escola} = escola.cod_escola END)
              AND (CASE WHEN {$aluno} = 0 THEN TRUE ELSE {$aluno} = aluno.cod_aluno END)
              AND (CASE WHEN {$ano} = 0 THEN TRUE ELSE {$ano} = escola_ano_letivo.ano END)
              AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE {$curso} = curso.cod_curso END)
              AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE {$serie} = serie.cod_serie END)
              AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE {$turma} = turma.cod_turma END)
GROUP BY
  cod_escola,
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
  pes_pai.nome,
  pes_mae.nome          
ORDER BY
  cod_escola,
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
  pes_pai.nome,
  pes_mae.nome
        ";
        return $return;
    }
}
