<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ClassCouncilMapReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'class-council-map';
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

        return "SELECT DISTINCT
       matricula.cod_matricula,
       matricula.ref_cod_aluno AS cod_aluno,

      (SELECT translate(public.fcn_upper(pessoa.nome),'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN')
	       FROM cadastro.pessoa, cadastro.fisica, pmieducar.aluno
	      WHERE pessoa.idpes = fisica.idpes AND
	            fisica.idpes = aluno.ref_idpes AND
	            aluno.cod_aluno = matricula.ref_cod_aluno LIMIT 1
      ) AS nm_aluno,

      (CASE matricula.aprovado
	          WHEN 1 THEN 'Apr'
	          WHEN 2 THEN 'Rep'
	          WHEN 3 THEN 'And'
	          WHEN 4 THEN 'Trs'
                    WHEN 6 THEN 'Aba'
	          ELSE 'Rec'
      END) AS situacao,

(SELECT COALESCE((SELECT to_char((((select serie.dias_letivos
                       from pmieducar.serie
                                            where serie.cod_serie = matricula.ref_ref_cod_serie)::float -
                   (SELECT sum(falta_geral.quantidade)
                       FROM modules.falta_geral,
                            modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id AND
                            falta_aluno.matricula_id = matricula.cod_matricula AND
                            falta_geral.etapa in('1','2','3','4')))::float * 100::float )::float /
                            (select serie.dias_letivos from pmieducar.serie where serie.cod_serie = matricula.ref_ref_cod_serie) :: float,'999')),


to_char((100 - (((SELECT sum(falta_componente_curricular.quantidade)
           FROM modules.falta_componente_curricular,
                modules.falta_aluno
          WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id AND
                falta_aluno.matricula_id = matricula.cod_matricula AND
                falta_componente_curricular.etapa in ('1','2','3','4') AND
				falta_aluno.tipo_falta = 2) * (select curso.hora_falta * 100 from pmieducar.curso where curso.cod_curso = matricula.ref_cod_curso))/
                                                              (select serie.carga_horaria from pmieducar.serie where  serie.cod_serie = matricula.ref_ref_cod_serie))), '999')))  AS frequencia_geral,


       (SELECT CASE  WHEN nota_componente_curricular.nota_arredondada = '10' THEN
			                   '10,0'
			               WHEN char_length(nota_componente_curricular.nota_arredondada) = '1' THEN
			                   replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                           ELSE
                               replace(nota_componente_curricular.nota_arredondada,'.',',')
                           END
	      FROM modules.nota_componente_curricular,
	           modules.nota_aluno
	     WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id AND
	           nota_componente_curricular.componente_curricular_id = componente_curricular.id AND
	           nota_aluno.matricula_id = matricula.cod_matricula AND
             nota_componente_curricular.etapa = '1'
      ) AS nota1,

       (SELECT CASE  WHEN nota_componente_curricular.nota_arredondada = '10' THEN
			                   '10,0'
			               WHEN char_length(nota_componente_curricular.nota_arredondada) = '1' THEN
			                   replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                           ELSE
                               replace(nota_componente_curricular.nota_arredondada,'.',',')
                           END
	      FROM modules.nota_componente_curricular,
	           modules.nota_aluno
	     WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id AND
	           nota_componente_curricular.componente_curricular_id = componente_curricular.id AND
	           nota_aluno.matricula_id = matricula.cod_matricula AND
             nota_componente_curricular.etapa = '2'
      ) AS nota2,

       (SELECT CASE  WHEN nota_componente_curricular.nota_arredondada = '10' THEN
			                   '10,0'
			               WHEN char_length(nota_componente_curricular.nota_arredondada) = '1' THEN
			                   replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                           ELSE
                               replace(nota_componente_curricular.nota_arredondada,'.',',')
                           END
	      FROM modules.nota_componente_curricular,
	           modules.nota_aluno
	     WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id AND
	           nota_componente_curricular.componente_curricular_id = componente_curricular.id AND
	           nota_aluno.matricula_id = matricula.cod_matricula AND
             nota_componente_curricular.etapa = '3'
      ) AS nota3,

       (SELECT CASE  WHEN nota_componente_curricular.nota_arredondada = '10' THEN
			                   '10,0'
			               WHEN char_length(nota_componente_curricular.nota_arredondada) = '1' THEN
			                   replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                           ELSE
                               replace(nota_componente_curricular.nota_arredondada,'.',',')
                           END
	      FROM modules.nota_componente_curricular,
	           modules.nota_aluno
	     WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id AND
	           nota_componente_curricular.componente_curricular_id = componente_curricular.id AND
	           nota_aluno.matricula_id = matricula.cod_matricula AND
             nota_componente_curricular.etapa = '4'
      ) AS nota4,

      componente_curricular.abreviatura AS nm_componente_curricular,

      (SELECT escola_ano_letivo.ano
          FROM pmieducar.escola_ano_letivo
         WHERE escola_ano_letivo.ref_cod_escola = $escola and
		       ano = $ano) AS ano,

      (SELECT public.fcn_upper(instituicao.nm_responsavel)
         FROM pmieducar.instituicao
        WHERE instituicao.cod_instituicao = $instituicao) AS nome_responsavel,

      (SELECT COALESCE((SELECT public.fcn_upper(ps.nome)
          FROM cadastro.pessoa ps,
               cadastro.juridica,
               pmieducar.escola
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
               escola.cod_escola = $escola),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS nm_escola,


      (SELECT public.fcn_upper(instituicao.nm_instituicao)
         FROM pmieducar.instituicao
        WHERE instituicao.cod_instituicao = $instituicao) AS nome_instituicao,

      (SELECT public.fcn_upper(substring(logradouro.idtlog::text from 1 for 1)) ||
              lower(substring(logradouro.idtlog::text, 2))
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa,
              pmieducar.escola
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola) AS tipo_logradouro,

      (SELECT COALESCE((SELECT COALESCE((SELECT logradouro.nome
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa,
              pmieducar.escola
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT endereco_externo.logradouro FROM cadastro.endereco_externo, pmieducar.escola WHERE endereco_externo.idpes = escola.ref_idpes AND escola.cod_escola = $escola))),(SELECT logradouro FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS logradouro,

      (SELECT COALESCE((SELECT COALESCE((SELECT municipio.nome
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro,
              pmieducar.escola
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT endereco_externo.cidade FROM cadastro.endereco_externo, pmieducar.escola WHERE endereco_externo.idpes = escola.ref_idpes AND escola.cod_escola = $escola))),(SELECT municipio FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS municipio,

      (SELECT COALESCE((SELECT COALESCE((SELECT bairro.nome
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro,
              pmieducar.escola
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT endereco_externo.bairro FROM cadastro.endereco_externo, pmieducar.escola WHERE endereco_externo.idpes = escola.ref_idpes AND escola.cod_escola = $escola))),(SELECT bairro FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS bairro,

      (SELECT COALESCE((SELECT COALESCE((SELECT municipio.sigla_uf
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro,
              pmieducar.escola
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT endereco_externo.sigla_uf FROM cadastro.endereco_externo, pmieducar.escola WHERE endereco_externo.idpes = escola.ref_idpes AND escola.cod_escola = $escola))),(select inst.ref_sigla_uf from pmieducar.instituicao inst where inst.cod_instituicao = $instituicao))) AS uf_municipio,

      (SELECT COALESCE((SELECT COALESCE((SELECT endereco_pessoa.numero
         FROM cadastro.endereco_pessoa,
              cadastro.juridica,
              pmieducar.escola
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT endereco_externo.numero FROM cadastro.endereco_externo, pmieducar.escola WHERE endereco_externo.idpes = escola.ref_idpes AND escola.cod_escola = $escola))),(SELECT numero FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS numero,

     (SELECT COALESCE((SELECT COALESCE((SELECT to_char(endereco_pessoa.cep::integer, '99999-999')
         FROM cadastro.endereco_pessoa,
              cadastro.juridica,
              pmieducar.escola
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT to_char(endereco_externo.cep::integer,'99999-999') FROM cadastro.endereco_externo, pmieducar.escola WHERE endereco_externo.idpes = escola.ref_idpes AND escola.cod_escola = $escola))),(SELECT to_char(escola_complemento.cep::integer,'99999-999') FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = $escola))) AS cep,

     (SELECT COALESCE((SELECT min(fone_pessoa.ddd)
         FROM cadastro.fone_pessoa,
              cadastro.juridica,
              pmieducar.escola
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT min(ddd_telefone) FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS fone_ddd,


     (SELECT COALESCE((SELECT min(to_char(fone_pessoa.fone, '9999-9999'))
         FROM cadastro.fone_pessoa,
              cadastro.juridica,
              pmieducar.escola
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT min(to_char(telefone, '9999-9999')) FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = $escola))) AS fone,

     (SELECT COALESCE((SELECT ps.email
         FROM cadastro.pessoa ps,
              cadastro.juridica,
              pmieducar.escola
        WHERE juridica.idpes = ps.idpes AND
              juridica.idpes = escola.ref_idpes AND
              escola.cod_escola = $escola),(SELECT email FROM pmieducar.escola_complemento where ref_cod_escola = $escola))) AS email,

	   (SELECT curso.nm_curso
        FROM pmieducar.curso
	     WHERE curso.cod_curso = matricula.ref_cod_curso AND
             curso.ativo = 1 AND
             curso.cod_curso = $curso) AS nome_curso,

     (SELECT serie.nm_serie
        FROM pmieducar.serie
	     WHERE serie.cod_serie = matricula.ref_ref_cod_serie AND
             serie.ativo = 1 AND
             serie.cod_serie = $serie) AS nome_serie,

	    (SELECT turma.nm_turma
	       FROM pmieducar.turma,
                    pmieducar.matricula_turma
              WHERE turma.cod_turma = $turma AND
                    matricula_turma.ref_cod_turma = turma.cod_turma AND
                    matricula_turma.ref_cod_matricula = matricula.cod_matricula AND
                    turma.ativo = 1 AND
		matricula_turma.ativo = 1) AS nome_turma,

       (select turma_turno.nome as nm_turno from pmieducar.turma_turno, pmieducar.turma where turma_turno.id = turma.turma_turno_id and turma.cod_turma = matricula_turma.ref_cod_turma and  instituicao_id = $instituicao limit 1) as periodo


 FROM pmieducar.escola_ano_letivo,
	    pmieducar.matricula,
      pmieducar.matricula_turma,
      modules.componente_curricular

WHERE
  escola_ano_letivo.ativo = 1 AND
  escola_ano_letivo.ref_cod_escola = matricula.ref_ref_cod_escola AND
  escola_ano_letivo.ano = $ano AND
  matricula.ref_ref_cod_escola = $escola AND
  matricula.ref_cod_curso = $curso AND
  matricula.ref_ref_cod_serie = $serie AND
  matricula.ativo = 1 AND
  matricula.ano = $ano AND
  matricula_turma.ref_cod_turma = $turma AND
  --matricula_turma.ativo = 1 AND
  matricula_turma.ref_cod_matricula = matricula.cod_matricula AND

 (matricula_turma.ativo = 1 or exists( select 1 from pmieducar.transferencia_solicitacao
                                                     where ref_cod_matricula_saida = matricula.cod_matricula and
                                                           ativo = 1 limit 1)) and

  CASE WHEN (select 1 from modules.componente_curricular_turma
    WHERE componente_curricular_turma.turma_id = matricula_turma.ref_cod_turma AND
    componente_curricular_turma.escola_id = matricula.ref_ref_cod_escola limit 1) = 1 THEN

    componente_curricular.id in (select componente_curricular_id from modules.componente_curricular_turma
    where componente_curricular_turma.escola_id = matricula.ref_ref_cod_escola AND
    componente_curricular_turma.turma_id = matricula_turma.ref_cod_turma)
  ELSE
    componente_curricular.id in (select ref_cod_disciplina from pmieducar.escola_serie_disciplina
    where escola_serie_disciplina.ref_ref_cod_serie = matricula.ref_ref_cod_serie AND
    escola_serie_disciplina.ref_ref_cod_escola = matricula.ref_ref_cod_escola AND
    escola_serie_disciplina.ativo = 1)
  END

ORDER BY nm_aluno, cod_aluno";

    }
}
