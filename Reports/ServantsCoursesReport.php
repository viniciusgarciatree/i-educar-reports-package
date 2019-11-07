<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ServantsCoursesReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'servants-courses';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
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
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
//        $funcao = $this->args['funcao'] ?: 0;
//        $vinculo = $this->args['vinculo'] ?: 0;
        $periodo = $this->args['periodo'] ?: 0;
//        $nao_emitir_afastados = $this->args['nao_emitir_afastados'];

        return "select * from (
SELECT 
distinct on(servidor.cod_servidor, employee_graduations.course_id) cod_servidor,
servidor_funcao.matricula,
pessoa.nome AS nm_servidor,
completion_year,
educacenso_curso_superior.nome as curso,
educacenso_ies.nome as instituicao,
employee_graduation_disciplines.name as disciplina
 FROM pmieducar.instituicao
 INNER JOIN pmieducar.servidor ON (servidor.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = servidor.cod_servidor)
  LEFT JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
  LEFT JOIN pmieducar.servidor_funcao ON (servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao)
  LEFT JOIN employee_graduations ON (employee_graduations.employee_id = servidor.cod_servidor)
  LEFt JOIN modules.educacenso_curso_superior ON (educacenso_curso_superior.id = employee_graduations.course_id)
  LEFt JOIN modules.educacenso_ies ON (educacenso_ies.id = employee_graduations.college_id)
  LEFt JOIN employee_graduation_disciplines ON (employee_graduation_disciplines.id = employee_graduations.discipline_id)
  LEFT JOIN pmieducar.escola ON (servidor_alocacao.ref_cod_escola = escola.cod_escola)
  LEFT JOIN cadastro.juridica pessoa_juridica ON (pessoa_juridica.idpes = escola.ref_idpes)
  LEFT JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola AND escola_ano_letivo.ano = {$ano})
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND employee_graduations.course_id is not null
   AND servidor.ativo = 1
   AND servidor_alocacao.ano = {$ano}
   AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
   AND (CASE WHEN {$periodo} = 0 THEN TRUE ELSE servidor_alocacao.periodo = {$periodo} END)
 ORDER BY servidor.cod_servidor) as tmp  order by nm_servidor";
    }
}
