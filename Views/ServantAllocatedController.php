<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/ServantAllocatedReport.php';

class ServantAllocatedController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 71;

    /**
     * @var string
     */
    protected $_titulo = 'Alocação servidores';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Alocação servidores', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
//        $this->inputsHelper()->dynamic('serie', ['required' => true]);
//        $this->inputsHelper()->dynamic('turma', ['required' => true]);
//        $this->inputsHelper()->dynamic('matricula', ['required' => false]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
//        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
//        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
//        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
//        $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
    }

    /**
     * @return ServantAllocatedReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ServantAllocatedReport();
    }
}
