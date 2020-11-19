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

        unset($this->args['matricula']);
        unset($this->args['rotinas']);
        unset($this->args['registro']);
        unset($this->args['operacao']);
        unset($this->args['data_inicial']);
        unset($this->args['data_final']);
        unset($this->args['hora_inicial']);
        unset($this->args['hora_final']);

        $auditoria = new clsModulesAuditoriaGeral(null, null);
        $auditoria->setOrderby('data_hora DESC');
        $auditoria->setLimite($this->limite, $this->offset);
        $auditoriaLst = $auditoria->lista(
            $matricula,
            $rotinas,
            Portabilis_Date_Utils::brToPgSQL($data_inicial),
            Portabilis_Date_Utils::brToPgSQL($data_final),
            $hora_inicial,
            $hora_final,
            $operacao,
            $registro
        );

        $operacoes = \iEducar\Modules\AuditoriaGeral\Model\Operacoes::getDescriptiveValues();
        $operacoes = array_replace([null => 'Todas'], $operacoes);
        $arrDados = [];

        foreach ($auditoriaLst as $a) {
            $valorAntigo = \iEducar\Modules\AuditoriaGeral\Model\JsonToHtmlTable::transformJsonToHtmlTable($a['valor_antigo']);
            $valorNovo = \iEducar\Modules\AuditoriaGeral\Model\JsonToHtmlTable::transformJsonToHtmlTable($a['valor_novo']);

            $usuario = new clsFuncionario($a['usuario_id']);
            $usuario = $usuario->detalhe();
            $pessoa = new clsPessoaFisica($a['usuario_id']);
            $pessoa = $pessoa->detalhe();

            $operacao = $operacoes[$a['operacao']];

            $dataAuditoria = Portabilis_Date_Utils::pgSQLToBr($a['data_hora']);

            $arrValorAntigo = self::getToArray($valorAntigo);
            $arrValorNovo = self::getToArray($valorNovo);
            $arrMerge = self::mergeArrayAlteracao($arrValorAntigo,$arrValorNovo);

            if(count($arrMerge)>0) {
                $arrDados[] = [
                    'matricula'     => $usuario['matricula'] . " - " . $pessoa['nome'],
                    'rotina'        => ucwords($a['rotina']),
                    'operacao'      => $operacao,
                    'valores'       => $arrMerge,
                    'dataAuditoria' => $dataAuditoria
                ];
            }
        }
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