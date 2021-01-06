<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentIndividualRecordReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'student-individual-record';
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
        $this->addRequiredArg('aluno');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $exibir_paracer_descritivo = $this->args['exibir_paracer_descritivo'];
        $queryMainReport   = $this->getSqlMainReport();
        $queryComponente   = $this->getSqlComponenteReport();
        $queryObservacao   = $this->getSqlObservacaoReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        $arrMain       = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);
        $arrComponente = Portabilis_Utils_Database::fetchPreparedQuery($queryComponente);
        $arrObservacao = Portabilis_Utils_Database::fetchPreparedQuery($queryObservacao);
        $header        = Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport);

        $carga_horaria_total = 0;
        $horas = 0;
        $minutos = 0;
        $totalFaltasAula = 0;
        foreach ($arrComponente as $index => $value) {
            $faltasAula = 0;
            $arrValue = explode(':', $value['carga_horaria_auxiliar']);
            $arrValue[1] =trim($arrValue[1]);

            $horas += (int)$arrValue[0];
            $minutos += (int)substr($arrValue[1], 0, 2);
            if (strlen($arrValue[1])<2) {
                $arrValue[1] = $arrValue[1] . str_pad($arrValue[1], 2-strlen($arrValue[1]), '0');
            } else {
                $arrValue[1] = substr($arrValue[1], 0, 2);
            }
            $arrComponente[$index]['carga_horaria_auxiliar'] = '' . $arrValue[0] . ':' . $arrValue[1];

            if ($minutos>59) {
                $minutos -= 60;
                $horas += 1;
            }
            $faltasAula += $value['falta1'];
            $faltasAula += $value['falta2'];
            $faltasAula += $value['falta3'];
            $faltasAula += $value['falta4'];
            $faltasHoras = ($faltasAula * $value['hora_aula']);
            if ($faltasHoras>0) {
                $totalFaltasAula                       += $faltasHoras;
                $minutosFalta = ($faltasHoras % 60);
                $faltasHoras                           = (($faltasHoras - ($faltasHoras % 60)) / 60) . ':' . $minutosFalta;
                $arrComponente[$index]['faltas_horas'] = $faltasHoras;
            } else {
                $arrComponente[$index]['faltas_horas'] = '';
            }
        }

        if ($horas == 0) {
            $carga_horaria_total = '-';
        } else {
            $carga_horaria_total = $horas . ':' . ($minutos < 10 ? '0' . $minutos : $minutos);
        }

        unset($this->args['exibir_paracer_descritivo']);

        if (count($arrMain) == 0) {
            return [];
        }

        $arrMain[0]['carga_horaria_total'] = $carga_horaria_total;
        $arrMain[0]['data_componente'] = count($arrComponente) > 0 ? $arrComponente : [];

        if (!$exibir_paracer_descritivo) {
            $arrMain[0]['parecer']  = '';
        } else {
            $arrMain[0]['parecer'] = count(
                $arrObservacao
            ) > 0 && !empty($arrObservacao[0]['parecer']) ? $arrObservacao[0]['parecer'] : '';
        }

        $return = [
            'main'   => $arrMain,
            'header' => $header,
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
        $escola      = $this->args['escola'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $aluno       = $this->args['aluno'] ?: 0;
        $curso       = $this->args['curso'] ?: 0;
        $serie       = $this->args['serie'] ?: 0;
        $turma       = $this->args['turma'] ?: 0;
        $ano         = $this->args['ano'] ?: 0;

        $return = "
SELECT
       fcn_upper(view_dados_escola.nome) AS nm_escola,
       view_dados_escola.logradouro,
       view_dados_escola.bairro,
       matricula.cod_matricula AS cod_matricula,
       aluno.cod_aluno AS cod_aluno,
       relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nome_aluno,
       to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
       relatorio.get_pai_aluno(aluno.cod_aluno) AS nome_do_pai,
       relatorio.get_mae_aluno(aluno.cod_aluno) AS nome_da_mae,
       municipio.nome || '/' || municipio.sigla_uf AS cidade_nascimento_uf,
       municipio.sigla_uf AS uf_nascimento,
       municipio.nome AS cidade_nascimento,
       turma.nm_turma,
       matricula.ano,
       COALESCE(endpes.numero::text,'') as numero,
       trim(leading ' ' from concat(fone_telefone.ddd,'', to_char(fone_telefone.fone, '99999-9999'))::text) as telefone,
	   trim(leading ' ' from concat(fone_celular.ddd,'',to_char(fone_celular.fone, '9 9999-9999'))::text) as celular,
       CASE
            WHEN fisica.nacionalidade = 1 THEN 'Brasileira'
            WHEN fisica.nacionalidade = 2 THEN 'Naturalizado Brasileiro'
            ELSE 'Estrangeiro'
       END as nacionalidade,
       COALESCE(fisica.nis_pis_pasep::text,'') AS nis_pis_pasep,
       logradouro.nome as logradouro_aluno,
       COALESCE(fisica.sexo,'') as sexo,
       eca.cod_aluno_inep AS cod_inep,
       view_situacao.texto_situacao_simplificado AS situacao_simplificado,
	   matricula.ano AS ano,
       curso.nm_curso AS nome_curso,
       serie.nm_serie AS nome_serie,
       turma.nm_turma AS nome_turma,
       turma_turno.nome AS periodo,
       COALESCE((
			  SELECT
			  CASE
				WHEN STRPOS(etapa_ensino.descricao ,'1º Ano') <> 0
		   			OR STRPOS(etapa_ensino.descricao ,'2º Ano') <> 0
					OR STRPOS(etapa_ensino.descricao ,'3º Ano') <> 0
		            OR STRPOS(etapa_ensino.descricao ,'1 ano') <> 0
		   			OR STRPOS(etapa_ensino.descricao ,'2 ano') <> 0
					OR STRPOS(etapa_ensino.descricao ,'3 ano') <> 0
		   			OR STRPOS(etapa_ensino.descricao ,'Educação Infantil') <> 0
				THEN 'Alfabetização'
				WHEN STRPOS(etapa_ensino.descricao ,'4º Ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'5º Ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'6º Ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'7º Ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'8º Ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'9º Ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'4 ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'5 ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'6 ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'7 ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'8 ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'9 ano') <> 0
                   OR STRPOS(etapa_ensino.descricao ,'9 ano') <> 0
                THEN 'Complementar'
			    ELSE ''
				END as ciclo
			FROM cadastro.etapa_ensino
			INNER JOIN pmieducar.turma AS turma_serie ON etapa_ensino.codigo = turma_serie.etapa_educacenso
			INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma_serie.cod_turma
			INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula AND matricula.ativo = 1
			INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula AND view_situacao.cod_turma = turma_serie.cod_turma
			AND matricula_turma.sequencial = view_situacao.sequencial
			INNER JOIN pmieducar.aluno AS aluno_ciclos ON pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
			WHERE aluno_ciclos.cod_aluno = aluno.cod_aluno
			AND turma_serie.cod_turma = turma.cod_turma LIMIT 1
			)
	    ,'') as ciclo,
	    COALESCE((
			SELECT serie_serie.nm_serie AS serie
				FROM cadastro.etapa_ensino
				INNER JOIN pmieducar.turma AS turma_serie ON etapa_ensino.codigo = turma_serie.etapa_educacenso
				INNER JOIN pmieducar.serie as serie_serie ON (turma_serie.ref_ref_cod_serie = serie_serie.cod_serie)
				INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma_serie.cod_turma
				INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula AND matricula.ativo = 1
				INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula AND view_situacao.cod_turma = turma_serie.cod_turma
				AND matricula_turma.sequencial = view_situacao.sequencial
				INNER JOIN pmieducar.aluno AS aluno_ciclos ON pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
				WHERE aluno_ciclos.cod_aluno = aluno.cod_aluno
				AND turma_serie.cod_turma = turma.cod_turma LIMIT 1
				)
		,'') as serie
		,(SELECT textcat_all(obs)
          FROM (SELECT observacao AS obs
                  FROM pmieducar.historico_escolar phe
                 WHERE phe.ref_cod_aluno = aluno.cod_aluno
                   AND phe.ativo = 1
                   AND phe.aprovado <> 2
                 ORDER BY phe.ano)tabl) AS observacao_all
 FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso)
INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie)
INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_serie = serie.cod_serie)
LEFT JOIN modules.regra_avaliacao_serie_ano rasa
ON turma.ano = rasa.ano_letivo
AND rasa.serie_id = serie.cod_serie
LEFT JOIN modules.regra_avaliacao ON modules.regra_avaliacao.id = rasa.regra_avaliacao_id
INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                       AND view_situacao.sequencial = matricula_turma.sequencial
                                       AND view_situacao.cod_situacao = 9)
