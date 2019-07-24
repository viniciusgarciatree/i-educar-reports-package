<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/AmountOfLoanToReadersReport.php';

class AmountOfLoanToReadersController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 57;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Quantidade de emprestimos por leitor';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Quantidade de emprestimos por leitor', [
            'educar_biblioteca_index.php' => 'Biblioteca',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
        $this->inputsHelper()->dynamic(['BibliotecaPesquisaCliente', 'dataInicial', 'dataFinal'], ['required' => false]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        if (!isset($_POST['ref_cod_cliente']) || trim($_POST['ref_cod_cliente']) == '') {
            $this->report->addArg('cliente', 0);
        } else {
            $this->report->addArg('cliente', (int) $this->getRequest()->ref_cod_cliente);
        }
        $this->report->addArg('dt_inicial', $this->getRequest()->data_inicial);
        $this->report->addArg('dt_final', $this->getRequest()->data_final);
    }

    /**
     * @return AmountOfLoanToReadersReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new AmountOfLoanToReadersReport();
    }
}
