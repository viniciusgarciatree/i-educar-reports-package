<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/LibraryLoansReport.php';

class LibraryLoansController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 57;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de Empréstimos';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de Empréstimos', [
            'educar_biblioteca_index.php' => 'Biblioteca',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
        $this->inputsHelper()->dynamic(['BibliotecaPesquisaObra', 'BibliotecaPesquisaCliente', 'dataInicial', 'dataFinal'], ['required' => false]);
        $this->inputsHelper()->select('situacao', [
            'label' => 'Situação',
            'resources' => [
                1 => 'Todos',
                2 => 'Em Atraso',
            ],
            'value' => 1
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('acervo', (int) $this->getRequest()->ref_cod_acervo);

        if (!isset($_POST['ref_cod_cliente']) || trim($_POST['ref_cod_cliente']) == '') {
            $this->report->addArg('cliente', 0);
        } else {
            $this->report->addArg('cliente', (int) $this->getRequest()->ref_cod_cliente);
        }

        $this->report->addArg('dt_inicial', $this->getRequest()->data_inicial);
        $this->report->addArg('dt_final', $this->getRequest()->data_final);
        $this->report->addArg('situacao', $this->getRequest()->situacao);
    }

    /**
     * @return LibraryLoansReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new LibraryLoansReport();
    }
}
