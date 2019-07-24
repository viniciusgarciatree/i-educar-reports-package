<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/LibraryTagsReport.php';

class LibraryTagsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 57;

    /**
     * @var string
     */
    protected $_titulo = 'Emissão de etiquetas';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão de etiquetas', [
            'educar_biblioteca_index.php' => 'Biblioteca',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
        $this->inputsHelper()->dynamic(['BibliotecaPesquisaObra'], ['required' => false]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('acervo', (int) $this->getRequest()->ref_cod_acervo);
    }

    /**
     * @return LibraryTagsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new LibraryTagsReport();
    }
}
