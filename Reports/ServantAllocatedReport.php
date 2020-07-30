<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ServantAllocatedReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'servidores-horas-alocadas';
//        return 'students-not-enrolled';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
//        $this->addRequiredArg('escola');
//        $this->addRequiredArg('modelo');
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
//        $curso = $this->args['curso'] ?: 0;
//        $modelo = $this->args['modelo'] == 1 ? '' : 'not ';
//        $serie = $this->args['serie'] ?: 0;
//        $turma = $this->args['turma'] ?: 0;

        return "select 
a.cod_servidor as codserv,
g.nome, 
nm_funcao, 
a.carga_horaria as cargahorserv,  
(CASE WHEN b.periodo = 3 THEN 'Noturno'
		     WHEN b.periodo = 2 THEN 'Vespertino'
		     WHEN b.periodo = 1 THEN 'Matutino'
		END) AS Periodo,
b.carga_horaria as cargahoraloc,
h.fantasia as nm_escola_servidor,
 public.fcn_upper(nm_instituicao) AS nome_instituicao,

       e.cidade AS cidade_instituicao,

       public.fcn_upper(ref_sigla_uf) AS uf_instituicao,

      (SELECT fcn_upper(substring(logradouro.idtlog::text from 1 for 1)) ||
              lower(substring(logradouro.idtlog::text, 2))
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = f.ref_idpes) AS tipo_logradouro,

      (SELECT CASE WHEN 0 = 0 THEN
              e.logradouro
			  ELSE
	             (SELECT logradouro.nome
                    FROM public.logradouro,
                         cadastro.juridica,
                         cadastro.pessoa ps,
                         cadastro.endereco_pessoa
                   WHERE juridica.idpes = ps.idpes AND
                         ps.idpes = endereco_pessoa.idpes AND
                         endereco_pessoa.idlog = logradouro.idlog AND
                         juridica.idpes = f.ref_idpes)
			  END) AS logradouro,

      (SELECT CASE WHEN 0 = 0 THEN
              e.bairro
			  ELSE
		         (SELECT bairro.nome
                    FROM public.municipio,
                         cadastro.endereco_pessoa,
                         cadastro.juridica,
                         public.bairro
                   WHERE endereco_pessoa.idbai = bairro.idbai AND
						 bairro.idmun = municipio.idmun AND
						 juridica.idpes = endereco_pessoa.idpes AND
						 juridica.idpes = f.ref_idpes)
			  END) AS bairro,

			(SELECT CASE WHEN 0 = 0 THEN
                 e.cidade
			 ELSE
                   (SELECT municipio.nome
				     FROM public.municipio,
                          cadastro.endereco_pessoa,
                          cadastro.juridica,
                          public.bairro
					WHERE endereco_pessoa.idbai = bairro.idbai AND
						  bairro.idmun = municipio.idmun AND
						  juridica.idpes = endereco_pessoa.idpes AND
                          juridica.idpes = f.ref_idpes)
		     END)  AS municipio,

      (SELECT CASE WHEN 0 = 0 THEN
                 e.numero
			  ELSE
		         (SELECT COALESCE(endereco_pessoa.numero::integer,0)
                   FROM cadastro.endereco_pessoa,
                        cadastro.juridica
                  WHERE juridica.idpes = endereco_pessoa.idpes AND
                        juridica.idpes = f.ref_idpes)
		      END) AS numero,


	 (SELECT CASE WHEN 0 = 0 THEN
                 e.ref_sigla_uf
			 ELSE
                (SELECT municipio.sigla_uf
                   FROM public.municipio,
                        cadastro.endereco_pessoa,
                        cadastro.juridica,
                        public.bairro
                  WHERE endereco_pessoa.idbai = bairro.idbai AND
                        bairro.idmun = municipio.idmun AND
                        juridica.idpes = endereco_pessoa.idpes AND
                        juridica.idpes = f.ref_idpes)
		     END)  AS uf_municipio,


	 (SELECT CASE WHEN 0 = 0 THEN
                  e.ddd_telefone
			 ELSE
		         (SELECT min(fone_pessoa.ddd)
                    FROM cadastro.fone_pessoa,
                         cadastro.juridica
                   WHERE juridica.idpes = fone_pessoa.idpes AND
                         juridica.idpes = f.ref_idpes)
		     END) AS fone_ddd,


	 (SELECT CASE WHEN 0 = 0 THEN
                  to_char(e.telefone, '9999-9999')
			 ELSE
                 (SELECT min(to_char(fone_pessoa.fone, '9999-9999'))
                    FROM cadastro.fone_pessoa,
                         cadastro.juridica
                   WHERE juridica.idpes = fone_pessoa.idpes AND
                         juridica.idpes = f.ref_idpes)
		     END) AS fone,

	 (SELECT CASE WHEN 0 = 0 THEN
                  to_char(e.cep, '99999-999')
			 ELSE
                 (SELECT to_char(endereco_pessoa.cep::integer, '99999-999')
                    FROM cadastro.endereco_pessoa,
                         cadastro.juridica
                   WHERE juridica.idpes = endereco_pessoa.idpes AND
                         juridica.idpes = f.ref_idpes)
		     END) AS cep,

     	 (SELECT CASE WHEN 0 = 0 THEN
                 ''
			     ELSE
                 	(SELECT ps.email
                       FROM cadastro.pessoa ps,
                            cadastro.juridica
                      WHERE juridica.idpes = ps.idpes AND
                            juridica.idpes = f.ref_idpes)
		         END) AS email,

       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual
from pmieducar.servidor a
left outer join pmieducar.servidor_alocacao b on a.cod_servidor = b.ref_cod_servidor 
left outer join pmieducar.servidor_funcao c on c.ref_cod_servidor = a.cod_servidor and b.ref_cod_servidor_funcao = c.cod_servidor_funcao
left outer join pmieducar.funcao d on c.ref_cod_funcao = d.cod_funcao
left outer join pmieducar.instituicao e on a.ref_cod_instituicao = e.cod_instituicao
left outer join pmieducar.escola f on b.ref_cod_escola = f.cod_escola
left outer join cadastro.pessoa g on a.cod_servidor = g.idpes
left outer join cadastro.juridica h on f.ref_idpes = h.idpes
where b.ano = $ano AND
       e.cod_instituicao = $instituicao AND
       (SELECT CASE WHEN $escola = 0 THEN
                 b.ref_cod_escola = f.cod_escola
               ELSE
                 b.ref_cod_escola = f.cod_escola AND
                 f.cod_escola = $escola
               END) order by a.cod_servidor";

    }
}
