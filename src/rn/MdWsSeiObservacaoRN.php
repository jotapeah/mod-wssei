<?
require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiObservacaoRN extends InfraRN {

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

  public function encapsulaObservacao(array $post){
      $observacaoDTO = new ObservacaoDTO();
      $observacaoDTO->setStrIdxObservacao(null);
    if (isset($post['unidade'])) {
        $observacaoDTO->setNumIdUnidade($post['unidade']);
    }

    if (isset($post['descricao'])) {
        $observacaoDTO->setStrDescricao($post['descricao']);
    }

    if (isset($post['protocolo'])) {
        $observacaoDTO->setDblIdProtocolo($post['protocolo']);
    }

      return $observacaoDTO;
  }

    /**
     * Metodo que cria uma observacao
     * @param ObservacaoDTO $observacaoDTO
     * @info metodo auxiliar encapsulaObservacao para facilitar encapsulamento
     * @return array
     */
  protected function criarObservacaoControlado(ObservacaoDTO $observacaoDTO){
    try{
        $observacaoRN = new ObservacaoRN();
        $observacaoRN->cadastrarRN0222($observacaoDTO);

        return MdWsSeiRest::formataRetornoSucessoREST('Observa��o cadastrada com sucesso!');
    }catch (Exception $e){
        LogSEI::getInstance()->gravar(InfraException::inspecionar($e));
        return MdWsSeiRest::formataRetornoErroREST($e);
    }
  }
}
