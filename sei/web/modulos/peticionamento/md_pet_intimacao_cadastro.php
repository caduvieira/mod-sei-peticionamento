<?
/**
 * TRIBUNAL REGIONAL FEDERAL DA 4� REGI�O
 *
 * 14/03/2017 - criado por pedro.cast
 *
 * Vers�o do Gerador de C�digo: 1.40.0
 */

try {
    require_once dirname(__FILE__) . '/../../SEI.php';

    session_start();
    SessaoSEI::getInstance()->validarLink();
    SessaoSEI::getInstance()->validarPermissao($_GET['acao']);

    //////////////////////////////////////////////////////////////////////////////
    //InfraDebug::getInstance()->setBolLigado(false);
    //InfraDebug::getInstance()->setBolDebugInfra(true);
    //InfraDebug::getInstance()->limpar();
    //////////////////////////////////////////////////////////////////////////////

    $strParametros = '';
    if (isset($_GET['arvore'])) {
        PaginaSEI::getInstance()->setBolArvore($_GET['arvore']);
        $strParametros .= '&arvore=' . $_GET['arvore'];
    }

    //Inits
    $objMdPetIntimacaoRN = new MdPetIntimacaoRN();
    $arrComandos = array();
    $idDocumento = isset($_GET['id_documento']) ? $_GET['id_documento'] : $_POST['hdnIdDocumento'];
    $strLinkAjaxUsuarios = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_pet_int_usuario_auto_completar&id_documento=' . $idDocumento);
    $strLinkAjaxTransportaUsuarios = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=usuario_dados_tabela');
    $strTipoIntimacao = MdPetIntTipoIntimacaoINT::montarSelectIdMdPetIntTipoIntimacao('0', '', '0');
    $strLinkAjaxBuscaTiposRespostaTipoIntimacao = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=busca_tipo_resposta_intimacao');
    $strLinkAjaxValidacoesSubmit = SessaoSEI::getInstance()->assinarLink('controlador_ajax.php?acao_ajax=md_pet_int_validar_cadastro');
    $isAlterar = false;
    $countInt  = 0;



    switch ($_GET['acao']) {
        case 'md_pet_intimacao_cadastrar':
    
            
            $strEmailAcoes = array('true', 'true');
            $strTitulo = 'Gerar Intima��o Eletr�nica';

            $arrComandos[] = '<button type="button" onclick="onSubmitForm();" accesskey="G" name="sbmCadastrarMdPetIntimacao" id="sbmCadastrarMdPetIntimacao" value="Salvar" class="infraButton"><span class="infraTeclaAtalho">G</span>erar Intima��o</button>';
            
            $arrComandos[] = '<button type="button" accesskey="C" name="btnCancelar" id="btnCancelar" value="Cancelar" onclick="location.href=\'' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . PaginaSEI::getInstance()->getAcaoRetorno() . '&acao_origem=' . $_GET['acao']) . '\';" class="infraButton"><span class="infraTeclaAtalho">C</span>ancelar</button>';

            $objDocumentoDTO = new DocumentoDTO();
            $objDocumentoDTO->retDblIdDocumento();
            $objDocumentoDTO->retDblIdProcedimento();
            $objDocumentoDTO->retNumIdOrgaoUnidadeResponsavel();
            $objDocumentoDTO->retStrProtocoloDocumentoFormatado();
            $objDocumentoDTO->retStrNomeSerie();
            $objDocumentoDTO->retStrNumero();
            $objDocumentoDTO->retNumIdSerie();
            $objDocumentoDTO->setDblIdDocumento($idDocumento);
            $objDocumentoRN = new DocumentoRN();
            $objDocumentoDTO = $objDocumentoRN->consultarRN0005($objDocumentoDTO);

            $strProtocoloDocumentoFormatado = !is_null($objDocumentoDTO) ? $objDocumentoDTO->getStrProtocoloDocumentoFormatado() : '';

//            Buscar Intima��es cadastradas.
            $arrIntimacoes = $objMdPetIntimacaoRN->buscaIntimacoesCadastradas($idDocumento);
            $isAlterar = (!empty($arrIntimacoes)) ? true : false;

            if (count($_POST) > 0) {
                
            	try {
                    $objMdPetIntimacaoDTO = $objMdPetIntimacaoRN->cadastrarIntimacao($_POST);

                    if ($objMdPetIntimacaoDTO) {
                        $idProcedimento = $objDocumentoDTO->getDblIdProcedimento();

                        //necess�rio para atualizara a arvore do processo e mostra caneta preta de imediato
                        header('Location: ' . SessaoSEI::getInstance()->assinarLink('controlador.php?acao=arvore_visualizar&acao_origem=' . $_GET['acao'] . '&id_procedimento=' . $idProcedimento. '&atualizar_arvore=1&id_documento=' . $objDocumentoDTO->getDblIdDocumento() ));
                        die;
                    }

                } catch (Exception $e) {
                    PaginaSEI::getInstance()->processarExcecao($e);
                }
                
            }
            
            $objMdPetIntPrazoTacitaDTO = new MdPetIntPrazoTacitaDTO();
            $objMdPetIntPrazoTacitaDTO->setBolExclusaoLogica(false);
            $objMdPetIntPrazoTacitaDTO->retTodos();
            
            $objMdPetIntPrazoTacitaRN = new MdPetIntPrazoTacitaRN();
            $objMdPetIntPrazoTacitaDTO = $objMdPetIntPrazoTacitaRN->consultar($objMdPetIntPrazoTacitaDTO);
            if (count($objMdPetIntPrazoTacitaDTO)>0) {
            	$numNumPrazo = $objMdPetIntPrazoTacitaDTO->getNumNumPrazo();
            }
            
            break;

        default:
            throw new InfraException("A��o '" . $_GET['acao'] . "' n�o reconhecida.");
    }


} catch (Exception $e) {
    PaginaSEI::getInstance()->processarExcecao($e);
}

