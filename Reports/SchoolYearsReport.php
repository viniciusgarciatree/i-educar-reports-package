<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class SchoolYearsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'school-years';
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
     */
    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;

        return "select 
ano,
pe.nome as escola,
(case when eal.andamento = 0 then 'Não Iniciado' when eal.andamento = 1 then 'Em Andamento' else 'Finalizado' end) as situacao
 from pmieducar.escola_ano_letivo as eal
left outer join pmieducar.escola esc on eal.ref_cod_escola = esc.cod_escola
left outer join cadastro.pessoa pe on esc.ref_idpes = pe.idpes
where eal.ano >= (select ano-4 from pmieducar.escola_ano_letivo order by ano desc limit 1) AND 
esc.ref_cod_instituicao = $instituicao "
            .($escola > 0 ? " AND eal.ref_cod_escola = $escola " : '').
'order by pe.nome, eal.ano';
    }
}
