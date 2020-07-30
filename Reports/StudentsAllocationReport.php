<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsAllocationReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-allocation';
//        return 'students-not-enrolled';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('modelo');
//        $this->addRequiredArg('curso');
//        $this->addRequiredArg('serie');
//        $this->addRequiredArg('turma');
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
        $modelo = $this->args['modelo'] == 1 ? '' : 'not ';
//        $serie = $this->args['serie'] ?: 0;
//        $turma = $this->args['turma'] ?: 0;

        return "SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(nm_responsavel) AS nome_responsavel,
       instituicao.cidade AS cidade_instituicao,
       public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
       aluno.cod_aluno,
       matricula.cod_matricula,
      (SELECT nm_serie
         FROM pmieducar.serie
        WHERE serie.cod_serie = matricula.ref_ref_cod_serie) AS serie,

      (SELECT nm_curso
         FROM pmieducar.curso
        WHERE curso.cod_curso = matricula.ref_cod_curso) AS curso,

	         (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
			   ps.idpes = escola.ref_idpes),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS nm_escola,

      (SELECT fcn_upper(substring(logradouro.idtlog::text from 1 for 1)) ||
              lower(substring(logradouro.idtlog::text, 2))
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

      (SELECT COALESCE((SELECT COALESCE((SELECT endereco_pessoa.numero::text
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.numero::text FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),
              (SELECT numero::text FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS numero,


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

     (SELECT COALESCE((SELECT COALESCE((SELECT to_char(endereco_pessoa.cep::integer, '99999-999')
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT to_char(endereco_externo.cep::integer,'99999-999') FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.cod_escola))),
              (SELECT to_char(escola_complemento.cep::integer,'99999-999') FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS cep,


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

       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
       matricula.cod_matricula,

       (SELECT translate(pessoa.nome,'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN')
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as nome_aluno,

       (SELECT CASE WHEN fisica.sexo = 'M' THEN
                    'Masculino'
                ELSE
                    'Feminino'
                END
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as sexo,
       (SELECT to_char(fisica.data_nasc,'dd/mm/yyyy')
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as data_nasc,
       (SELECT educacenso_cod_aluno.cod_aluno_inep
          FROM modules.educacenso_cod_aluno
         WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as cod_aluno_inep

  FROM pmieducar.instituicao,
       pmieducar.matricula,
       pmieducar.aluno,
       pmieducar.escola
 WHERE instituicao.cod_instituicao = $instituicao AND
       instituicao.cod_instituicao = escola.ref_cod_instituicao AND
       (SELECT CASE WHEN $escola = 0 THEN
                    matricula.ref_ref_cod_escola = escola.cod_escola
               ELSE
                    matricula.ref_ref_cod_escola = escola.cod_escola AND
                    escola.cod_escola = $escola
               END) AND
               (SELECT CASE WHEN $curso = 0 THEN
                    matricula.ref_cod_curso is not null
               ELSE
                    matricula.ref_cod_curso = $curso
               END) AND
       matricula.ref_cod_aluno = aluno.cod_aluno AND
       matricula.aprovado = 3 AND
	   matricula.ativo = 1 AND
       matricula.ano = $ano AND
       matricula.ultima_matricula = 1 AND
	   $modelo exists(select 1 from pmieducar.matricula_turma where matricula.cod_matricula = matricula_turma.ref_cod_matricula AND data_exclusao is null)

ORDER BY nm_escola, nome_aluno";

    }
}