LEFT JOIN pmieducar.historico_escolar  on historico_escolar.ref_cod_aluno = aluno.cod_aluno and historico_escolar.ano = matricula.ano
LEFT JOIN modules.parecer_aluno ON (parecer_aluno.matricula_id = matricula.cod_matricula)
INNER JOIN relatorio.view_dados_escola ON TRUE  AND (escola.cod_escola = view_dados_escola.cod_escola)
LEFT JOIN cadastro.endereco_pessoa endpes ON endpes.idpes = aluno.ref_idpes
LEFT JOIN public.logradouro ON logradouro.idlog = endpes.idlog
LEFT JOIN public.bairro ON bairro.idbai = endpes.idbai
LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = aluno.cod_aluno)
LEFT JOIN cadastro.fone_pessoa fone_telefone ON TRUE AND fone_telefone.idpes = fisica.idpes_mae  AND fone_telefone.tipo = 1
LEFT JOIN cadastro.fone_pessoa fone_celular ON TRUE  AND fone_celular.idpes = fisica.idpes_mae AND fone_celular.tipo = 3
WHERE instituicao.cod_instituicao = {$instituicao}
  AND matricula.ano = {$ano}
  AND escola.cod_escola = {$escola}
  AND curso.cod_curso = {$curso}
  AND serie.cod_serie = {$serie}
  AND turma.cod_turma = {$turma}
  AND matricula.ativo = 1
  AND aluno.cod_aluno = {$aluno}
