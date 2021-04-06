<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/AbsenceServantsReport.php';

class AbsenceServantsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 71;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Relação de servidores afastados';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relação de servidores afastados', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic('ano', ['required' => true]);
        $this->inputsHelper()->dynamic('instituicao', ['required' => true]);
//        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->simpleSearchServidor('servidor', ['label' => 'Servidor', 'required' => false]);
        $this->inputsHelper()->date('data_inicial', ['required' => true, 'label' => 'Data inicial']);
        $this->inputsHelper()->date('data_final', ['required' => true, 'label' => 'Data final']);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int)$this->getRequest()->ano);
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
//        $this->report->addArg('escola', (int)$this->getRequest()->ref_cod_escola);
        $this->report->addArg('servidor', (int)$this->getRequest()->servidor_id);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
    }

    /**
     * @return AbsenceServantsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new AbsenceServantsReport();
    }
}
