<?
/**
* ANATEL
*
* 28/06/2016 - criado por marcelo.bezerra - CAST
*
*/

require_once dirname(__FILE__).'/../../../SEI.php';

class EmailNotificacaoPeticionamentoRN extends InfraRN { 

	public static $EMAIL_PETICIONAMENTO_USUARIO_PETICIONANTE = 3001;
	public static $EMAIL_PETICIONAMENTO_UNIDADE_ABERTURA = 3002;
	
	public function __construct() {
		
		session_start();
		
		//////////////////////////////////////////////////////////////////////////////
		InfraDebug::getInstance()->setBolLigado(true);
		InfraDebug::getInstance()->setBolDebugInfra(true);
		InfraDebug::getInstance()->limpar();
		//////////////////////////////////////////////////////////////////////////////
		
		parent::__construct ();
	}
	
	protected function inicializarObjInfraIBanco() {
		return BancoSEI::getInstance ();
	}
	
	protected function notificaoPeticionamentoExternoConectado($arrParams ){
		
		$objInfraParametro = new InfraParametro( $this->getObjInfraIBanco() );
		
		$arrParametros = $arrParams[0]; //parametros adicionais fornecidos no formulario de peticionamento
		$objUnidadeDTO = $arrParams[1]; //UnidadeDTO da unidade geradora do processo
		$objProcedimentoDTO = $arrParams[2]; //ProcedimentoDTO para vincular o recibo ao processo correto
		$arrParticipantesParametro = $arrParams[3]; //array de ParticipanteDTO
		
		//consultar email da unidade (orgao)
		$orgaoRN = new OrgaoRN();
		$objOrgaoDTO = new OrgaoDTO();
		$objOrgaoDTO->retTodos();
		$objOrgaoDTO->setNumIdOrgao( $objUnidadeDTO->getNumIdOrgao() );
		$objOrgaoDTO->setStrSinAtivo('S');
		$objOrgaoDTO = $orgaoRN->consultarRN1352( $objOrgaoDTO );

		//obtendo o tipo de procedimento
		$idTipoProc = $arrParametros['id_tipo_procedimento'];
		$objTipoProcDTO = new TipoProcessoPeticionamentoDTO();
		$objTipoProcDTO->retTodos(true);
		$objTipoProcDTO->retStrNomeSerie();
		$objTipoProcDTO->setNumIdTipoProcessoPeticionamento( $idTipoProc );
		$objTipoProcRN = new TipoProcessoPeticionamentoRN();
		$objTipoProcDTO = $objTipoProcRN->consultar( $objTipoProcDTO );
		
		//variaveis basicas em uso no email
		$linkLoginUsuarioExterno = "http://linkLoginUsuarioExterno.com";
		$strNomeTipoProcedimento = $objProcedimentoDTO->getStrNomeTipoProcedimento();
		$strProtocoloFormatado = $objProcedimentoDTO->getStrProtocoloProcedimentoFormatado();
		$strSiglaUnidade = $objUnidadeDTO->getStrSigla();
		
		$strSiglaSistema = SessaoSEIExterna::getInstance()->getStrSiglaSistema();
		$strEmailSistema = $objInfraParametro->getValor('SEI_EMAIL_SISTEMA');
		
		$strSiglaOrgao = $objOrgaoDTO->getStrSigla();
		$strSiglaOrgaoMinusculas = InfraString::transformarCaixaBaixa($objOrgaoDTO->getStrSigla());
		$strSufixoEmail = $objInfraParametro->getValor('SEI_SUFIXO_EMAIL');
		
		//$strNomeContato = $objProcedimentoDTO->getStrNome();
		//$strEmailContato = $objProcedimentoDTO->getStrEmail();
		
		//tentando simular sessao de usuario interno do SEI
		SessaoSEI::getInstance()->setNumIdUnidadeAtual( $objUnidadeDTO->getNumIdUnidade() );
		SessaoSEI::getInstance()->setNumIdUsuario( SessaoSEIExterna::getInstance()->getNumIdUsuarioExterno() );
		
		$objUsuarioDTO = new UsuarioDTO();
		$objUsuarioDTO->retTodos();
		$objUsuarioDTO->setNumIdUsuario( SessaoSEIExterna::getInstance()->getNumIdUsuarioExterno() );
		
		$objUsuarioRN = new UsuarioRN();
		$objUsuarioDTO = $objUsuarioRN->consultarRN0489( $objUsuarioDTO );
		
		//print_r( $objUsuarioDTO ); die();
		
		$strNomeContato = $objUsuarioDTO->getStrNome();
		$strEmailContato = $objUsuarioDTO->getStrSigla();
		
		//enviando email de sistema ap�s cadastramento do processo de peticionamento pelo usu�rio externo
		//================================================================================================
		//EMAIL PARA O USUARIO PETICIONANTE
		//================================================================================================
		
		$objEmailSistemaDTO = new EmailSistemaDTO();
		$objEmailSistemaDTO->retNumIdEmailSistema();
		$objEmailSistemaDTO->retStrDe();
		$objEmailSistemaDTO->retStrPara();
		$objEmailSistemaDTO->retStrAssunto();
		$objEmailSistemaDTO->retStrConteudo();
		$objEmailSistemaDTO->setNumIdEmailSistema( EmailNotificacaoPeticionamentoRN::$EMAIL_PETICIONAMENTO_USUARIO_PETICIONANTE );
			
		$objEmailSistemaRN = new EmailSistemaRN();
		$objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);

