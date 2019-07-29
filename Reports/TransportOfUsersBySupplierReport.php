<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TransportOfUsersBySupplierReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'transport-of-users-by-supplier';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $empresa = $this->args['empresa'] ?: 0;
        $rota = $this->args['rota'] ?: 0;

        return "
       SELECT pessoa.nome AS nome_usuario_transporte,
       destino_rota.nome AS nome_destino_rota,
       rota_transporte_escolar.descricao AS rota,
       empresa.nome as fornecedor
  FROM modules.pessoa_transporte
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = pessoa_transporte.ref_idpes)
  LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = pessoa.idpes)
  LEFT JOIN public.logradouro ON (logradouro.idlog = endereco_pessoa.idlog)
  LEFT JOIN cadastro.fone_pessoa ON (fone_pessoa.idpes = pessoa.idpes)
 INNER JOIN modules.rota_transporte_escolar ON (rota_transporte_escolar.cod_rota_transporte_escolar = pessoa_transporte.ref_cod_rota_transporte_escolar)
 LEFT JOIN modules.empresa_transporte_escolar ON (empresa_transporte_escolar.cod_empresa_transporte_escolar = rota_transporte_escolar.ref_cod_empresa_transporte_escolar)
  LEFT JOIN cadastro.pessoa AS empresa ON (empresa.idpes = empresa_transporte_escolar.ref_idpes)
  LEFT JOIN cadastro.pessoa AS destino_rota ON (destino_rota.idpes = rota_transporte_escolar.ref_idpes_destino)
 WHERE rota_transporte_escolar.ano = {$ano}
 AND (CASE WHEN {$rota} = 0 THEN TRUE ELSE rota_transporte_escolar.cod_rota_transporte_escolar = {$rota} END)
 AND (CASE WHEN {$empresa} = 0 THEN TRUE ELSE empresa_transporte_escolar.cod_empresa_transporte_escolar = {$empresa} END)
 ORDER BY rota, fornecedor, relatorio.get_texto_sem_caracter_especial(pessoa.nome)
       ";
    }
}
