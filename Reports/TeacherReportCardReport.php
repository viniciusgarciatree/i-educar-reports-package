<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TeacherReportCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        $templateByPageOrientation = [
            1 => 'teacher-report-card',
            2 => 'teacher-report-card-portrait'
        ];

        return $templateByPageOrientation[$this->args['orientacao']];
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
        $this->addRequiredArg('orientacao');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport   = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();


        $dados   = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);
        $arrMain = [];


        foreach ($dados as $index => $value) {
            self::getAlunosBoletim(
                array(
                    'cod_turma'     => $value['cod_turma'],
                    'id_disciplina' => $value['id_disciplina']
                ),
                $value,
                $arrMain
            );
        }
        $arrMain = array_values($arrMain);

        $return = [
            'main'   => $arrMain,
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ];

        return $return;
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
        $escola      = $this->args['escola'] ?: 0;
        $ano         = $this->args['ano'] ?: 0;
        $curso       = $this->args['curso'] ?: 0;
        $serie       = $this->args['serie'] ?: 0;
        $turma       = $this->args['turma'] ?: 0;
        $disciplina  = $this->args['disciplina'] ?: 0;

        return "
            SELECT escola_ano_letivo.ano,
                curso.nm_curso,
                serie.nm_serie,
                turma.nm_turma,
                turma.cod_turma,
                escola_ano_letivo.ano,
                turma_turno.nome AS periodo,
                view_componente_curricular.id AS id_disciplina,
                view_componente_curricular.nome AS nome_disciplina
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_ano_letivo ON (pmieducar.escola_ano_letivo.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                            AND serie.ref_cod_curso = escola_curso.ref_cod_curso)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                            AND turma.ref_ref_cod_serie = serie.cod_serie
                            AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                            AND turma.ativo = 1)
            INNER JOIN pmieducar.curso ON (escola_curso.ref_cod_escola = escola.cod_escola
                            AND turma.ref_cod_curso = curso.cod_curso)
            LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
            INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma)
            WHERE instituicao.cod_instituicao = {$instituicao}
            AND escola.cod_escola = {$escola}
            AND escola_ano_letivo.ano = {$ano}
            AND turma.ano = escola_ano_letivo.ano
            AND escola_curso.ref_cod_curso = {$curso}
            AND serie.cod_serie = {$serie}
            AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
            AND (CASE WHEN {$disciplina} = 0 THEN TRUE ELSE view_componente_curricular.id = {$disciplina} END)
            ORDER BY turma.nm_turma, view_componente_curricular.nome;
        ";
    }

    public function getJsonDataFromDatasets()
    {
        $queriesDatasets = $this->getSqlsForDatasets();
        $jsonData        = [];

        foreach ($queriesDatasets as $name => $query) {

            $jsonData[$name] = Portabilis_Utils_Database::fetchPreparedQuery($query);
        }

        return $jsonData;
    }

    public function getSqlsForDatasets()
    {
        $turma                 = $this->args['turma'] ?: 0;
        $ano                   = $this->args['ano'] ?: 0;
        $componente_curricular = $this->args['disciplina'] ?: 0;
        $situacao              = $this->args['situacao'] ?: 0;
        $linha                 = $this->args['linha'] ?: 0;

        $crosstabSubreportQuery = "
            (SELECT matricula.cod_matricula,
            (CASE WHEN matricula.dependencia THEN '* ' || public.fcn_upper(pessoa.nome) ELSE public.fcn_upper(pessoa.nome) END) AS nome_aluno,
            (CASE WHEN matricula.dependencia THEN true ELSE false END) AS dep,
            view_modulo.nome AS nome_modulo,
            view_modulo.sequencial AS sequencial,
            (CASE WHEN falta_aluno.tipo_falta = 1 THEN falta_geral.quantidade
            ELSE falta_componente_curricular.quantidade END)::varchar AS falta,
            view_componente_curricular.nome,

            CASE WHEN nota_componente_curricular.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                    replace(trunc(nota_componente_curricular.nota_arredondada::numeric, COALESCE(
                    ( SELECT regra_avaliacao.qtd_casas_decimais
                    FROM pmieducar.turma
                    JOIN pmieducar.serie
                    ON turma.ref_ref_cod_serie = serie.cod_serie
                    JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
                    JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
                    WHERE matricula_turma.ref_cod_turma = turma.cod_turma
                    LIMIT 1
                    ), 1))::varchar, '.', ',')
                ELSE
                    nota_componente_curricular.nota_arredondada
                END AS nota_arredondada_etapa,
                (CASE
                    WHEN relatorio.get_situacao_componente(nota_componente_curricular_media.situacao) = '' THEN
                        view_situacao.texto_situacao
                    ELSE
                        relatorio.get_situacao_componente(nota_componente_curricular_media.situacao)
                END) AS situacao,
            replace(modules.frequencia_da_matricula(matricula.cod_matricula)::varchar,'.',',') AS frequencia,
            relatorio.get_nota_exame(view_componente_curricular.id, matricula.cod_matricula) AS nota_exame,
            nota_componente_curricular_media.media_arredondada AS media

            FROM pmieducar.matricula
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
            INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                        AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                        AND view_situacao.sequencial = matricula_turma.sequencial
                    AND view_situacao.cod_situacao = 10)
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            INNER JOIN relatorio.view_modulo ON (view_modulo.cod_turma = matricula_turma.ref_cod_turma)
            INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = matricula_turma.ref_cod_turma)
            LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.falta_componente_curricular ON (falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                                    AND falta_componente_curricular.etapa = view_modulo.sequencial::varchar
                                                    AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                                    AND falta_aluno.tipo_falta = 2)
            LEFT JOIN modules.falta_geral ON (falta_geral.falta_aluno_id = falta_aluno.id
                                    AND falta_geral.etapa = view_modulo.sequencial::varchar
                                    AND falta_aluno.tipo_falta = 1)
            LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.nota_componente_curricular ON (nota_componente_curricular.nota_aluno_id = nota_aluno.id
                                                    AND nota_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                                    AND nota_componente_curricular.etapa = view_modulo.sequencial::varchar)
            LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.nota_aluno_id = nota_aluno.id
                                                        AND nota_componente_curricular_media.componente_curricular_id = view_componente_curricular.id)
            LEFT JOIN relatorio.situacao_matricula ON(nota_componente_curricular_media.situacao = situacao_matricula.cod_situacao)

            WHERE matricula_turma.ref_cod_turma = {$turma}
            AND matricula.ano = {$ano}
            AND matricula.ativo = 1
            AND (CASE WHEN {$componente_curricular} = 0 THEN TRUE ELSE view_componente_curricular.id = {$componente_curricular} END)
            AND (CASE WHEN matricula.dependencia THEN
                CASE WHEN ((SELECT count(*)
                                FROM pmieducar.disciplina_dependencia dp
                            WHERE dp.ref_cod_matricula = matricula.cod_matricula
                                AND dp.ref_cod_disciplina = view_componente_curricular.id) > 0) THEN TRUE
                END
            ELSE TRUE END)
            AND (CASE WHEN {$situacao} = 0 THEN true ELSE COALESCE(nota_componente_curricular_media.situacao, matricula.aprovado) = {$situacao} END)
            ORDER BY (CASE WHEN matricula.dependencia THEN 1 ELSE 0 END), sequencial_fechamento, relatorio.get_texto_sem_caracter_especial(pessoa.nome), matricula.cod_matricula, sequencial)

            UNION ALL

            (SELECT round(random()*1000), NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL
            FROM generate_series(1, {$linha}));
        ";

        return [
            'teacher_report_card_crosstab' => $crosstabSubreportQuery
        ];
    }

    public function getAlunosBoletim($arrParam, $aluno, &$arrData)
    {
        $turma                 = $arrParam['cod_turma'] ?: 0;
        $componente_curricular = $arrParam['id_disciplina'] ?: 0;
        $ano                   = $this->args['ano'] ?: 0;
        $situacao              = $this->args['situacao'] ?: 0;
        $linha                 = $this->args['linha'] ?: 0;

        $getAlunosBoletimQuery = "
            (SELECT matricula.cod_matricula,
            (CASE WHEN matricula.dependencia THEN '* ' || public.fcn_upper(pessoa.nome) ELSE public.fcn_upper(pessoa.nome) END) AS nome_aluno,
            (CASE WHEN matricula.dependencia THEN true ELSE false END) AS dep,
            view_modulo.nome AS nome_modulo,
            view_modulo.sequencial AS sequencial,
            (CASE WHEN falta_aluno.tipo_falta = 1 THEN falta_geral.quantidade
            ELSE falta_componente_curricular.quantidade END)::varchar AS falta,
            view_componente_curricular.nome,

            CASE WHEN nota_componente_curricular.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                    replace(trunc(nota_componente_curricular.nota_arredondada::numeric, COALESCE(
                    ( SELECT regra_avaliacao.qtd_casas_decimais
                    FROM pmieducar.turma
                    JOIN pmieducar.serie
                    ON turma.ref_ref_cod_serie = serie.cod_serie
                    JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
                    JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id)
                    WHERE matricula_turma.ref_cod_turma = turma.cod_turma
                    LIMIT 1
                    ), 1))::varchar, '.', ',')
                ELSE
                    nota_componente_curricular.nota_arredondada
                END AS nota_arredondada_etapa,
                (CASE
                    WHEN relatorio.get_situacao_componente(nota_componente_curricular_media.situacao) = '' THEN
                        view_situacao.texto_situacao
                    ELSE
                        relatorio.get_situacao_componente(nota_componente_curricular_media.situacao)
                END) AS situacao,
            replace(modules.frequencia_da_matricula(matricula.cod_matricula)::varchar,'.',',') AS frequencia,
            relatorio.get_nota_exame(view_componente_curricular.id, matricula.cod_matricula) AS nota_exame,
            nota_componente_curricular_media.media_arredondada AS media

            FROM pmieducar.matricula
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
            INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                        AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                        AND view_situacao.sequencial = matricula_turma.sequencial
                    AND view_situacao.cod_situacao = 10)
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            INNER JOIN relatorio.view_modulo ON (view_modulo.cod_turma = matricula_turma.ref_cod_turma)
            INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = matricula_turma.ref_cod_turma)
            LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.falta_componente_curricular ON (falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                                    AND falta_componente_curricular.etapa = view_modulo.sequencial::varchar
                                                    AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                                    AND falta_aluno.tipo_falta = 2)
            LEFT JOIN modules.falta_geral ON (falta_geral.falta_aluno_id = falta_aluno.id
                                    AND falta_geral.etapa = view_modulo.sequencial::varchar
                                    AND falta_aluno.tipo_falta = 1)
            LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.nota_componente_curricular ON (nota_componente_curricular.nota_aluno_id = nota_aluno.id
                                                    AND nota_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                                    AND nota_componente_curricular.etapa = view_modulo.sequencial::varchar)
            LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.nota_aluno_id = nota_aluno.id
                                                        AND nota_componente_curricular_media.componente_curricular_id = view_componente_curricular.id)
            LEFT JOIN relatorio.situacao_matricula ON(nota_componente_curricular_media.situacao = situacao_matricula.cod_situacao)

            WHERE matricula_turma.ref_cod_turma = {$turma}
            AND matricula.ano = {$ano}
            AND matricula.ativo = 1
            AND (CASE WHEN {$componente_curricular} = 0 THEN TRUE ELSE view_componente_curricular.id = {$componente_curricular} END)
            AND (CASE WHEN matricula.dependencia THEN
                CASE WHEN ((SELECT count(*)
                                FROM pmieducar.disciplina_dependencia dp
                            WHERE dp.ref_cod_matricula = matricula.cod_matricula
                                AND dp.ref_cod_disciplina = view_componente_curricular.id) > 0) THEN TRUE
                END
            ELSE TRUE END)
            AND (CASE WHEN {$situacao} = 0 THEN true ELSE COALESCE(nota_componente_curricular_media.situacao, matricula.aprovado) = {$situacao} END)
            ORDER BY (CASE WHEN matricula.dependencia THEN 1 ELSE 0 END), sequencial_fechamento, relatorio.get_texto_sem_caracter_especial(pessoa.nome), matricula.cod_matricula, sequencial)

            UNION ALL

            (SELECT round(random()*1000), NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL
            FROM generate_series(1, {$linha}));
        ";

        $data = Portabilis_Utils_Database::fetchPreparedQuery($getAlunosBoletimQuery);

        foreach ($data as $index => $value) {
            $matricula = (int)$value['cod_matricula'];
            $key = $matricula.'-'.$aluno['id_disciplina'];
            if (!isset($arrData[$key])) {
                $arrData[$key] = array(
                    'ano'                                => $aluno['ano'],
                    'nm_curso'                           => $aluno['nm_curso'],
                    'nm_serie'                           => $aluno['nm_serie'],
                    'nm_turma'                           => $aluno['nm_turma'],
                    'cod_turma'                          => $aluno['cod_turma'],
                    'periodo'                            => $aluno['periodo'],
                    'id_disciplina'                      => $aluno['id_disciplina'],
                    'nome_disciplina'                    => $aluno['nome_disciplina'],
                    'cod_matricula'                      => $value['cod_matricula'],
                    'nome_aluno'                         => $value['nome_aluno'],
                    'nome'                               => $value['nome'],
                    'dep'                                => $value['dep'],
                    'frequencia'                         => $value['frequencia'],
                    'media'                              => $value['media'],
                    'nota_exame'                         => $value['nota_exame'],
                    'situacao'                           => $value['situacao'],
                    'falta_' . $value['sequencial']      => $value['falta'],
                    'bimestral_' . $value['sequencial']  => $value['sequencial'],
                    'nota_etapa_' . $value['sequencial'] => $value['nota_arredondada_etapa'],
                );
            } else {
                $arrData[$key]['bimestral_' . $value['sequencial']]  = $value['sequencial'];
                $arrData[$key]['nota_etapa_' . $value['sequencial']] = $value['nota_arredondada_etapa'];
                $arrData[$key]['falta_' . $value['sequencial']]      = $value['falta'];
                $arrData[$key]['media']                              = $value['media'];
                $arrData[$key]['sequencial']                         = $value['sequencial'];
            }
        }
    }

}
