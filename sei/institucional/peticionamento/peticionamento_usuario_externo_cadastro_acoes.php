<?
/**
* ANATEL
*
* 01/08/2016 - criado por marcelo.bezerra@cast.com.br - CAST
*
* Controle de a��es principais do cadastro de peticionamento
*
*/
  
  switch($_GET['acao']){

  	case 'peticionamento_usuario_externo_cadastrar':
  		$strTitulo = 'Peticionar Processo Novo';
  		break;
  		
    default:
      throw new InfraException("A��o '".$_GET['acao']."' n�o reconhecida.");
  }
?>