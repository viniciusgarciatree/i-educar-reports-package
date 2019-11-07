<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/MonthlyClosingServantsReport.php';

class MonthlyClosingServantsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999820;

    /**
     * @var string
     */
    protected $_titulo = 'Quadro de frequência dos servidores';

    /**
     * @var int
     */
    public $periodo;

    /**
     * @var int
     */
    public $cod_servidor_funcao;

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Quadro de frequência dos servidores', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'vinculo']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('vinculo', ['required' => false]);

        $obj_funcoes = new clsPmieducarFuncao();
        $lista_funcoes = $obj_funcoes->lista();
        $opcoes = ['' => 'Selecione'];

        if ($lista_funcoes) {
            foreach ($lista_funcoes as $funcao) {
                $opcoes[$funcao['cod_funcao']] = $funcao['nm_funcao'];
            }
        }

//        $periodo = [
//            0 => 'Todos',
//            1 => 'Matutino',
//            2 => 'Vespertino',
//            3 => 'Noturno'
//        ];

        $this->campoLista('funcao', 'Fun&ccedil;&atilde;o', $opcoes, $this->cod_servidor_funcao, '', false, '', '', false, false);
//        $this->campoLista('periodo', 'Per&iacute;odo', $periodo, $this->periodo, null, false, '', '', false, false);
        $this->inputsHelper()->date('data_inicial', ['required' => true, 'label' => 'Data inicial']);
        $this->inputsHelper()->date('data_final', ['required' => true, 'label' => 'Data final']);
//        $this->inputsHelper()->checkbox('emitir_totalizadores', ['label' => 'Adicionar totalizadores ao fim do relatório']);
//        $this->inputsHelper()->checkbox('nao_emitir_afastados', ['label' => 'Não emitir servidores afastados']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('funcao', (int) $this->getRequest()->funcao);
        $this->report->addArg('vinculo', (int) $this->getRequest()->vinculo_id);
//        $this->report->addArg('periodo', (int) $this->getRequest()->periodo);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
//        $this->report->addArg('emitir_totalizadores', (bool) $this->getRequest()->emitir_totalizadores);
//        $this->report->addArg('nao_emitir_afastados', (bool) $this->getRequest()->nao_emitir_afastados);
    }

    /**
     * @return MonthlyClosingServantsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new MonthlyClosingServantsReport();
    }
}
