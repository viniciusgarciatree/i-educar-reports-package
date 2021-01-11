<?php

use iEducar\Reports\JsonDataSource;
use Illuminate\Support\Facades\DB;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class DirectMailStudentsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'direct-mail-students';
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
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport   = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        $arrMain   = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);

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
     */
    public function getSqlMainReport()
    {
        $escola = $this->args['escola'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        $this->args['exibir_cabecalho'] = $this->getExibirCabecalho() ? 'true' : 'false';

        return "select 
distinct on (ref_cod_aluno) b.nome as aluno, 
logradouro, 
numero, 
complemento, 
bairro, 
cidade, 
cep 
FROM pmieducar.matricula d 
left outer join pmieducar.aluno a on a.cod_aluno = d.ref_cod_aluno
left outer join cadastro.pessoa b on a.ref_idpes = b.idpes
left outer join cadastro.v_endereco c on a.ref_idpes = c.idpes
left outer join pmieducar.escola e on d.ref_ref_cod_escola = e.cod_escola
left outer join pmieducar.matricula_turma mt on mt.ref_cod_matricula = d.cod_matricula
where logradouro is not null  and d.ativo = 1 AND
       d.ano = $ano AND
       e.ref_cod_instituicao = $instituicao"
            .($escola == 0 ? '' : " AND cod_escola = $escola")
            .($curso == 0 ? '' : " AND d.ref_cod_curso = $curso")
            .($serie == 0 ? '' : " AND d.ref_ref_cod_serie = $serie")
            .($matricula == 0 ? '' : " AND d.cod_matricula = $matricula")
            .($turma == 0 ? '' : " AND mt.ref_cod_turma = $turma").
'  ';
    }

    protected function getExibirCabecalho()
    {
        $result = DB::select('select EXISTS (select * from public.settings where key = \'report.header.mala_direta\' AND value = \'1\');');
        if (count($result) > 0 && $result[0]['exists'] == true) {
            return true;
        }

        return false;
    }
}
