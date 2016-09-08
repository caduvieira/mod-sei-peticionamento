<?
/**
* ANATEL
*
* 15/02/2016 - criado por jaqueline.mendes@cast.com.br - CAST
*
*/

try {
  require_once dirname(__FILE__).'/../../SEI.php';

  session_start();

  SessaoSEI::getInstance()->validarLink();

  PaginaSEI::getInstance()->verificarSelecao('gerir_tamanho_arquivo_peticionamento_cadastrar');

  //SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

  $objTamanhoArquivoDTO = new TamanhoArquivoPermitidoPeticionamentoDTO();
  $strDesabilitar = '';

  $arrComandos = array();

  switch($_GET['acao']){
    case 'gerir_tamanho_arquivo_peticionamento_cadastrar':
    	$strTitulo = 'Peticionamento - Tamanho M�ximo de Arquivos';
    	$arrComandos[] = '<button type="submit" accesskey="S" name="sbmCadastrarTamanhoArquivo" id="sbmCadastrarTamanhoArquivo" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">S</span>alvar</button>';
    	
    	$objTamanhoArquivoRN = new TamanhoArquivoPermitidoPeticionamentoRN();
    	$objTamanhoArquivoDTO->setNumIdTamanhoArquivo(TamanhoArquivoPermitidoPeticionamentoRN::$ID_FIXO_TAMANHO_ARQUIVO);
    	$objTamanhoArquivoDTO->retTodos();
    	$objTamanhoArquivoDTO = $objTamanhoArquivoRN->consultar($objTamanhoArquivoDTO);
    	if (isset($_POST['sbmCadastrarTamanhoArquivo'])) {
    		try{
    			
    	$cadastrar = is_null($objTamanhoArquivoDTO) ? true : false;
    	
    	$objTamanhoArquivoDTO = new TamanhoArquivoPermitidoPeticionamentoDTO();
    	$objTamanhoArquivoDTO->retTodos();
    	
    	$objTamanhoArquivoDTO->setNumValorDocPrincipal($_POST['txtValorDocPrincipal']);
    	$objTamanhoArquivoDTO->setNumValorDocComplementar($_POST['txtValorDocComplementar']);
    	$objTamanhoArquivoDTO->setNumIdTamanhoArquivo(TamanhoArquivoPermitidoPeticionamentoRN::$ID_FIXO_TAMANHO_ARQUIVO);
    	$objTamanhoArquivoDTO->setStrSinAtivo('S');
    	
    	if($cadastrar)
    	{
    		$objTamanhoArquivoDTO = $objTamanhoArquivoRN->cadastrar($objTamanhoArquivoDTO);
    	}else
    	{
    		$objTamanhoArquivoDTO = $objTamanhoArquivoRN->alterar($objTamanhoArquivoDTO);
    	}
    			PaginaSEI::getInstance()->adicionarMensagem('Os dados cadastrados foram salvos com sucesso.');
    			header('Location: '.SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora(TamanhoArquivoPermitidoPeticionamentoRN::$ID_FIXO_TAMANHO_ARQUIVO)));
    			die;
    		}catch(Exception $e){
    			PaginaSEI::getInstance()->processarExcecao($e);
    		}
    	}

	break;
    			   
    default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }


}catch(Exception $e){
  PaginaSEI::getInstance()->processarExcecao($e);
}