		//print_r( $objEmailSistemaDTO ); die();
		
		if ($objEmailSistemaDTO!=null){
		
			$strDe = $objEmailSistemaDTO->getStrDe();
			$strDe = str_replace('@sigla_sistema@',SessaoSEIExterna::getInstance()->getStrSiglaSistema() ,$strDe);
			$strDe = str_replace('@email_sistema@',$objInfraParametro->getValor('SEI_EMAIL_SISTEMA'),$strDe);
			$strDe = str_replace('@processo@',$strProtocoloFormatado ,$strDe);
			$strDe = str_replace('@sigla_orgao@',$objOrgaoDTO->getStrSigla(),$strDe);
			$strDe = str_replace('@sigla_orgao_minusculas@',InfraString::transformarCaixaBaixa($objOrgaoDTO->getStrSigla()),$strDe);
			$strDe = str_replace('@sufixo_email@',$objInfraParametro->getValor('SEI_SUFIXO_EMAIL'),$strDe);
			
			$strConteudo = str_replace('@nome_usuario_externo@', $strNomeContato ,$strConteudo);
			$strConteudo = str_replace('@email_usuario_externo@', $strEmailContato ,$strConteudo);
			$strConteudo = str_replace('@link_login_usuario_externo@',$objOrgaoDTO->getStrSigla(),$strConteudo);
		
			$strPara = $objEmailSistemaDTO->getStrPara();
			$strPara = str_replace('@nome_contato@', $strNomeContato ,$strPara);
			$strPara = str_replace('@email_contato@', $strEmailContato ,$strPara);
			$strPara = str_replace('@email_usuario_externo@', $strEmailContato ,$strPara);
			
			$strAssunto = $objEmailSistemaDTO->getStrAssunto();
			$strAssunto = str_replace('@sigla_orgao@',$objOrgaoDTO->getStrSigla(),$strAssunto);
			$strAssunto = str_replace('@processo@',$strProtocoloFormatado ,$strAssunto);
			
			$strConteudo = $objEmailSistemaDTO->getStrConteudo();
			$strConteudo = str_replace('@processo@', $strProtocoloFormatado , $strConteudo);
			$strConteudo = str_replace('@nome_usuario_externo@', $objUsuarioDTO->getStrNome() , $strConteudo);
			$strConteudo = str_replace('@link_login_usuario_externo@', $linkLoginUsuarioExterno , $strConteudo);
			$strConteudo = str_replace('@tipo_processo@', $objTipoProcDTO->getStrNomeSerie() , $strConteudo);
			$strConteudo = str_replace('@nome_contato@', $strNomeContato ,$strConteudo);
			$strConteudo = str_replace('@email_contato@', $strEmailContato ,$strConteudo);
			$strConteudo = str_replace('@sigla_orgao@',$objOrgaoDTO->getStrSigla(),$strConteudo);
			$strConteudo = str_replace('@descricao_orgao@',$objOrgaoDTO->getStrDescricao(),$strConteudo);
			$strConteudo = str_replace('@sitio_internet_orgao@',$objOrgaoDTO->getStrSitioInternet(),$strConteudo);
		
			//print_r ( $strConteudo ); die();
			InfraMail::enviarConfigurado(ConfiguracaoSEI::getInstance(), $strDe, $strPara, null, null, $strAssunto, $strConteudo);
		
	     }
	     
