<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/TransportOfUsersBySupplierReport.php';
require_once 'include/modules/clsModulesRotaTransporteEscolar.inc.php';
require_once 'Portabilis/Date/Utils.php';

class TransportOfUsersBySupplierController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 69;

    /**
     * @var string
     */
    protected $_titulo = 'Relação de usuários do transporte escolar por fornecedor';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relação de usuários do transporte escolar por fornecedor', [
            'educar_transporte_escolar_index.php' => 'Transporte escolar',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic('ano', ['required' => true]);
        $this->inputsHelper()->dynamic('instituicao', ['required' => true]);
        $this->inputsHelper()->simpleSearchEmpresa(null, ['required' => false]);
        // Montar o inputsHelper->select \/
        // Cria lista de rotas
        $obj_rota = new clsModulesRotaTransporteEscolar();
        $obj_rota->setOrderBy(' descricao asc ');
        $lista_rota = $obj_rota->lista();
        $rota_resources = ['' => 'Selecione uma rota'];
        foreach ($lista_rota as $reg) {
            $rota_resources["{$reg['cod_rota_transporte_escolar']}"] = "{$reg['descricao']} - {$reg['ano']}";
        }

        // Rota
        $options = [
            'label' => 'Rota',
            'required' => false,
            'resources' => $rota_resources
        ];
        $this->inputsHelper()->select('rota', $options);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('empresa', ((int) $this->getRequest()->empresa_id == null ? 0 : (int) $this->getRequest()->pessoaj_id));
        $this->report->addArg('rota', (int) $this->getRequest()->rota);
    }

    /**
     * @return TransportOfUsersBySupplierReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TransportOfUsersBySupplierReport();
    }
}