PaginaSEI::getInstance()->montarDocType();
PaginaSEI::getInstance()->abrirHtml();
PaginaSEI::getInstance()->abrirHead();
PaginaSEI::getInstance()->montarMeta();
PaginaSEI::getInstance()->montarTitle(PaginaSEI::getInstance()->getStrNomeSistema() . ' - ' . $strTitulo);
PaginaSEI::getInstance()->montarStyle();
PaginaSEI::getInstance()->abrirStyle();

require_once 'md_pet_intimacao_cadastro_css.php';

PaginaSEI::getInstance()->fecharStyle();
PaginaSEI::getInstance()->montarJavaScript();

require_once 'md_pet_intimacao_cadastro_js.php';

PaginaSEI::getInstance()->fecharHead();
PaginaSEI::getInstance()->abrirBody($strTitulo, 'onload="inicializar();"');

?>
<form id="frmMdPetIntimacaoCadastro" 
          method="post" 
          action="<?= SessaoSEI::getInstance()->assinarLink('controlador.php?acao=' . $_GET['acao'] . '&acao_origem=' . $_GET['acao']) ?>">
        <? PaginaSEI::getInstance()->montarBarraComandosSuperior($arrComandos); ?>
        <? PaginaSEI::getInstance()->abrirAreaDados(); ?>

        <fieldset id="fldDestinatarios">
            <legend class="infraLegend" class="infraLabelObrigatorio"> Destinat�rios</legend>

                <!-- Usuario Externo -->
                <div class="grid_12">
                    <div class="grid grid_4-5">
                        <label id="lblUsuario" for="txtUsuario"class="infraLabelObrigatorio">Usu�rio Externo: </label>
                        <img style="margin-bottom:-3px;" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/ajuda.gif" name="ajuda" id="imgAjudaUsuario" <?= PaginaSEI::montarTitleTooltip('A pesquisa � realizada somente sobre Usu�rios Externos liberados. A consulta pode ser efetuada pelo Nome, E-mail ou CPF do Usu�rio Externo.') ?> class="infraImg"/>
                        <input style="margin-top:1px;" type="text" id="txtUsuario" name="txtUsuario" class="infraText campoPadrao" onkeypress="return infraMascaraTexto(this,event);" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>

                    <!-- Email -->
                    <div class="grid grid_4-5">
                        <label id="lblEmail" for="txtEmail"  class="infraLabelObrigatorio">E-mail do Usu�rio Externo:</label>
                        <input type="text" id="txtEmail" name="txtEmail" class="infraText campoPadrao infraAutoCompletar" disabled="disabled" onkeypress="return infraMascaraTexto(this,event,50);" maxlength="50" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                    </div>

                    <div class="height_2"></div>

                    <!-- Botao Adicionar -->
                    <div class="grid grid_2">
                        <!--<input type="button" id="sbmGravarUsuario" accesskey="A" name="sbmGravarUsuario" class="infraButton" onclick="transportarUsuario();" value="Adicionar" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>-->
                        <button type="button" id="sbmGravarUsuario" accesskey="A" name="sbmGravarUsuario" class="infraButton" onclick="transportarUsuario();" value="Adicionar" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"><span class="infraTeclaAtalho">A</span>dicionar</button>
                    </div>

                </div>

                <div class="tabUsuario clear height_2" style="<?php echo $isAlterar ? '' : 'display:none' ?>"></div>
                <!-- Tabela de Destinat�rios -->

                <div id="divTabelaUsuarioExterno" class="tabUsuario infraAreaTabela" style="<?php echo $isAlterar ? '' : 'display:none' ?>">
                    <table id="tblEnderecosEletronicos" width="100%" class="infraTable">
                        <tr>
                            <th style="display:none;">ID</th>
                            <th class="infraTh">Destinat�rio</th>
                            <th class="infraTh">E-mail</th>
                            <th class="infraTh" width="90px">CPF</th>
                            <th class="infraTh" width="66px">Data de Expedi��o</th>
                            <th class="infraTh" width="215px">Situa��o da Intima��o</th>
                            <th class="infraTh" width="40px">A��es</th>
                        </tr>
                        <? if ($isAlterar) { ?>
                            <input type="hidden" id="hdnIdUsuarios" name="hdnIdUsuarios" value="<?= $arrIntimacoes ?>"/>
                            <? foreach ($arrIntimacoes as $key => $intimacao) {
                                $countInt++;
                                ?>
                                <tr class="<?php echo $key % 2 == 0 ? 'infraTrClara' : 'infraTrEscura'; ?>">
                                    <td style="display:none; width: 100px;  "> <?= $intimacao['Id'] ?></td>
                                    <td> <?= $intimacao['Nome'] ?></td>
                                    <td> <?= $intimacao['Email'] ?></td>
                                    <td> <?= InfraUtil::formatarCpf($intimacao['Cpf']) ?></td>
                                    <td align="center"> <?= $intimacao['DataIntimacao'] ?></td>
                                    <td> <?= $intimacao['Situacao'] ?></td>
                                    <td align="center"><a href='#'
                                                          onclick="abrirIntimacaoCadastrada('<?= $intimacao['Url'] ?>')">
                                            <img title='Consultar Destinat�rio' alt='Consultar Destinat�rio'
                                                 src='/infra_css/imagens/consultar.gif' class='infraImg'/></a></td>
                                </tr>
                            <? }
                        } ?>
                    </table>
                    <input type="hidden" id="hdnIdDadosUsuario" name="hdnIdDadosUsuario"
                           value="<?= $_POST['hdnIdDadosUsuario'] ?>"/>
                    <input type="hidden" id="hdnDadosUsuario" name="hdnDadosUsuario"
                           value="<?= $_POST['hdnDadosUsuario'] ?>"/>

                </div>
        </fieldset>

        <div class="clear height_1"></div>

        <div id="conteudoHide" style="display: none;">

        <div class="grid grid_9">
            <!-- Tipo de Intima��o -->
            <div class="grid grid_6" style="margin-left:2px;">
                <label id="lblTipodeIntimacao" for="lblTipodeIntimacao" accesskey="" class="infraLabelObrigatorio">Tipo de Intima��o:</label>
                <select style="width: 50%" id="selTipoIntimacao" name="selTipoIntimacao" onchange="mostraTipoResposta(this)" class="campoPadrao infraSelect" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>">
                    <?= $strTipoIntimacao ?>
                </select>
                <input type=hidden name=hdnTipoIntimacao id=hdnTipoIntimacao>
            </div>

            <div class="clear height_1"></div>

            <!-- Tipo de Resposta -->
            <div class="grid grid_11" id="divTipoResposta" name="divTipoResposta">
                <div class="grid grid_3">
                    <label id="lblTipodeResposta" for="lblTipodeResposta" class="infraLabelObrigatorio">Tipo de Resposta:</label>
                </div>
                <div class="clear"></div>
                <div class="grid grid_6" id="divSelectTipoResposta"></div>
            </div>

            <div style="display: none" id="divEspacoResposta" class="clear height_1"></div>

        </div>

        <div class="clear"></div>

        <fieldset id="fldDocumentosIntimacao">
            <legend class="infraLegend" class="infraLabelObrigatorio"> Documentos da Intima��o <img style="margin-top:1px; margin-bottom: -3px" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/ajuda.gif" name="ajuda" id="imgAjudaAnexos" <?= PaginaSEI::montarTitleTooltip('Considerar-se-� cumprida a Intima��o Eletr�nica com a consulta ao Documento Principal ou, se indicados, a qualquer um dos Protocolos dos Anexos da Intima��o. \n\n Caso a consulta n�o seja efetuada em at� ' . $numNumPrazo . ' dias corridos da data de gera��o da Intima��o Eletr�nica, automaticamente ocorrer� seu Cumprimento por Decurso do Prazo T�cito. \n\n\n\n\n O Documento Principal e poss�veis Anexos ter�o o acesso ao seu teor protegidos at� o cumprimento da Intima��o.') ?> /></legend>

            <!-- Documento Principal-->
            <div class="grid grid_8" style="margin-top:5px">
                <label id="lblDocPrincIntimacao" for="lblDocPrincIntimacao" class="infraLabelOpcional">Documento Principal da Intima��o: <?= DocumentoINT::formatarIdentificacao($objDocumentoDTO) . ' (' . $strProtocoloDocumentoFormatado . ')'; ?></label>
            </div>

            <div class="clear height"></div>

            <div id="divOptAno" class="grid_8 infraDivCheckbox">
                <input type="checkbox" id="optPossuiAnexo" name="rdoPossuiAnexo" value="S"
                       onclick="esconderAnexos(this)" class="infraCheckbox" <?= (false ? 'checked="checked"' : '') ?>
                       tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                <label id="lblPossuiAnexo" for="optPossuiAnexo" accesskey="" class="infraLabelCheckbox">Intima��o possui Anexos </label>
            </div>

            <div class="clear"></div>
            <!-- Anexos -->
            <div class="grid grid_10">
                <label id="lblAnexosIntimacao" for="lblAnexosIntimacao" accesskey="" class="infraLabelObrigatorio">Protocolos dos Anexos da Intima��o:</label>
                <div style="display: -webkit-box;">
                    <select onclick="controlarSelected(this);" id="selAnexosIntimacao" style="width: 90%" name="selAnexosIntimacao" size="7"
                            class="infraSelect" multiple="multiple"></select>

                    <img style="padding-left: 5px;" id="imgLupaAnexos" onclick="objLupaProtocolosIntimacao.selecionar(700,500);"
                         src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/lupa.gif"
                         alt="Selecionar Protocolos" title="Selecionar Protocolos" class="infraImg"/>
                    </br>

                    <img style="padding-left: 4px;" id="imgExcluirAnexos" onclick="objLupaProtocolosIntimacao.remover();"
                         src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/remover.gif"
                         alt="Remover Protocolos Selecionados" title="Remover Protocolos Selecionados"
                         class="infraImgNormal"/>

                </div>
                <input type="hidden" id="hdnAnexosIntimacao" name="hdnAnexosIntimacao"
                       value="<?= $_POST['hdnAnexosIntimacao'] ?>"/>
            </div>

        </fieldset>

        <div class="clear"></div>

            <fieldset id="flTpAcesso" style="margin-top:17px">
                <legend class="infraLegend" class="infraLabelObrigatorio"> Tipo de Acesso Externo </legend>
                <!-- Tipo de Acesso Externo -->
                <div class="clear height"></div>
                <div class="grid grid_8" style="margin-top:3px">
                    <!-- Integral -->
                    <div id="divOptAno" class="infraDivRadio">
                        <input type="radio" id="optIntegral" name="optIntegral" value="I" class="infraRadio" onclick="mostrarProtocoloParcial(this)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        <label id="lblIntegral" for="optIntegral" accesskey="" class="infraLabelRadio">Integral </label> &nbsp;<img src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/ajuda.gif" name="ajuda" id="imgAjudaAnexos" <?= PaginaSEI::montarTitleTooltip('Aten��o! Toda Intima��o Eletr�nica ocorre por meio da funcionalidade de Disponibiliza��o de Acesso Externo do SEI. \n\n Selecionando o Tipo de Acesso Externo Integral, TODOS os Protocolos constantes no processo ser�o disponibilizados ao Destinat�rio, independentemente de seus N�veis de Acesso, incluindo Protocolos futuros que forem adicionados ao processo. \n\n\n\n\n Para que n�o ocorra nulidade da Intima��o, o Acesso Externo Integral somente poder� ser cancelado depois de cumprida a Intima��o e conclu�do o Prazo Externo correspondente (se indicado para poss�vel Resposta). Caso posteriormente o Acesso Externo Integral utilizado pela Intima��o Eletr�nica seja cancelado, ele ser� automaticamente substitu�do por um Acesso Externo Parcial abrangendo o Documento Principal e poss�veis Anexos da Intima��o, al�m de Documentos peticionados pelo pr�prio Usu�rio Externo.') ?> class="infraImg"/>
                    </div>

                    <!-- Parcial -->
                    <div id="divOptAno" class="infraDivRadio" style="margin-left: 16px;">
                        <input type="radio" id="optParcial" name="optParcial" value="P" class="infraRadio" onclick="mostrarProtocoloParcial(this)" tabindex="<?= PaginaSEI::getInstance()->getProxTabDados() ?>"/>
                        <label id="lblParcial" for="optParcial" accesskey="" class="infraLabelRadio">Parcial </label> &nbsp;<img src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/ajuda.gif" name="ajuda" id="imgAjudaAnexos" <?= PaginaSEI::montarTitleTooltip('Aten��o! Toda Intima��o Eletr�nica ocorre por meio da funcionalidade de Disponibiliza��o de Acesso Externo do SEI. \n\n Selecionando o Tipo de Acesso Externo Parcial, SOMENTE ser�o disponibilizados ao Destinat�rio o Documento Principal, os Protocolos dos Anexos da Intima��o (se indicados) e os Protocolos adicionados no Acesso Parcial (se indicados). O Documento Principal e Protocolos dos Anexos ser�o automaticamente inclu�dos no Acesso Parcial. \n\n\n\n\n Para que n�o ocorra nulidade da Intima��o, o Acesso Externo Parcial n�o poder� ser alterado nem cancelado. Todos os Protocolos inclu�dos no Acesso Externo Parcial poder�o ser visualizados pelo Destinat�rio, independentemente de seus N�veis de Acesso, n�o abrangendo Protocolos futuros que forem adicionados ao processo.') ?> class="infraImg"/>
                    </div>
                </div>

                <div class="clear height"></div>

                <!-- Protocolos Dispon�veis -->
                <div class="grid grid_10">
                    <label id="lblProtocolosDisponibilizados" for="lblProtocolosDisponibilizados" accesskey="" class="infraLabelObrigatorio">Protocolos Disponibilizados:</label>
                    <div style="display: -webkit-box;">
                        <select onclick="controlarSelected(this);" style="width: 90%" id="selProtocolosDisponibilizados" multiple="multiple" name="selProtocolosDisponibilizados" size="7" class="infraSelect"></select>

                        <img style="padding-left: 5px;" id="imgLupaProtocolos" onclick="objLupaProtocolosDisponibilizados.selecionar(700,500);" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/lupa.gif" alt="Selecionar Protocolos" title="Selecionar Protocolos" class="infraImg"/>
                        </br>
                        <img style="padding-left: 4px;" id="imgExcluirProtocolos" onclick="objLupaProtocolosDisponibilizados.remover();" src="<?= PaginaSEI::getInstance()->getDiretorioImagensGlobal() ?>/remover.gif" alt="Remover Protocolos Selecionados" title="Remover Protocolos Selecionados" class="infraImgNormal"/>
                    </div>
                </div>
            </fieldset>
            </div>

        <!-- Hiddens -->
        <select style="display: none" multiple="multiple" id="selMainIntimacao" name="selMainIntimacao" size="12"/>
        <input type="hidden" id="hdnIsAlterar" name="hdnIsAlterar" value="<?php echo $isAlterar ? '1' : '0' ?>"/>
        <input type="hidden" id="hdnCountIntimacoes" name="hdnCountIntimacoes" value="<?php echo $countInt ?>"/>
        <input type="hidden" id="hdnProtocolosDisponibilizados" name="hdnProtocolosDisponibilizados"
               value="<?= $_POST['hdnProtocolosDisponibilizados'] ?>"/>
        <input type="hidden" id="hdnIdDocumento" name="hdnIdDocumento" value="<?php echo $idDocumento ?>"/>
        <input type="hidden" id="hndIdDocumento" name="hndIdDocumento" value="<?=$idDocumento?>" />
        <input type="hidden" id="hdnIdProcedimento" name="hdnIdProcedimento" value="<?= array_key_exists('id_procedimento', $_GET) ? $_GET['id_procedimento'] : $_POST['hdnIdProcedimento'] ?>"/>
        <input type="hidden" id="hdnIdsDocAnexo" name="hdnIdsDocAnexo" value=""/>
        <input type="hidden" id="hdnIdsDocDisponivel" name="hdnIdsDocDisponivel" value=""/>

        <!-- Hiddens das constantes do Acesso Parcial / Integral -->
    <input type="hidden" id="hdnStaAcessoParcial" name="hdnStaAcessoParcial" value="<?php echo MdPetIntAcessoExternoDocumentoRN::$ACESSO_PARCIAL ?>">
    <input type="hidden" id="hdnStaAcessoIntegral" name="hdnStaAcessoIntegral" value="<?php echo MdPetIntAcessoExternoDocumentoRN::$ACESSO_INTEGRAL ?>">
    <input type="hidden" id="hdnStaSemAcesso" name="hdnStaSemAcesso" value="<?php echo MdPetIntAcessoExternoDocumentoRN::$NAO_POSSUI_ACESSO ?>">

        <?php PaginaSEI::getInstance()->montarBarraComandosInferior($arrComandos); ?>
        <?php PaginaSEI::getInstance()->fecharAreaDados(); ?>

    </form>
<?php

PaginaSEI::getInstance()->fecharBody();
PaginaSEI::getInstance()->fecharHtml();
?>