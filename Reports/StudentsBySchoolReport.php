<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsBySchoolReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-by-school';
//        return 'students-not-enrolled';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
//        $this->addRequiredArg('escola');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "SELECT count(aluno.cod_aluno),
       escola.cod_escola,
       (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
               ps.idpes = escola.ref_idpes),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS nm_escola

  FROM pmieducar.aluno,
       pmieducar.matricula,
       pmieducar.escola

 WHERE aluno.cod_aluno = matricula.ref_cod_aluno AND
       matricula.ativo = 1 AND
       matricula.ano = $ano AND
       matricula.ref_ref_cod_escola = escola.cod_escola AND
       escola.ref_cod_instituicao = $instituicao".($situacao == 9 ? "" : " AND
       matricula.aprovado = $situacao").

"group by escola.cod_escola, nm_escola

Order by nm_escola";

    }
}