ORDER BY sequencial_fechamento,
         nome_aluno,
         cod_aluno
LIMIT 1
        ";

        return $return;
    }

    /**
     * Retorna o SQL para buscar os dados que serão adicionados ao cabeçalho.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlHeaderReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola      = $this->args['escola'] ?: 0;
        $notSchool   = empty($this->args['escola']) ? 'true' : 'false';

        $sql = "

            SELECT
                public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
                public.fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
                (CASE WHEN {$notSchool} THEN '' ELSE fcn_upper(view_dados_escola.nome) END) AS nm_escola,
                (CASE WHEN {$notSchool} THEN instituicao.ref_idtlog::text ELSE view_dados_escola.tipo_logradouro::text END),
                (CASE WHEN {$notSchool} THEN instituicao.logradouro ELSE view_dados_escola.logradouro END),
                (CASE WHEN {$notSchool} THEN instituicao.bairro ELSE view_dados_escola.bairro END),
                (CASE WHEN {$notSchool} THEN instituicao.numero::integer ELSE view_dados_escola.numero::integer END),
                (CASE WHEN {$notSchool} THEN instituicao.ddd_telefone ELSE view_dados_escola.telefone_ddd END) AS fone_ddd,
                (CASE WHEN {$notSchool} THEN 0 ELSE view_dados_escola.celular_ddd END) AS cel_ddd,
                (CASE WHEN {$notSchool} THEN to_char(instituicao.cep, '99999-999') ELSE to_char(view_dados_escola.cep::integer, '99999-999') END) AS cep,
                (CASE WHEN {$notSchool} THEN to_char(instituicao.telefone, '99999-9999') ELSE view_dados_escola.telefone END) AS fone,
                (CASE WHEN {$notSchool} THEN ' ' ELSE view_dados_escola.celular END) AS cel,
                (CASE WHEN {$notSchool} THEN ' ' ELSE view_dados_escola.email END),
                instituicao.ref_sigla_uf AS uf,
                instituicao.cidade,
                view_dados_escola.inep
            FROM
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE
                AND (instituicao.cod_instituicao = escola.ref_cod_instituicao)
            INNER JOIN relatorio.view_dados_escola ON TRUE
                AND (escola.cod_escola = view_dados_escola.cod_escola)
            WHERE TRUE
                AND instituicao.cod_instituicao = {$instituicao}
                AND
                (
                    CASE WHEN {$notSchool} THEN
                        TRUE
                    ELSE
                        view_dados_escola.cod_escola = {$escola}
                    END
                )
            LIMIT 1

        ";

        return $sql;
    }

    public function getSqlComponenteReport()
    {
        $escola      = $this->args['escola'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $aluno       = $this->args['aluno'] ?: 0;
        $curso       = $this->args['curso'] ?: 0;
        $serie       = $this->args['serie'] ?: 0;
        $turma       = $this->args['turma'] ?: 0;
        $ano         = $this->args['ano'] ?: 0;

        return "
        SELECT
modules.componente_curricular.tipo_base,
	view_componente_curricular.ordenamento AS componente_order,
    componente_curricular.nome as curricular_nome,
    CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa1.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa1.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa1.nota_arredondada
              END
       END AS nota1,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa2.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa2.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa2.nota_arredondada
              END
       END AS nota2,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa3.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa3.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa3.nota_arredondada
              END
       END AS nota3,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa4.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa4.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa4.nota_arredondada
              END
       END AS nota4,
    COALESCE(
	   CASE
           WHEN matricula_turma.remanejado = true THEN '-'
	   ELSE
	   		CASE
				WHEN nota_componente_curricular_etapa1.nota_original ~ '^-?[0-9]+\.?[0-9]*$' THEN replace(trunc(nota_componente_curricular_etapa1.nota_original::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
		    ELSE
                nota_componente_curricular_etapa1.nota_original
              END
       END,'-') AS nota_original_1,
	   COALESCE(
	   CASE
           WHEN matricula_turma.remanejado = true THEN '-'
	   ELSE
	   		CASE
				WHEN nota_componente_curricular_etapa1.nota_recuperacao ~ '^-?[0-9]+\.?[0-9]*$' THEN replace(trunc(nota_componente_curricular_etapa1.nota_recuperacao::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
		    ELSE
                nota_componente_curricular_etapa1.nota_recuperacao
              END
       END,'-') AS nota_recuperacao_1,
	   COALESCE(
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa2.nota_original ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa2.nota_original::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa2.nota_original
              END
       END,'-') AS nota_original_2,
	   COALESCE(
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa2.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa2.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa2.nota_arredondada
              END
       END,'-') AS nota_recuperacao_2,
	   COALESCE(
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa3.nota_original ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa3.nota_original::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa3.nota_original
              END
       END,'-') AS nota_original_3,
	   COALESCE(
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa3.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa3.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa3.nota_arredondada
              END
       END,'-') AS nota_recuperacao_3,
	   COALESCE(
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa4.nota_original ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa4.nota_original::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa4.nota_original
              END
       END,'-') AS nota_original_4,
	   COALESCE(
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa4.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa4.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa4.nota_arredondada
              END
       END,'-') AS nota_recuperacao_4,
  COALESCE(CASE
      WHEN matricula_turma.remanejado = true THEN 0
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '1'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '1'
                        AND falta_aluno.tipo_falta = 1)))::integer
  END,0) AS falta1,
  COALESCE(CASE
      WHEN matricula_turma.remanejado = true THEN 0
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '2'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '2'
                        AND falta_aluno.tipo_falta = 1)))::integer
  END,0) AS falta2,
  COALESCE(CASE
      WHEN matricula_turma.remanejado = true THEN 0
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '3'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '3'
                        AND falta_aluno.tipo_falta = 1)))::integer
  END,0) AS falta3,
  COALESCE(CASE
      WHEN matricula_turma.remanejado = true THEN 0
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '4'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '4'
                        AND falta_aluno.tipo_falta = 1)))::integer
  END,0) AS falta4,
  (CASE
   		WHEN componente_curricular_turma.carga_horaria is not null THEN componente_curricular_turma.carga_horaria
		WHEN ccae.carga_horaria is not null THEN ccae.carga_horaria
   		ELSE 0
   END)::integer AS carga_horaria
   ,(CASE
       WHEN componente_curricular_turma.carga_horaria_auxiliar is not null THEN REPLACE(componente_curricular_turma.carga_horaria_auxiliar::varchar,'.',':')
       WHEN ccae.carga_horaria_auxiliar is not null THEN REPLACE(ccae.carga_horaria_auxiliar::varchar,'.',':')
       ELSE ''
   END)::varchar AS carga_horaria_auxiliar
   ,(CASE
       WHEN componente_curricular_turma.hora_aula is not null THEN componente_curricular_turma.hora_aula
       WHEN ccae.hora_aula is not null THEN ccae.hora_aula
       ELSE 0
   END)::integer AS hora_aula
   ,COALESCE(texto_situacao,'') as texto_situacao
   ,COALESCE(modules.frequencia_da_matricula(matricula.cod_matricula),0) AS frequencia
 FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso)
INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie)
INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_serie = serie.cod_serie)
LEFT JOIN modules.regra_avaliacao_serie_ano rasa ON turma.ano = rasa.ano_letivo AND rasa.serie_id = serie.cod_serie
LEFT JOIN modules.regra_avaliacao ON modules.regra_avaliacao.id = rasa.regra_avaliacao_id
INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma)
LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa1 ON (nota_componente_curricular_etapa1.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa1.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa1.etapa = '1')
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa2 ON (nota_componente_curricular_etapa2.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa2.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa2.etapa = '2')
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa3 ON (nota_componente_curricular_etapa3.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa3.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa3.etapa = '3')
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa4 ON (nota_componente_curricular_etapa4.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa4.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa4.etapa = '4')
INNER JOIN modules.componente_curricular on componente_curricular.id = view_componente_curricular.id
LEFT JOIN modules.parecer_aluno ON (parecer_aluno.matricula_id = matricula.cod_matricula)
INNER JOIN relatorio.view_dados_escola ON TRUE  AND (escola.cod_escola = view_dados_escola.cod_escola)
LEFT JOIN modules.componente_curricular_turma ON componente_curricular_turma.componente_curricular_id = componente_curricular.id AND componente_curricular_turma.turma_id = turma.cod_turma AND componente_curricular_turma.ano_escolar_id = serie.cod_serie
LEFT JOIN modules.componente_curricular_ano_escolar as ccae ON ccae.componente_curricular_id = componente_curricular.id AND serie.cod_serie = ccae.ano_escolar_id
LEFT JOIN relatorio.view_situacao on view_situacao.cod_turma = turma.cod_turma and view_situacao.cod_matricula = matricula.cod_matricula and view_situacao.cod_situacao = any (array[1, 2, 3, 4, 5, 6, 12, 13])
WHERE instituicao.cod_instituicao = {$instituicao}
  AND matricula.ano = {$ano}
  AND escola.cod_escola = {$escola}
  AND curso.cod_curso = {$curso}
  AND serie.cod_serie = {$serie}
  AND turma.cod_turma = {$turma}
  AND matricula.ativo = 1
  AND aluno.cod_aluno = {$aluno}
ORDER BY tipo_base,componente_order,curricular_nome
        ";
    }

    public function getSqlObservacaoReport()
    {
        $escola      = $this->args['escola'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $aluno       = $this->args['aluno'] ?: 0;
        $curso       = $this->args['curso'] ?: 0;
        $serie       = $this->args['serie'] ?: 0;
        $turma       = $this->args['turma'] ?: 0;
        $ano         = $this->args['ano'] ?: 0;

        return "
        SELECT
            array_to_string(array_agg(etapa ||'º Bimestre:: ' || parecer),'\n') AS parecer
        FROM ( SELECT
             parecer_geral_etapa.parecer
             ,parecer_geral_etapa.etapa
             FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_serie = serie.cod_serie)
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
            INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
            LEFT JOIN modules.parecer_aluno ON (parecer_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.parecer_geral as parecer_geral_etapa ON (parecer_geral_etapa.parecer_aluno_id = parecer_aluno.id)
            WHERE parecer_geral_etapa.parecer IS NOT null
              AND instituicao.cod_instituicao = {$instituicao}
              AND matricula.ano = {$ano}
              AND escola.cod_escola = {$escola}
              AND curso.cod_curso = {$curso}
              AND serie.cod_serie = {$serie}
              AND turma.cod_turma = {$turma}
              AND matricula.ativo = 1
              AND aluno.cod_aluno = {$aluno}
        ) AS T
        ";
    }
}
