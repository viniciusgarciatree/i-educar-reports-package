<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsWithBenefitsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    private $templateName = 'students-with-benefits';

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return $this->templateName;
    }

    private function setTemplateName($name = ""){
        $this->templateName = !empty($name) ? $name : 'students-with-benefits' ;
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        //$this->addRequiredArg('escola');
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
     */
    public function getSqlMainReport()
    {
        $situacao = $this->args['situacao'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $ano = $this->args['ano'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];
        $modelo = $this->args['modelo'];


        // Quantitaivo
        if($modelo == 2){
            self::setTemplateName('students-with-benefits-quantitative');
            return "
select 
quantidade::integer,ano::integer,(to_char(now(),'YYYY'))::integer - ano::integer as idade  
from (
SELECT count(*) as quantidade,to_char(fisica.data_nasc,'YYYY') AS ano
  FROM pmieducar.matricula
  LEFT OUTER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
  LEFT OUTER JOIN cadastro.fisica ON (aluno.ref_idpes = fisica.idpes)
  LEFT OUTER JOIN cadastro.pessoa ON (fisica.idpes = pessoa.idpes)
  LEFT OUTER JOIN pmieducar.aluno_aluno_beneficio ON (aluno_aluno_beneficio.aluno_id = aluno.cod_aluno)
  LEFT OUTER JOIN pmieducar.aluno_beneficio ON (aluno_beneficio.cod_aluno_beneficio = aluno_aluno_beneficio.aluno_beneficio_id)
  LEFT OUTER JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
  LEFT OUTER JOIN pmieducar.curso ON (curso.cod_curso = matricula.ref_cod_curso)
  LEFT OUTER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
  LEFT OUTER JOIN pmieducar.turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
  LEFT OUTER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
AND view_situacao.cod_turma = turma.cod_turma
        AND view_situacao.cod_situacao = {$situacao}
        AND matricula_turma.sequencial = view_situacao.sequencial)
  LEFT OUTER JOIN pmieducar.escola on matricula.ref_ref_cod_escola = escola.cod_escola
  LEFT OUTER JOIN cadastro.pessoa pe on escola.ref_idpes = pe.idpes
 WHERE escola.ref_cod_instituicao = {$instituicao}
   AND matricula.ultima_matricula = 1
   AND matricula.ativo = 1
   AND aluno.ativo = 1
   AND matricula.ano = {$ano}
   AND nm_beneficio IS NOT NULL 
   AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
   AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE curso.cod_curso = {$curso} END)
   AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE serie.cod_serie = {$serie} END)
   AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
 GROUP BY to_char(fisica.data_nasc,'YYYY')
) as t ORDER BY ano desc
        ";
        }

        $return = "
SELECT pe.nome as escola,
       cod_aluno,
       pessoa.nome AS aluno,
       pessoa.nome,
       fisica.nis_pis_pasep,
       serie.nm_serie,
       turma.nm_turma,
       textcat_all(distinct nm_beneficio) AS nm_beneficio,
       (SELECT cod_aluno_inep
          FROM modules.educacenso_cod_aluno
         WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) AS cod_inep,
       view_situacao.texto_situacao AS situacao
  FROM pmieducar.matricula
  LEFT OUTER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
  LEFT OUTER JOIN cadastro.fisica ON (aluno.ref_idpes = fisica.idpes)
  LEFT OUTER JOIN cadastro.pessoa ON (fisica.idpes = pessoa.idpes)
  LEFT OUTER JOIN pmieducar.aluno_aluno_beneficio ON (aluno_aluno_beneficio.aluno_id = aluno.cod_aluno)
  LEFT OUTER JOIN pmieducar.aluno_beneficio ON (aluno_beneficio.cod_aluno_beneficio = aluno_aluno_beneficio.aluno_beneficio_id)
  LEFT OUTER JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
  LEFT OUTER JOIN pmieducar.curso ON (curso.cod_curso = matricula.ref_cod_curso)
  LEFT OUTER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
  LEFT OUTER JOIN pmieducar.turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
  LEFT OUTER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
					AND view_situacao.cod_turma = turma.cod_turma
				        AND view_situacao.cod_situacao = {$situacao}
				        AND matricula_turma.sequencial = view_situacao.sequencial)
  LEFT OUTER JOIN pmieducar.escola on matricula.ref_ref_cod_escola = escola.cod_escola
  LEFT OUTER JOIN cadastro.pessoa pe on escola.ref_idpes = pe.idpes
 WHERE escola.ref_cod_instituicao = {$instituicao}
   AND matricula.ultima_matricula = 1
   AND matricula.ativo = 1
   AND aluno.ativo = 1
   AND matricula.ano = {$ano}
   AND nm_beneficio IS NOT NULL 
   AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
   AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE curso.cod_curso = {$curso} END)
   AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE serie.cod_serie = {$serie} END)
   AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
 GROUP BY pe.nome, cod_aluno, aluno, nm_serie, nm_turma, fisica.nis_pis_pasep, texto_situacao
 ORDER BY pe.nome, relatorio.get_texto_sem_caracter_especial(pessoa.nome), nm_beneficio
        ";

        return $return;
    }
}
