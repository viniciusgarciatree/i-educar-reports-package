<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsWithBenefitsReport.php';

class StudentsWithBenefitsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999233;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de alunos que recebem benefícios';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de alunos que recebem benefícios', [
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
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->dynamic('situacaoMatricula', ['required' => false]);

        $resources = [
            0 => 'Todos',
            1 => 'Vulnerabilidade',
            2 => 'Bolsa Família',
        ];
        $options = ['label' => 'Tipo do benefício', 'resources' => $resources, 'value' => 0,'required' => false];
        $this->inputsHelper()->select('tipoBeneficio', $options);

        $this->inputsHelper()->checkbox('codigo_nis', ['label' => 'Somente Código NIS?']);

        $resources = [
            1 => 'Analítico',
            2 => 'Quantitativo',
        ];
        $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];
        $this->inputsHelper()->select('modelo', $options);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
        $this->report->addArg('beneficio', (int) $this->getRequest()->tipoBeneficio);
        $this->report->addArg('codigo_nis', $this->getRequest()->codigo_nis ? 1 : 0);
    }

    /**
     * @return StudentsWithBenefitsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsWithBenefitsReport();
    }
}
