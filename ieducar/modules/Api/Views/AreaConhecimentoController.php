<?php

#error_reporting(E_ALL);
#ini_set("display_errors", 1);

/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 * @category  i-Educar
 * @license   @@license@@
 * @package   Api
 * @subpackage  Modules
 * @since   Arquivo disponível desde a versão ?
 * @version   $Id$
 */

require_once 'lib/Portabilis/Controller/ApiCoreController.php';
require_once 'lib/Portabilis/Array/Utils.php';
require_once 'lib/Portabilis/String/Utils.php';
require_once 'lib/Portabilis/Utils/Database.php';

class AreaConhecimentoController extends ApiCoreController
{

  function canGetAreasDeConhecimento(){
    return  $this->validatesPresenceOf('instituicao_id');
  }

  function getAreasDeConhecimento(){
    if($this->canGetAreasDeConhecimento()){

      $instituicaoId = $this->getRequest()->instituicao_id;

      $sql = 'SELECT id, nome, ordenamento_ac
                FROM modules.area_conhecimento
                WHERE instituicao_id = $1
                ORDER BY nome ';

      $areas = $this->fetchPreparedQuery($sql, array($instituicaoId));

      $attrs = array('id', 'nome', 'ordenamento_ac');
      $areas = Portabilis_Array_Utils::filterSet($areas, $attrs);

      foreach ($areas as &$disciplina) {
        $disciplina['nome'] = Portabilis_String_Utils::toUtf8($disciplina['nome']);
      }

      return array('areas' => $areas);
    }
  }

  protected function getAreasDeConhecimentoForSerie() {

         $serieId = $this->getRequest()->serie_id;
    
         $sql    = 'SELECT ac.id as id,
                           (ac.nome) as nome
                      from modules.area_conhecimento ac
                     where ac.id in(select area_conhecimento.id 
                                      from modules.area_conhecimento
                                     inner join modules.componente_curricular cc on(cc.area_conhecimento_id = ac.id)
                                     inner join modules.componente_curricular_ano_escolar ccae on (ccae.componente_curricular_id = cc.id
                                                                                                   and ccae.ano_escolar_id = $1))
                     order by (lower(ac.nome)) ASC';

        $paramsSql = array($serieId);
        $areasConhecimento = $this->fetchPreparedQuery($sql, $paramsSql);
        $options = array();
        $options = Portabilis_Array_Utils::setAsIdValue($areasConhecimento, 'id', 'nome');
    
        return array('options' => $options);
    
      }

  protected function getAreasDeConhecimentoForEscolaSerie() {

     $escolaId = $this->getRequest()->escola_id;
     $serieId = $this->getRequest()->serie_id;

     $sql    = 'SELECT ac.id as id,
                       (ac.nome) as nome
                  from modules.area_conhecimento ac
                 where ac.id in(select area_conhecimento.id 
                                  from modules.area_conhecimento
                                 inner join modules.componente_curricular cc on(cc.area_conhecimento_id = ac.id)
                     inner join pmieducar.escola_serie_disciplina esd on(esd.ref_cod_disciplina = cc.id)
                     where esd.ref_ref_cod_escola = $1
                       and ref_ref_cod_serie = $2)
                 order by (lower(ac.nome)) ASC';


    $paramsSql = array($escolaId, $serieId);
    $areasConhecimento = $this->fetchPreparedQuery($sql, $paramsSql);
    $options = array();
    $options = Portabilis_Array_Utils::setAsIdValue($areasConhecimento, 'id', 'nome');

    return array('options' => $options);

  }

  public function Gerar() {
    if ($this->isRequestFor('get', 'areas-de-conhecimento'))
      $this->appendResponse($this->getAreasDeConhecimento());
      elseif($this->isRequestFor('get', 'areaconhecimento-serie'))
        $this->appendResponse($this->getAreasDeConhecimentoForSerie());
      elseif($this->isRequestFor('get', 'areaconhecimento-escolaserie'))
        $this->appendResponse($this->getAreasDeConhecimentoForEscolaSerie());
    else
      $this->notImplementedOperationError();
  }
}