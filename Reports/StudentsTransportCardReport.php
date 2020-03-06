<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsTransportCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-transport-card';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "select public.fcn_upper(instituicao.nm_instituicao) as nm_instituicao,
       public.fcn_upper(instituicao.nm_responsavel) as nm_responsavel,
       aluno.cod_aluno,
       escola_ano_letivo.ano as ano_letivo,
       (SELECT cod_aluno_inep
          FROM modules.educacenso_cod_aluno
         WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as inep,
               pessoa.nome as nome_aluno,

       (SELECT CASE WHEN fisica.sexo = 'M' THEN
                        'Masculino'
                     ELSE
                        'Feminino'
                     END) as sexo,
       to_char(fisica.data_nasc,'dd/mm/yyyy') as data_nasc,
       (SELECT documento.rg from cadastro.documento where documento.idpes = fisica.idpes) as identidade,
       curso.nm_curso as nome_curso,
       turma.nm_turma as nome_turma,
       serie.nm_serie as nome_serie,
       turma.cod_turma as cod_turma,

      (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
			   ps.idpes = escola.ref_idpes),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS nm_escola,

      (SELECT fcn_upper(substring(logradouro.idtlog from 1 for 1)) ||
              lower(substring(logradouro.idtlog, 2))
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes) AS tipo_logradouro,

      (SELECT COALESCE((SELECT COALESCE((SELECT logradouro.nome
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.logradouro FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT logradouro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS logradouro,

      (SELECT COALESCE((SELECT COALESCE((SELECT bairro.nome
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.bairro FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT bairro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS bairro,

      (SELECT COALESCE((SELECT COALESCE ((SELECT municipio.nome
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes), (SELECT endereco_externo.cidade FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT municipio FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS municipio,

      (SELECT COALESCE((SELECT COALESCE((SELECT endereco_pessoa.numero
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.numero FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT numero FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS numero,


      (SELECT COALESCE((SELECT COALESCE((SELECT municipio.sigla_uf
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.sigla_uf FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(select inst.ref_sigla_uf from pmieducar.instituicao inst where inst.cod_instituicao = instituicao.cod_instituicao))) AS uf_municipio,

     (SELECT COALESCE((SELECT min(fone_pessoa.ddd)
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT min(ddd_telefone) FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS fone_ddd,

     (SELECT COALESCE((SELECT COALESCE((SELECT to_char(endereco_pessoa.cep, '99999-999')
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT to_char(endereco_externo.cep,'99999-999') FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT to_char(escola_complemento.cep,'99999-999') FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS cep,


     (SELECT COALESCE((SELECT min(to_char(fone_pessoa.fone, '9999-9999'))
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT min(to_char(telefone, '9999-9999')) FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS fone,

   (SELECT COALESCE((SELECT ps.email
         FROM cadastro.pessoa ps,
              cadastro.juridica
        WHERE juridica.idpes = ps.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT email FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS email,

       to_char(current_date,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
    (SELECT CASE WHEN date_part('hour',hora_inicial) = 6 or date_part('hour',hora_inicial) = 7 THEN 'Matutino'
                     WHEN date_part('hour',hora_inicial) = 13 or date_part('hour',hora_inicial) = 14 THEN 'Verpertino'
                     WHEN date_part('hour',hora_inicial) = 18 or date_part('hour',hora_inicial) = 19 THEN 'Noturno'
                END
	       FROM pmieducar.turma,
                pmieducar.matricula_turma
          WHERE turma.cod_turma = matricula_turma.ref_cod_turma AND
                matricula_turma.ref_cod_matricula = matricula.cod_matricula AND
                turma.ativo = 1 AND
		        matricula_turma.ativo = 1 AND
                hora_inicial IS NOT NULL) AS periodo,

(select infra_predio.nm_predio
   from pmieducar.infra_predio_comodo,
        pmieducar.infra_comodo_funcao,
        pmieducar.infra_predio
  where infra_comodo_funcao.cod_infra_comodo_funcao = infra_predio_comodo.ref_cod_infra_comodo_funcao  AND
        infra_comodo_funcao.ref_cod_escola = escola.cod_escola AND
        infra_predio.cod_infra_predio = infra_predio_comodo.ref_cod_infra_predio AND
        infra_predio.ref_cod_escola = escola.cod_escola AND
        infra_predio.ativo = 1 AND
        infra_predio_comodo.cod_infra_predio_comodo = turma.ref_cod_infra_predio_comodo) as predio,

(select nm_comodo
   from pmieducar.infra_predio_comodo,
        pmieducar.infra_comodo_funcao,
        pmieducar.infra_predio
  where infra_comodo_funcao.cod_infra_comodo_funcao = infra_predio_comodo.ref_cod_infra_comodo_funcao  AND
        infra_comodo_funcao.ref_cod_escola = escola.cod_escola AND
        infra_predio.cod_infra_predio = infra_predio_comodo.ref_cod_infra_predio AND
        infra_predio.ref_cod_escola = escola.cod_escola AND
        infra_predio.ativo = 1 AND
        infra_predio_comodo.cod_infra_predio_comodo = turma.ref_cod_infra_predio_comodo) as sala,

 (SELECT CASE WHEN matricula.aprovado = 1 THEN 'Aprovado'
                   WHEN matricula.aprovado = 2 THEN 'Reprovado'
                   WHEN matricula.aprovado = 3 THEN 'Em Andamento'
                   WHEN matricula.aprovado = 4 THEN 'Transferido'
                   WHEN matricula.aprovado = 6 THEN 'Abandono'
                   WHEN matricula.aprovado = 32 THEN 'Desistente'
          END) as situacao

  from pmieducar.instituicao,
       pmieducar.aluno,
       cadastro.fisica,
       cadastro.pessoa,
       pmieducar.curso,
       pmieducar.matricula,
       pmieducar.matricula_turma,
       pmieducar.turma,
       pmieducar.serie,
       pmieducar.escola,
       pmieducar.escola_ano_letivo,
       pmieducar.escola_curso,
       pmieducar.escola_serie

where  escola_ano_letivo.ano = $ano AND
       escola_ano_letivo.andamento = 1 AND
       instituicao.cod_instituicao = $instituicao AND
       escola.cod_escola = $escola AND
       curso.cod_curso = $curso AND
       serie.cod_serie = $serie AND
       escola_serie.ref_cod_serie = serie.cod_serie AND
       escola_curso.ref_cod_curso = curso.cod_curso AND
       escola_curso.ref_cod_escola = escola.cod_escola AND
       escola_ano_letivo.ref_cod_escola = escola.cod_escola AND
       escola_serie.ref_cod_escola = escola.cod_escola AND
       escola.ref_cod_instituicao = instituicao.cod_instituicao AND
       pessoa.idpes = fisica.idpes AND
       aluno.ref_idpes = fisica.idpes AND
       aluno.cod_aluno = matricula.ref_cod_aluno AND
       matricula.cod_matricula = matricula_turma.ref_cod_matricula AND
       turma.cod_turma = matricula_turma.ref_cod_turma AND
       turma.ref_ref_cod_serie = escola_serie.ref_cod_serie AND
       turma.ref_ref_cod_escola = escola_serie.ref_cod_escola AND
       turma.ref_cod_curso = curso.cod_curso AND
       matricula_turma.ativo = 1 AND
       matricula.ultima_matricula > 0 AND
       matricula.ano = $ano AND
       exists(SELECT 1 FROM modules.transporte_aluno WHERE transporte_aluno.aluno_id = aluno.cod_aluno) AND
             matricula_turma.ref_cod_turma = $turma AND
             (SELECT CASE WHEN $matricula = 0 THEN
                   matricula_turma.ref_cod_matricula = matricula.cod_matricula AND
 		           matricula_turma.ref_cod_turma = $turma
               ELSE
                   matricula.cod_matricula = $matricula
               END)

ORDER BY nome_turma, nome_aluno";

    }
}
