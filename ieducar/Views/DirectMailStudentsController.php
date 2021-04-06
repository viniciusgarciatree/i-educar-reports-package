<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/DirectMailStudentsReport.php';

class DirectMailStudentsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 55;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Emissão de etiquetas de mala direta';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão de etiquetas de mala direta', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao'], [], ['required' => false]);
        $this->inputsHelper()->dynamic(['escola', 'curso', 'serie', 'turma'], [], ['options' => ['required' => false]]);
        $this->inputsHelper()->dynamic('matricula', [], ['options' => ['required' => false]]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int)$this->getRequest()->ano);
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int)$this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int)$this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int)$this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int)$this->getRequest()->ref_cod_turma);
        $this->report->addArg('matricula', (int)$this->getRequest()->ref_cod_matricula);
    }

    /**
     * @return DirectMailStudentsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new DirectMailStudentsReport();
    }
}
