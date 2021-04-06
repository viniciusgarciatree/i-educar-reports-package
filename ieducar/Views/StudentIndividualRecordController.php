<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentIndividualRecordReport.php';

class StudentIndividualRecordController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 9999200;

    /**
     * @var string
     */
    protected $_titulo = 'Ficha Individual do Aluno';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Ficha Individual do Aluno', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano','instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->simpleSearchAluno(null);
        $this->inputsHelper()->checkbox('exibir_paracer_descritivo', ['label' => 'Exibir Parecer Descritivo se existir?']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $rootPath = dirname(dirname(dirname(dirname(__FILE__))));
        $filePath = $rootPath . '/modules/Reports/ReportLogos/';

        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('aluno', (int) $this->getRequest()->aluno_id);
        $this->report->addArg('exibir_paracer_descritivo', (bool) $this->getRequest()->exibir_paracer_descritivo);

        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('portaria_aprovacao_pontos', (string) $GLOBALS['coreExt']['Config']->report->portaria_aprovacao_pontos);

        $this->report->addArg('logo1', (string) $filePath.'brasil.png');
        $this->report->addArg('logo2', (string) $filePath.'brasao-prefeitura-simples.png');
    }

    /**
     * @return SchoolHistoryReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentIndividualRecordReport();
    }
}