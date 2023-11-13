<?php

require_once DIR_SEI_WEB . '/SEI.php';

class MdWsSeiEditorRN extends InfraRN
{

    const VERSAO_CARIMBO_PUBLICACAO_OBRIGATORIO = "3.0.7";

  protected function inicializarObjInfraIBanco(){
      return BancoSEI::getInstance();
  }

    /**
     * M�todo que verifica o o atributo carimbo publica��o � obrigat�rio na vers�o atual do SEI
     * @return bool
     */
  public static function versaoCarimboPublicacaoObrigatorio(){
      $numVersaoAtual = intval(str_replace('.', '', SEI_VERSAO));
      $numVersaoCarimboObrigatorio = intval(str_replace('.', '', self::VERSAO_CARIMBO_PUBLICACAO_OBRIGATORIO));
    if($numVersaoAtual >= $numVersaoCarimboObrigatorio){
        return true;
    }

      return false;
  }

}
