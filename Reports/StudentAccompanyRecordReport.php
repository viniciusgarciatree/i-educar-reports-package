<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentAccompanyRecordReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'student-accompany-record';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('modelo');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport   = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        $arrMain = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);
        $header  = Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport);


        if (count($arrMain) == 0) {
            return array();
        }

        $tipoBase = ComponenteCurricular_Model_TipoBase::getInstance();
        $tipos = $tipoBase->getKeys();

        $dataNotas  = [];
        $dataParece  = [];
        $dataFaltas = ['falta1' => 0, 'falta2' => 0, 'falta3' => 0, 'falta4' => 0];

        foreach ($arrMain as $index => $value) {
            $tipoId = (int)$value['tipo_base'];
            $dataFaltas['falta1'] = $value['falta1'];
            $dataFaltas['falta2'] = $value['falta2'];
            $dataFaltas['falta3'] = $value['falta3'];
            $dataFaltas['falta4'] = $value['falta4'];

            $dataParece['parecer1'] = $value['parecer1'];
            $dataParece['parecer2'] = $value['parecer2'];
            $dataParece['parecer3'] = $value['parecer3'];
            $dataParece['parecer4'] = $value['parecer4'];

            $dataNotas[]          = array(
                'tipo'            => $tipoBase[$tipoId],
                'tipo_id'         => $tipoId,                
                'curricular_nome' => $value['curricular_nome'],
                'nota1'           => $value['nota1'],
                'nota2'           => $value['nota2'],
                'nota3'           => $value['nota3'],
                'nota4'           => $value['nota4'],
            );
        }

        $exibir_apreceres = explode(',', trim($value['exibir_apreceres'], '{}'));

        $arrReport                = array();
        $arrReport                = $arrMain[0];
        $arrMain[]                = $arrMain[0];
        $tempo_da_aula = 0;
        $value['carga_horaria'] = intval($value['carga_horaria']);
        $value['dias_letivos'] = intval($value['dias_letivos']);
        $quatidade_hora_dia_aula = intval($value['dias_letivos']) > 0 ? $value['carga_horaria']/$value['dias_letivos'] : 0;
        $quatidade_hora_dia_aula = $quatidade_hora_dia_aula>0 ? $quatidade_hora_dia_aula * 60 : 0;

        if(is_numeric($value['carga_horaria']) && is_numeric($value['dias_letivos']) && $value['carga_horaria']>0 && $value['dias_letivos']>0) {
            $tempo_da_aula = ((($value['carga_horaria'] / $value['dias_letivos']) * 60) / 5);
        }

        $arrReport['falta_hora1'] =  "-";
        $arrReport['falta_hora2'] =  "-";
        $arrReport['falta_hora3'] =  "-";
        $arrReport['falta_hora4'] =  "-";
        $falta_hora_total = 0;

        $calc_hora_falta = function ($hora){
            $hora = intval($hora);
            $tHora = "";
            if ($hora>0){
                return "" . date('H:i',strtotime( (intval($hora/60).':'. intval($hora%60)).":00"));
            }
            return ($hora>0 ?  $tHora : 0);
        };

        if($tempo_da_aula>0) {
            if (is_numeric($dataFaltas['falta1']) && $dataFaltas['falta1']) {
                $arrReport['falta_hora1'] = $dataFaltas['falta1'] * $quatidade_hora_dia_aula;
                $falta_hora_total += $arrReport['falta_hora1'];
                $arrReport['falta_hora1'] = $calc_hora_falta($arrReport['falta_hora1']);
            }
            if (is_numeric($dataFaltas['falta2']) && $dataFaltas['falta2']) {
                $arrReport['falta_hora2'] = $dataFaltas['falta2'] * $quatidade_hora_dia_aula;
                $falta_hora_total += $arrReport['falta_hora2'];
                $arrReport['falta_hora2'] = $calc_hora_falta($arrReport['falta_hora2']);
            }
            if (is_numeric($dataFaltas['falta3']) && $dataFaltas['falta3']) {
                $arrReport['falta_hora3'] = $dataFaltas['falta3'] * $quatidade_hora_dia_aula;
                $falta_hora_total += $arrReport['falta_hora3'];
                $arrReport['falta_hora3'] = $calc_hora_falta($arrReport['falta_hora3']);
            }
            if (is_numeric($dataFaltas['falta4']) && $dataFaltas['falta4']) {
                $arrReport['falta_hora4'] = $dataFaltas['falta4'] * $quatidade_hora_dia_aula;
                $falta_hora_total += $arrReport['falta_hora4'];
                $arrReport['falta_hora4'] = $calc_hora_falta($arrReport['falta_hora4']);
            }
        }

        $parecer = !empty($dataParece['parecer1']) ? " <b>1º Bimestre:</b> " . $dataParece['parecer1'] : "";
        $parecer .= !empty($dataParece['parecer2']) ? "<br> <br> <b>2º Bimestre:</b> " . $dataParece['parecer2'] : "";
        $parecer .= !empty($dataParece['parecer3']) ? "<br> <br> <b>3º Bimestre:</b> " . $dataParece['parecer3'] : "";
        $parecer .= !empty($dataParece['parecer4']) ? "<br> <br> <b>4º Bimestre:</b> " . $dataParece['parecer4'] : "";
        $arrReport['parecer'] = $parecer;

        $falta_hora_total = intval($falta_hora_total);
        $arrReport['falta_hora_total'] = $falta_hora_total > 0 ? $calc_hora_falta($falta_hora_total) : "-";

        $arrReport['falta_total']      = is_numeric($dataFaltas['falta1']) && $dataFaltas['falta1'] > 0 ? $dataFaltas['falta1'] : 0;
        $arrReport['falta_total']      += is_numeric($dataFaltas['falta2']) && $dataFaltas['falta2'] > 0 ? $dataFaltas['falta2'] : 0;
        $arrReport['falta_total']      += is_numeric($dataFaltas['falta3']) && $dataFaltas['falta3'] > 0 ? $dataFaltas['falta3'] : 0;
        $arrReport['falta_total']      += is_numeric($dataFaltas['falta4']) && $dataFaltas['falta4'] > 0 ? $dataFaltas['falta4'] : 0;

        $arrReport['falta1']      = is_numeric($dataFaltas['falta1']) && $dataFaltas['falta1'] > 0 ? $dataFaltas['falta1'] : "-";
        $arrReport['falta2']      = is_numeric($dataFaltas['falta2']) && $dataFaltas['falta2'] > 0 ? $dataFaltas['falta2'] : "-";
        $arrReport['falta3']      = is_numeric($dataFaltas['falta3']) && $dataFaltas['falta3'] > 0 ? $dataFaltas['falta3'] : "-";
        $arrReport['falta4']      = is_numeric($dataFaltas['falta4']) && $dataFaltas['falta4'] > 0 ? $dataFaltas['falta4'] : "-";

        /**
         * Verificar onde estão estes dados de processo de formação
         */
        $arrReport['data_notas']                 = $dataNotas;
        for($x=0;$x<5;$x++){
            $arrReport['data_notas_diversificada'][] = array(
                'curricular_nome' => "",
                'nota1'           => "",
                'nota2'           => "",
                'nota3'           => "",
                'nota4'           => "",
            );
        }

        $arrObs = array();
        $contBimestre = 1;
        $exibe_parecer = intval(30/count($exibir_apreceres));
        $rowCont = $exibe_parecer;

        for ($x = 0; $x < 30; $x++) {
            if($rowCont == $exibe_parecer && $contBimestre <= count($exibir_apreceres)){
                $arrObs[] = array("obs" => $contBimestre++ ." º Bimestre: ");
                $rowCont = 0;
            }else{
                $arrObs[] = array("obs" => "");
            }

            $rowCont++;
        }

        $arrReport['data_obs'] = $arrObs;
        $arrReport['cidade']   = $header[0]['cidade'] . "/" . $header[0]['uf'];

        $return = [
            'main'   => $arrReport,
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
SELECT t.*, CASE 
    WHEN STRPOS(serie ,'Período') <> 0 THEN 'Período'
	WHEN STRPOS(serie ,'Ano') <> 0 THEN 'Ano'
	WHEN STRPOS(serie ,'Série') <> 0 THEN 'Série'
  ELSE serie
END as periodo_ano_serie FROM (
        SELECT matricula.cod_matricula AS cod_matricula,
       aluno.cod_aluno AS cod_aluno,
       relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nome_aluno,
       to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
       relatorio.get_pai_aluno(aluno.cod_aluno) AS nome_do_pai,
       relatorio.get_mae_aluno(aluno.cod_aluno) AS nome_da_mae,
       municipio.nome || '/' || municipio.sigla_uf AS cidade_nascimento_uf,
       municipio.sigla_uf AS uf_nascimento,
       municipio.nome AS cidade_nascimento,
       view_situacao.texto_situacao_simplificado AS situacao_simplificado,
       CASE
           WHEN matricula_turma.remanejado = true THEN null
           ELSE
              trim(to_char(modules.frequencia_da_matricula(matricula.cod_matricula),'99999999999D99'))::character varying
       END AS frequencia_geral,
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
  (SELECT replace(textcat_all(abv), '<br>','|') FROM (SELECT abreviatura || ' - ' || nome AS abv
      FROM relatorio.view_componente_curricular
      WHERE view_componente_curricular.cod_turma = turma.cod_turma
      ORDER BY abreviatura, nome)  tabl) as legenda,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
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
                        AND falta_aluno.tipo_falta = 1)))::character varying
  END AS falta1,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
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
                        AND falta_aluno.tipo_falta = 1)))::character varying
  END AS falta2,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
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
                        AND falta_aluno.tipo_falta = 1))) ::character varying
  END AS falta3,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
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
                        AND falta_aluno.tipo_falta = 1)))::character varying
  END AS falta4,
      view_componente_curricular.ordenamento AS componente_order,
      componente_curricular.nome as curricular_nome,
	  view_componente_curricular.abreviatura AS curricular_abre,
      matricula.ano AS ano,
      curso.nm_curso AS nome_curso,
      serie.nm_serie AS nome_serie,
      turma.nm_turma AS nome_turma,
      relatorio.get_qtde_modulo(turma.cod_turma) AS qtd_modulo,
      turma_turno.nome AS periodo,
      COALESCE((SELECT 
  CASE 
    WHEN STRPOS(etapa_ensino.descricao ,'1º Ano') <> 0 OR STRPOS(etapa_ensino.descricao ,'2º Ano') <> 0 
        OR STRPOS(etapa_ensino.descricao ,'3º Ano') <> 0 OR STRPOS(etapa_ensino.descricao ,'Educação Infantil') <> 0 
    THEN 'Alfabetização'
    WHEN STRPOS(etapa_ensino.descricao ,'4º Ano') <> 0 OR STRPOS(etapa_ensino.descricao ,'5º Ano') <> 0 THEN 'Complementar'
  ELSE 'Não encontrado'
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
),'') as ciclo,
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
),'') as serie,
COALESCE(to_char(historico_escolar.dias_letivos::float,'999'),COALESCE(to_char((
		SELECT serie.dias_letivos AS serie
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
		)::float,'999'),'')) AS dias_letivos,
        COALESCE(to_char(historico_escolar.carga_horaria::float,'999'),COALESCE(to_char((
			SELECT serie.carga_horaria AS serie
FROM cadastro.etapa_ensino
INNER JOIN pmieducar.turma AS turma_serie ON etapa_ensino.codigo = turma_serie.etapa_educacenso
INNER JOIN pmieducar.serie as serie_serie ON (turma_serie.ref_ref_cod_serie = serie_serie.cod_serie)
INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma_serie.cod_turma
INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula AND matricula.ativo = 1
INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula AND view_situacao.cod_turma = turma_serie.cod_turma 
AND matricula_turma.sequencial = view_situacao.sequencial
INNER JOIN pmieducar.aluno AS aluno_ciclos ON pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
WHERE aluno_ciclos.cod_aluno = aluno.cod_aluno
AND turma_serie.cod_turma = turma.cod_turma LIMIT 1)::float,'999'),'')) AS carga_horaria,
        modules.componente_curricular.tipo_base	
        ,(
            SELECT array_agg(ano_letivo_modulo.sequencial)  FROM pmieducar.ano_letivo_modulo 
            WHERE ano_letivo_modulo.ref_ano = matricula.ano AND ano_letivo_modulo.ref_ref_cod_escola = escola.cod_escola 
            AND current_date >= data_fim
        ) AS exibir_apreceres,
        (
            SELECT max(sequencial)  FROM pmieducar.ano_letivo_modulo 
            WHERE ano_letivo_modulo.ref_ano = matricula.ano AND ano_letivo_modulo.ref_ref_cod_escola = escola.cod_escola
        ) AS semestres,
        (SELECT textcat_all(obs)
          FROM (SELECT observacao AS obs
                  FROM pmieducar.historico_escolar phe
                 WHERE phe.ref_cod_aluno = aluno.cod_aluno
                   AND phe.ativo = 1
                   AND phe.aprovado <> 2
                 ORDER BY phe.ano)tabl) AS observacao_all
        ,COALESCE(parecer_geral_etapa_1.parecer,'') as parecer1
         ,COALESCE(parecer_geral_etapa_2.parecer,'') as parecer2
         ,COALESCE(parecer_geral_etapa_3.parecer,'') as parecer3
         ,COALESCE(parecer_geral_etapa_4.parecer,'') as parecer4
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
INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                       AND view_situacao.sequencial = matricula_turma.sequencial
                                       AND view_situacao.cod_situacao = 9)
