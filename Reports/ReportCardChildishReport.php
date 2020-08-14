<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'Reports/Tipos/TipoBoletim.php';
require_once 'App/Model/IedFinder.php';

class ReportCardChildishReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @return string
     *
     * @throws App_Model_Exception
     */
    public function templateName()
    {
        return 'report-card-childish';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
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
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $situacao_matricula = $this->args['situacao_matricula'] ?: 0;
        $alunos_diferenciados = $this->args['alunos_diferenciados'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "SELECT  
	      curso.nm_curso AS nome_curso,
          serie.nm_serie AS nome_serie,
          turma.nm_turma AS nome_turma,
          public.fcn_upper(pessoa.nome) AS nome_aluno,
          turma_turno.nome AS periodo,
          view_situacao.texto_situacao AS situacao,
          view_componente_curricular.nome AS nome_disciplina,
          TRUNC(nota_etapa1.nota::NUMERIC, 1) AS nota1num,
          tav_etapa1.descricao AS nota1,
          parecer_componente_etapa1.parecer AS parecer_componente_etapa1,
          TRUNC(nota_etapa2.nota::NUMERIC, 1) AS nota2num,
          tav_etapa2.descricao AS nota2,
          parecer_componente_etapa2.parecer AS parecer_componente_etapa2,
          matricula.cod_matricula AS matricula,
          modules.frequencia_da_matricula(matricula.cod_matricula) AS frequencia,
          fisica.data_nasc AS dt_nasc,
          relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 1) AS nota1numturma,
          relatorio.get_media_turma(turma.cod_turma, view_componente_curricular.id, 2) AS nota2numturma,
          parecer_geral_etapa1.parecer AS parecer_geral_etapa1,
          parecer_geral_etapa2.parecer AS parecer_geral_etapa2,
          falta_etapa1.quantidade AS total_faltas_et1,
          falta_etapa2.quantidade AS total_faltas_et2,
          falta_componente1.quantidade AS faltas_componente_et1,
          falta_componente2.quantidade AS faltas_componente_et2,
          relatorio.get_total_geral_falta_componente(matricula.cod_matricula) AS total_geral_faltas_componente,
          relatorio.get_total_faltas(matricula.cod_matricula) AS total_faltas,
          curso.hora_falta * 100 AS curso_hora_falta,
          componente_curricular_ano_escolar.carga_horaria::int AS carga_horaria_componente,
          serie.carga_horaria AS carga_horaria_serie,
          nota_componente_curricular_media.media_arredondada AS media,
          TRUNC(nota_componente_curricular_media.media::NUMERIC, 1) AS medianum,
          TRUNC(nota_exame.nota_arredondada::NUMERIC, 1) AS nota_exame,
          TRUNC(coalesce(regra_avaliacao.media, 0.00), 1) AS media_recuperacao,
          relatorio.get_media_geral_turma(turma.cod_turma, view_componente_curricular.id) AS medianumturma,
          relatorio.get_total_falta_componente(matricula.cod_matricula, view_componente_curricular.id) AS total_faltas_componente
   FROM pmieducar.instituicao
   INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
   INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
   INNER JOIN relatorio.view_dados_escola ON (view_dados_escola.cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                         AND escola_curso.ref_cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                  AND curso.ativo = 1)
   INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                         AND escola_serie.ref_cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                  AND serie.ativo = 1)
   INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                  AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                                  AND (
                                           turma.ref_ref_cod_serie = escola_serie.ref_cod_serie OR
                                           turma.ref_ref_cod_serie_mult = escola_serie.ref_cod_serie
                                       )
                                  AND turma.ativo = 1)
   INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma)
   INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
   INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                      AND matricula.ref_ref_cod_escola = escola.cod_escola
                                      AND matricula.ref_cod_curso = curso.cod_curso
                                      AND matricula.ref_ref_cod_serie = serie.cod_serie
                                      AND matricula.ano = turma.ano
                                      AND matricula.ativo = 1)
   INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                          AND view_situacao.cod_turma = turma.cod_turma
                                          AND matricula_turma.sequencial = view_situacao.sequencial)
   LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
                                       AND turma.cod_turma = matricula_turma.ref_cod_turma)
   INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
   INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
   INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
   LEFT JOIN modules.parecer_aluno parecer ON (parecer.matricula_id = matricula.cod_matricula)
   LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
   LEFT JOIN modules.nota_componente_curricular nota_etapa1 ON (nota_etapa1.nota_aluno_id = nota_aluno.id
                                                                AND nota_etapa1.componente_curricular_id = view_componente_curricular.id
                                                                AND nota_etapa1.etapa = '1')
   LEFT JOIN modules.parecer_componente_curricular parecer_componente_etapa1 ON (nota_etapa1.componente_curricular_id = parecer_componente_etapa1.componente_curricular_id
                                                                AND parecer.id = parecer_componente_etapa1.parecer_aluno_id
                                                                AND parecer_componente_etapa1.etapa = '1')
   LEFT JOIN modules.parecer_geral parecer_geral_etapa1 ON (parecer.id = parecer_geral_etapa1.parecer_aluno_id
                                                                AND parecer_geral_etapa1.etapa = '1')
   LEFT JOIN modules.nota_componente_curricular nota_etapa2 ON (nota_etapa2.nota_aluno_id = nota_aluno.id
                                                                AND nota_etapa2.componente_curricular_id = view_componente_curricular.id
                                                                AND nota_etapa2.etapa = '2')
   LEFT JOIN modules.parecer_geral parecer_geral_etapa2 ON (parecer.id = parecer_geral_etapa2.parecer_aluno_id
                                                                AND parecer_geral_etapa2.etapa = '2')
   LEFT JOIN modules.parecer_componente_curricular parecer_componente_etapa2 ON (nota_etapa2.componente_curricular_id = parecer_componente_etapa2.componente_curricular_id
                                                                AND parecer.id = parecer_componente_etapa2.parecer_aluno_id
                                                                AND parecer_componente_etapa2.etapa = '2')                                                                                                                    
   LEFT JOIN modules.nota_componente_curricular nota_exame ON (nota_exame.nota_aluno_id = nota_aluno.id
                                                               AND nota_exame.componente_curricular_id = view_componente_curricular.id
                                                               AND nota_exame.etapa = 'Rc')    
   LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.nota_aluno_id = nota_aluno.id
                                                          AND nota_componente_curricular_media.componente_curricular_id = view_componente_curricular.id)
   LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
   LEFT JOIN modules.falta_geral falta_etapa1 ON (falta_etapa1.falta_aluno_id = falta_aluno.id
                                                  AND falta_etapa1.etapa = '1')
   LEFT JOIN modules.falta_geral falta_etapa2 ON (falta_etapa2.falta_aluno_id = falta_aluno.id
                                                  AND falta_etapa2.etapa = '2')
   LEFT JOIN modules.falta_componente_curricular falta_componente1 ON (falta_componente1.falta_aluno_id = falta_aluno.id
                                                                       AND falta_componente1.componente_curricular_id = view_componente_curricular.id
                                                                       AND falta_componente1.etapa = '1')
   LEFT JOIN modules.falta_componente_curricular falta_componente2 ON (falta_componente2.falta_aluno_id = falta_aluno.id
                                                                       AND falta_componente2.componente_curricular_id = view_componente_curricular.id
                                                                       AND falta_componente2.etapa = '2')
   LEFT JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                                                           AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                           AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos)
                                                           )
   LEFT JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
   LEFT JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
   LEFT JOIN modules.tabela_arredondamento_valor tav_etapa1 on (regra_avaliacao.tabela_arredondamento_id = tav_etapa1.tabela_arredondamento_id 
                                                         AND tav_etapa1.nome = nota_etapa1.nota_arredondada)
   LEFT JOIN modules.tabela_arredondamento_valor tav_etapa2 on (regra_avaliacao.tabela_arredondamento_id = tav_etapa2.tabela_arredondamento_id 
                                                         AND tav_etapa2.nome = nota_etapa2.nota_arredondada)
   WHERE instituicao.cod_instituicao = {$instituicao}
     AND escola.cod_escola = {$escola}
     AND curso.cod_curso = {$curso}
     AND serie.cod_serie = {$serie}
     AND turma.cod_turma = {$turma}
     AND escola_ano_letivo.ano = {$ano}
     AND view_situacao.cod_situacao = {$situacao_matricula}
     AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(aluno.cod_aluno, {$alunos_diferenciados})
     AND (CASE WHEN {$matricula} = 0 THEN TRUE ELSE matricula.cod_matricula = {$matricula} END)
   ORDER BY sequencial_fechamento,
            relatorio.get_texto_sem_caracter_especial(pessoa.nome),
            view_componente_curricular.ordenamento,
            view_componente_curricular.nome";
    }
}