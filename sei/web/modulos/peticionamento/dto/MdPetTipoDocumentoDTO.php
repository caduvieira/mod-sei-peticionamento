<?

/**
 * ANATEL
*
* 15/04/2016 - criado por jaqueline.mendes - CAST
* 26/08/2024 - Atualização por gabrielg.colab - SPASSU
*
*/

require_once dirname(__FILE__).'/../../../SEI.php';

class MdPetTipoDocumentoDTO extends InfraDTO {

	public function getStrNomeTabela() {
		return null;
	}

	public function montar() {
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'TipoDoc');
		$this->adicionarAtributo(InfraDTO::$PREFIXO_STR,'Descricao');
	}
}

?>