INNER JOIN modules.componente_curricular on componente_curricular.id = view_componente_curricular.id
LEFT JOIN pmieducar.historico_escolar  on historico_escolar.ref_cod_aluno = aluno.cod_aluno and historico_escolar.ano = matricula.ano
LEFT JOIN modules.parecer_aluno ON (parecer_aluno.matricula_id = matricula.cod_matricula)
LEFT JOIN modules.parecer_geral as parecer_geral_etapa_1 ON (parecer_geral_etapa_1.parecer_aluno_id = parecer_aluno.id AND parecer_geral_etapa_1.etapa = '1')
LEFT JOIN modules.parecer_geral as parecer_geral_etapa_2 ON (parecer_geral_etapa_2.parecer_aluno_id = parecer_aluno.id AND parecer_geral_etapa_2.etapa = '2')
LEFT JOIN modules.parecer_geral as parecer_geral_etapa_3 ON (parecer_geral_etapa_3.parecer_aluno_id = parecer_aluno.id AND parecer_geral_etapa_3.etapa = '3')
LEFT JOIN modules.parecer_geral as parecer_geral_etapa_4 ON (parecer_geral_etapa_4.parecer_aluno_id = parecer_aluno.id AND parecer_geral_etapa_4.etapa = '4')
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
         cod_aluno,
         modules.componente_curricular.tipo_base,
         modules.componente_curricular.ordenamento
) as t
GROUP BY cod_matricula,cod_aluno,nome_aluno,data_nasc,nome_do_pai,nome_da_mae,cidade_nascimento_uf,uf_nascimento
,cidade_nascimento,situacao_simplificado,frequencia_geral,nota1,nota2,nota3,nota4,legenda
,falta1,falta2,falta3,falta4,componente_order,curricular_nome,curricular_abre,ano,nome_curso,nome_serie,nome_turma
,qtd_modulo,periodo,ciclo,serie,dias_letivos,carga_horaria,tipo_base,exibir_apreceres,semestres,observacao_all
,parecer1,parecer2,parecer3,parecer4,periodo_ano_serie
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
}
