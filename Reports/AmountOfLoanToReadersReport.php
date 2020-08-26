<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class AmountOfLoanToReadersReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'amount-of-loan-to-readers';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
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
        $dt_inicial = $this->args['dt_inicial'] ?: 0;
        $dt_final = $this->args['dt_final'] ?: 0;
        $cliente = $this->args['cliente'] ?: 0;

        if(!is_numeric($dt_inicial)){
            list($mth,$day,$yr)=explode("/",$dt_inicial);
            $dt_inicial = (int) $yr.$mth.$day;
        }

        if(!is_numeric($dt_final)){
            list($mth,$day,$yr)=explode("/",$dt_final);
            $dt_final = (int) $yr.$mth.$day;
        }

        return "select nome, count(*) as qtd from pmieducar.exemplar_emprestimo ee
left outer join pmieducar.cliente cli on cli.cod_cliente = ee.ref_cod_cliente
left outer join cadastro.pessoa pe on cli.ref_idpes = pe.idpes
left outer join pmieducar.exemplar ex on ex.cod_exemplar = ee.ref_cod_exemplar
left outer join pmieducar.acervo ac on ac.cod_acervo = ex.ref_cod_acervo
left outer join pmieducar.biblioteca ba on ba.cod_biblioteca = ac.ref_cod_biblioteca
where ba.ref_cod_instituicao = $instituicao AND ba.ref_cod_escola = $escola "
            . ($cliente > 0 ? " AND ee.ref_cod_cliente = $cliente " : " ")
            . ($dt_inicial > 0 ? " AND to_char(ee.data_retirada, 'YYYYMMDD')::integer >= {$dt_inicial} " : " ")
            . ($dt_final > 0 ? " AND to_char(ee.data_retirada, 'YYYYMMDD')::integer <= {$dt_final} " : " ") .
            " group by 1  order by 2 desc;";
    }
}