	     //================================================================================================
	     //EMAIL PARA A UNIDADE DE ABERTURA DO PETICIONAMENTO
	     //================================================================================================
	     
	     $objEmailSistemaDTO = new EmailSistemaDTO();
	     $objEmailSistemaDTO->retStrDe();
	     $objEmailSistemaDTO->retStrPara();
	     $objEmailSistemaDTO->retStrAssunto();
	     $objEmailSistemaDTO->retStrConteudo();
	     $objEmailSistemaDTO->setNumIdEmailSistema( EmailNotificacaoPeticionamentoRN::$EMAIL_PETICIONAMENTO_UNIDADE_ABERTURA );
	     	
	     $objEmailSistemaRN = new EmailSistemaRN();
	     $objEmailSistemaDTO = $objEmailSistemaRN->consultar($objEmailSistemaDTO);
	     	
	     if ($objEmailSistemaDTO!=null){
	     
	     	$strDe = $objEmailSistemaDTO->getStrDe();
	     	$strDe = str_replace('@sigla_sistema@',SessaoSEIExterna::getInstance()->getStrSiglaSistema() ,$strDe);
	     	$strDe = str_replace('@processo@',$strProtocoloFormatado ,$strDe);
	     	$strDe = str_replace('@email_sistema@',$objInfraParametro->getValor('SEI_EMAIL_SISTEMA'),$strDe);
	     	$strDe = str_replace('@sigla_orgao@',$objOrgaoDTO->getStrSigla(),$strDe);
	     	$strDe = str_replace('@sigla_orgao_minusculas@',InfraString::transformarCaixaBaixa($objOrgaoDTO->getStrSigla()),$strDe);
	     	$strDe = str_replace('@sufixo_email@',$objInfraParametro->getValor('SEI_SUFIXO_EMAIL'),$strDe);
	     
	     	$strPara = $objEmailSistemaDTO->getStrPara();
	     	
	     	//TODO obter o email da unidade para onde enviar o email
	     	//$strPara = str_replace('@nome_contato@',$objProcedimentoOuvidoriaDTO->getStrNome(),$strPara);
	     	//$strPara = str_replace('@email_contato@',$objProcedimentoOuvidoriaDTO->getStrEmail(),$strPara);

	     	$strPara = str_replace('@processo@', $strProtocoloFormatado , $strPara);
	     	$strPara = str_replace('@nome_contato@', $strNomeContato , $strPara);
	     	$strPara = str_replace('@email_contato@', $strEmailContato , $strPara);
	     	$strPara = str_replace('@email_usuario_externo@', $strEmailContato , $strPara);
	     	
	     	$strAssunto = $objEmailSistemaDTO->getStrAssunto();
	     	$strAssunto = str_replace('@sigla_orgao@',$objOrgaoDTO->getStrSigla(), $strAssunto);
	     	$strAssunto = str_replace('@processo@', $strProtocoloFormatado , $strAssunto);
	     	
	     	/*
	     	   i. processo - n�mero do processo.
			   ii. tipo_processo - tipo do processo.
			   iii. nome_usuario_externo - nome do usu�rio externo.
			   iv. email_usuario_externo - endere�o eletr�nico do usu�rio externo.
			   v. link_login_usuario_externo - endere�o da p�gina de login de usu�rios externos.
			   vi. tipo_peticionamento - informe se foi Peticionamento de Processo Novo ou Peticionamento Intercorrente em processo j� existente.
			   vii. sigla_unidade_abertura_do_processo - sigla da unidade de abertura do processo.
			   viii. descri��o_unidade_abertura_do_processo - descri��o da unidade de abertura do processo.
			   ix. conteudo_recibo_eletronico_de_protocolo - conte�do do recibo eletr�nico de protocolo (deve conter os mesmos dados disponibilizados ao Usu�rio Externo ao final do Peticionamento)
			   x. sigla_orgao - sigla do �rg�o.
			   xi. descricao_orgao - descri��o do �rg�o.
			   xii. sitio_internet_orgao - endere�o do site do �rg�o.
	     	*/
	     	
	     	$strConteudo = $objEmailSistemaDTO->getStrConteudo();
	     	$strConteudo = str_replace('@processo@',$objProcedimentoDTO->getStrProtocoloProcedimentoFormatado(),$strConteudo);
	     	$strConteudo = str_replace('@tipo_processo@', $objTipoProcDTO->getStrNomeSerie() ,$strConteudo);
	     	$strConteudo = str_replace('@nome_usuario_externo@', $strNomeContato ,$strConteudo);
	     	$strConteudo = str_replace('@email_usuario_externo@', $strEmailContato ,$strConteudo);
	     	$strConteudo = str_replace('@link_login_usuario_externo@', $linkLoginUsuarioExterno ,$strConteudo);
	     	$strConteudo = str_replace('@tipo_peticionamento@',$objOrgaoDTO->getStrDescricao(),$strConteudo);
	     	$strConteudo = str_replace('@sigla_unidade_abertura_do_processo@', $strSiglaUnidade ,$strConteudo);
	     	$strConteudo = str_replace('@siglas_unidades_abertura_do_processo@', $strSiglaUnidade ,$strConteudo);
	     	
	     	$strConteudo = str_replace('@descri��o_unidade_abertura_do_processo@',$objUnidadeDTO->getStrDescricao(),$strConteudo);
	     	//$strConteudo = str_replace('@conteudo_recibo_eletronico_de_protocolo@', '::: Conteudo do recibo :::' ,$strConteudo);
	     	$strConteudo = str_replace('@sigla_orgao@',$objOrgaoDTO->getStrSigla(),$strConteudo);
	     	$strConteudo = str_replace('@descricao_orgao@',$objOrgaoDTO->getStrDescricao(),$strConteudo);
	     	$strConteudo = str_replace('@sitio_internet_orgao@',$objOrgaoDTO->getStrSitioInternet(),$strConteudo);
	     	
	     	/*	     
	     	$strConteudoFormulario = '';
	     	$strConteudoFormulario .= 'Formul�rio de Ouvidoria'."\n";
	     	$strConteudoFormulario .= DocumentoINT::formatarExibicaoConteudo(DocumentoINT::$TV_TEXTO, $strXmlFormulario);
	     
	     	$arrConteudoFormulario = explode("\n",$strConteudoFormulario);
	     	$strConteudoFormulario = '';
	     		
	     	foreach($arrConteudoFormulario as $linha){
	     		$strConteudoFormulario .= '>  '.$linha."\n";
	     	} 
	     	*/
	     		
	     	//$strConteudo = str_replace('@conteudo_formulario_ouvidoria@',$strConteudoFormulario,$strConteudo);	     
	     	//echo "segundo email :: Conteudo :: ";
	     	//echo $strConteudo; die();	     	
	     	
	     	InfraMail::enviarConfigurado(ConfiguracaoSEI::getInstance(), $strDe, $strPara, null, null, $strAssunto, $strConteudo);
	     
	     }
	     
	}
			
}
?>