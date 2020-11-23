<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'Reports/Tipos/TipoBoletim.php';
require_once 'App/Model/IedFinder.php';

class ReportAuditoriaGeralReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @return string
     *
     * @throws App_Model_Exception
     */
    public function templateName()
    {
        return "reports-auditoria-geral";
    }

    public function getJsonData()
    {
        $arrDados = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        return array_merge(
            [
                'main'   => $arrDados,
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('data_inicial');
        $this->addRequiredArg('data_final');
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

        $matricula    = $this->args['matricula'] ?: 0;
        $rotinas      = $this->args['rotinas'] ?: 0;
        $registro     = $this->args['registro'] ?: 0;
        $operacao     = $this->args['operacao'] ?: null;
        $data_inicial = $this->args['data_inicial'] ?: 0;
        $data_final   = $this->args['data_final'] ?: 0;
        $hora_inicial = $this->args['hora_inicial'] ?: 0;
        $hora_final   = $this->args['hora_final'] ?: 0;

        $data_inicial = Portabilis_Date_Utils::brToPgSQL($data_inicial);
        $data_final = Portabilis_Date_Utils::brToPgSQL($data_final);

        unset($this->args['matricula']);
        unset($this->args['rotinas']);
        unset($this->args['registro']);
        unset($this->args['operacao']);
        unset($this->args['data_inicial']);
        unset($this->args['data_final']);
        unset($this->args['hora_inicial']);
        unset($this->args['hora_final']);


        $whereAnd = " WHERE ";
        $filtros = " ";

        if (is_string($rotinas)) {
            $filtros .= "{$whereAnd} rotina ILIKE '%{$rotinas}%'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($operacao)) {
            $filtros .= "{$whereAnd} operacao = {$operacao}";
            $whereAnd = ' AND ';
        }

        if (is_string($registro)) {
            $filtros .= "{$whereAnd} codigo = '{$registro}'";
            $whereAnd = ' AND ';
        }

        if (is_string($matricula)) {
            $filtros .= "{$whereAnd} EXISTS (SELECT 1
                                         FROM portal.funcionario
                                        WHERE funcionario.ref_cod_pessoa_fj = auditoria_geral.usuario_id
                                          AND funcionario.matricula = '{$matricula}')";
            $whereAnd = ' AND ';
        }

        if (is_string($data_inicial)) {
            $filtros .= "{$whereAnd} data_hora::date >= '{$data_inicial}'";
            $whereAnd = ' AND ';
        }

        if (is_string($data_final)) {
            $filtros .= "{$whereAnd} data_hora::date <= '{$data_final}'";
            $whereAnd = ' AND ';
        }

        if (is_string($hora_inicial)) {
            $filtros .= "{$whereAnd} data_hora::time >= '{$hora_inicial}'";
            $whereAnd = ' AND ';
        }

        if (is_string($hora_final)) {
            $filtros .= "{$whereAnd} data_hora::time <= '{$hora_final}'";
            $whereAnd = ' AND ';
        }

        // $usuario['matricula'] . " - " . $pessoa['nome'],

        $sql = "
SELECT 
    pessoa.nome as matricula,
    auditoria_geral.rotina,
    CASE 
        WHEN auditoria_geral.operacao = 1 THEN 'Novo' 
        WHEN auditoria_geral.operacao = 2 THEN 'Edição' 
        WHEN auditoria_geral.operacao = 3 THEN 'Exclusão' 
        ELSE '' 
    END as operacao,
    to_char(auditoria_geral.data_hora,'dd/mm/yyyy hh:ss:ii') AS data_auditoria,
    '' as valores
FROM modules.auditoria_geral
INNER JOIN cadastro.pessoa ON pessoa.idpes = auditoria_geral.usuario_id
  {$filtros}
ORDER BY auditoria_geral.data_hora
        ";

        $arrDados = Portabilis_Utils_Database::fetchPreparedQuery($sql);

        return $arrDados;
    }

    private function getToArray($arrValor)
    {
        $arrValor = explode("<tr>",$arrValor);
        $arrVal = [];
        foreach ($arrValor as $value)
        {
            $aux = trim(str_replace("</td><td ","</td>\n<td ",$value));
            $arrAux = [];
            $arrValAux = [];
            $arrAux[] = explode("|",str_replace("\n","|",$aux));
            foreach ($arrAux as $valueAux) {
                if(count($valueAux)>1) {
                    foreach ($valueAux as $val) {
                        $arrValAux[] = trim(strip_tags($val));
                    }
                    $arrValAux = ['campo'=>$arrValAux[0], 'valor' => $arrValAux[1]];
                }
            }
            if(count($arrValAux)>0) {
                $arrVal[] = $arrValAux;
            }

        }
        return $arrVal;
    }

    private function mergeArrayAlteracao($arrValorAntigo, $arrValorNovo)
    {
        $arrMerge = [];
        $buscaCampoValorDiferente = function ($arrValorNovo, $campo, $valor, &$valido = false)
        {
            $novoValor = "";
            foreach ($arrValorNovo as $index => $value){
                if($campo == $value['campo'] && $value['valor'] != $valor){
                    $novoValor = $value['valor'];
                    $valorAux = str_replace("T"," ",$value['valor']);
                    $valorAux = Portabilis_Date_Utils::pgSQLToBr($valorAux);
                    if($valorAux){
                        $novoValor = $valorAux;
                    }
                    $valido = true;
                    break;
                }
            }
            return $novoValor;
        };

        foreach ($arrValorAntigo as $index => $value){
            $valido = false;
            $valorNovo = $buscaCampoValorDiferente($arrValorNovo, $value['campo'], $value['valor'], $valido);
            if($valido) {
                $valorAux = str_replace("T"," ",$value['valor']);
                $valorAux = Portabilis_Date_Utils::pgSQLToBr($valorAux);
                $antigoValor = $value['valor'];
                if($valorAux){
                    $antigoValor = $valorAux;
                }
                $arrMerge[] = [
                    'campo' => $value['campo'],
                    'valorAntigo' => $antigoValor,
                    'valorNovo' => $valorNovo,
                    ];
            }
        }
        return $arrMerge;
    }
}