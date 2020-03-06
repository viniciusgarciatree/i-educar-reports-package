<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ShortagesDelaysServantsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'shortages-delays-servants';
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
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $servidor = $this->args['servidor'] ?: 0;
        $dataInicial = $this->args['data_inicial'] ?: 0;
        $dataFinal = $this->args['data_final'] ?: 0;

        return "select 
pe.nome as escola,
(case when fa.tipo = 1 then 'ATRASO' else 'FALTA' end) as tipo,
to_char(data_falta_atraso,'dd/MM/yyyy') as data_falta_atraso,
qtd_horas,
qtd_min,
(case when fa.justificada = 0 then 'SIM' else 'NÃO' end) as justificada,
ps.nome as servidor
 from pmieducar.falta_atraso as fa
left outer join pmieducar.escola esc on fa.ref_cod_escola = esc.cod_escola
left outer join cadastro.pessoa pe on esc.ref_idpes = pe.idpes
left outer join pmieducar.servidor se on fa.ref_cod_servidor = se.cod_servidor
left outer join cadastro.pessoa ps on se.cod_servidor = ps.idpes
where to_char(data_falta_atraso,'yyyy') = '$ano'  " .
            ($dataInicial != 0 ? "AND fa.data_falta_atraso >= '{$dataInicial}' " : " ") .
            ($dataFinal != 0 ? "AND fa.data_falta_atraso <= '{$dataFinal}' " : " ") .
            "AND se.ref_cod_instituicao = $instituicao "
            . ($escola > 0 ? " AND fa.ref_cod_escola = $escola " : "")
            . ($servidor > 0 ? " AND fa.ref_cod_servidor = $servidor " : "") .
            "order by ps.nome, data_falta_atraso";
    }
}