$arrComandos[] = '<button type="button" accesskey="C" name="btnFechar" id="btnFechar" value="Fechar" onclick="location.href=\''.PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.PaginaSEI::getInstance()->getAcaoRetorno().'&acao_origem='.$_GET['acao'].PaginaSEI::getInstance()->montarAncora('1'))).'\';" class="infraButton"><span class="infraTeclaAtalho">F</span>echar</button>';

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(':: '.PaginaSEI::getInstance()->getStrNomeSistema().' - '.$strTitulo.' ::');
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();
?>
#lblValorDocPrincipal {position:absolute;left:0%;top:7%;width:215px;margin-left:15px;}
#txtValorDocPrincipal {position:absolute;left:0%;top:13%;width:215px;margin-left:15px}
#lblValorDocComplementar {position:absolute;left:0%;top:21%;width:340px;margin-left:15px}
#txtValorDocComplementar {position:absolute;left:0%;top:27%;width:215px;margin-left:15px}
#fieldsetTamanhoArquivo {width: 96%; height: 110px; margin-left: 0px;}
<?
PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();
PaginaSEI::getInstance()->abrirJavaScript();
PaginaSEI::getInstance()->fecharJavaScript();
PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo,'onload="inicializar();"');
?>
<form id="frmCadastroTamanhoArquivo" method="post" onsubmit="return OnSubmitForm();" 
action="<?=PaginaSEI::getInstance()->formatarXHTML(SessaoSEI::getInstance()->assinarLink('controlador.php?acao='.$_GET['acao']))?>">
<?
PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos);
PaginaSEI::getInstance()->abrirAreaDados('30em');
?>

<fieldset id="fieldsetTamanhoArquivo" class="infraFieldset">

 <legend class="infraLegend">&nbsp;Limite em Mb para carregamento de Arquivos&nbsp;</legend>
	<label id="lblValorDocPrincipal" for="txtValorDocPrincipal" class="infraLabelObrigatorio">
	Valor para Documento Principal:
	</label>
  <input type="text" id="txtValorDocPrincipal" name="txtValorDocPrincipal" class="infraText" value="<?php echo isset($objTamanhoArquivoDTO) ? $objTamanhoArquivoDTO->getNumValorDocPrincipal() : '' ?>" 
  onkeypress="return validarCampo(this, event, 11)" maxlength="11" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
  
	<label id="lblValorDocComplementar" for="txtValorDocComplementar" class="infraLabelObrigatorio">
	Valor para Documentos Essenciais e Complementares:
	</label>
	<input type="text" id="txtValorDocComplementar" name="txtValorDocComplementar" class="infraText" value="<?php echo isset($objTamanhoArquivoDTO) ? $objTamanhoArquivoDTO->getNumValorDocComplementar() : '' ?>" 
  onkeypress="return validarCampo(this, event, 11);"  onkeydown="somenteNumeros(event)" maxlength="11" tabindex="<?=PaginaSEI::getInstance()->getProxTabDados()?>" />
 </fieldset> 
  
  <input type="hidden" id="hdnIdTamanhoArquivoPeticionamento" name="hdnIdTamanhoArquivoPeticionamento" value="<?=$_GET['id_tamanho_arquivo_peticionamento'];?>" />
  <?
  PaginaSEI::getInstance()->fecharAreaDados();
  ?>
</form>
<?
PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>

<script type="text/javascript">

function validarCampo(obj, event, tamanho){
	 if(!somenteNumeros(event)){
		 return somenteNumeros(event)
	 }else{
		 return infraMascaraTexto(obj, event, tamanho);
	 }
	
}

function inicializar(){
  if ('<?=$_GET['acao']?>'=='gerir_tamanho_arquivo_peticionamento_cadastrar'){
    document.getElementById('txtValorDocPrincipal').focus();
  }else{
    document.getElementById('btnFechar').focus();
  }
  infraEfeitoTabelas();
}

function validarCadastro() {
	
  if (infraTrim(document.getElementById('txtValorDocPrincipal').value)=='') {
    alert('Informe o Valor para Documento Principal.');
    document.getElementById('txtValorDocPrincipal').focus();
    return false;
  }

  if (infraTrim(document.getElementById('txtValorDocComplementar').value)=='') {
	  alert('Informe o Valor para Documento Complementar.');
	    document.getElementById('txtValorDocComplementar').focus();
	    return false;
	  }
  
  return true;
}

function OnSubmitForm() {
  return validarCadastro();
}

function somenteNumeros(e){
    var tecla=(window.event)?event.keyCode:e.which;   
    if((tecla>47 && tecla<58))
         return true;
    else{
    	if (tecla==8 || tecla==0)
        	 return true;
	else  return false;
    }
}

</script>