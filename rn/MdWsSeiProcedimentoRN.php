<?
require_once dirname(__FILE__) . '/../../../SEI.php';

class MdWsSeiProcedimentoRN extends InfraRN
{

    protected function inicializarObjInfraIBanco()
    {
        return BancoSEI::getInstance();
    }

    /**
     * Retorna o total de unidades do processo
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    protected function listarUnidadesProcessoConectado(ProtocoloDTO $protocoloDTO)
    {
        try {
            if (!$protocoloDTO->getDblIdProtocolo()) {
                throw new InfraException('Protocolo n�o informado.');
            }
            $result = array();

            $relProtocoloProtocoloDTOConsulta = new RelProtocoloProtocoloDTO();
            $relProtocoloProtocoloDTOConsulta->setDblIdProtocolo1($protocoloDTO->getDblIdProtocolo());
            $relProtocoloProtocoloDTOConsulta->retDblIdProtocolo1();
            $relProtocoloProtocoloDTOConsulta->setNumMaxRegistrosRetorno(1);
            $relProtocoloProtocoloDTOConsulta->setNumPaginaAtual(0);
            $relProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
            $ret = $relProtocoloProtocoloRN->listarRN0187($relProtocoloProtocoloDTOConsulta);
            if ($ret) {
                /** @var RelProtocoloProtocoloDTO $relProtocoloProtocoloDTO */
                $relProtocoloProtocoloDTO = $ret[0];
                $result['processo'] = $relProtocoloProtocoloDTO->getDblIdProtocolo1();
                $result['unidades'] = $relProtocoloProtocoloDTOConsulta->getNumTotalRegistros();
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que lista o sobrestamento de um processo
     * @param AtividadeDTO $atividadeDTOParam
     * @return array
     */
    protected function listarSobrestamentoProcessoConectado(AtividadeDTO $atividadeDTOParam)
    {
        try {
            if (!$atividadeDTOParam->isSetDblIdProtocolo()) {
                throw new InfraException('Protocolo n�o informado.');
            }
            if (!$atividadeDTOParam->isSetNumIdUnidade()) {
                $atividadeDTOParam->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            }

            $result = array();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->retTodos();
            $atividadeDTOConsulta->setDblIdProtocolo($atividadeDTOParam->getDblIdProtocolo());
            $atividadeDTOConsulta->setDthConclusao(null);
            $atividadeDTOConsulta->setNumIdTarefa(TarefaRN::$TI_SOBRESTAMENTO);
            $atividadeDTOConsulta->setNumMaxRegistrosRetorno(1);
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);

            /** @var AtividadeDTO $atividadeDTO */
            foreach ($ret as $atividadeDTO) {
                $result[] = array(
                    'idAtividade' => $atividadeDTO->getNumIdAtividade(),
                    'idProtocolo' => $atividadeDTO->getDblIdProtocolo(),
                    'dthAbertura' => $atividadeDTO->getDthAbertura(),
                    'sinInicial' => $atividadeDTO->getStrSinInicial(),
                    'dtaPrazo' => $atividadeDTO->getDtaPrazo(),
                    'tipoVisualizacao' => $atividadeDTO->getNumTipoVisualizacao(),
                    'dthConclusao' => null,
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo de sobrestamento de processo
     * @param EntradaSobrestarProcessoAPI $entradaSobrestarProcessoAPI
     * @return array
     */
    protected function sobrestamentoProcessoControlado(EntradaSobrestarProcessoAPI $entradaSobrestarProcessoAPI)
    {
        try {
            $seiRN = new SeiRN();
            $seiRN->sobrestarProcesso($entradaSobrestarProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo sobrestado com sucesso');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * @param $protocolo
     * @return array
     */
    protected function removerSobrestamentoProcessoControlado(ProcedimentoDTO $procedimentoDTOParam)
    {
        try {
            if (!$procedimentoDTOParam->getDblIdProcedimento()) {
                throw new InfraException('Procedimento n?o informado.');
            }
            $seiRN = new SeiRN();
            $entradaRemoverSobrestamentoProcessoAPI = new EntradaRemoverSobrestamentoProcessoAPI();
            $entradaRemoverSobrestamentoProcessoAPI->setIdProcedimento($procedimentoDTOParam->getDblIdProcedimento());

            $seiRN->removerSobrestamentoProcesso($entradaRemoverSobrestamentoProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Sobrestar cancelado com sucesso.');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que retorna os procedimentos com acompanhamento
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOConsulta
     * @return array
     */
    protected function listarProcedimentoAcompanhamentoConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $usuarioAtribuicaoAtividade = null;
            $mdWsSeiProtocoloDTOConsulta = new MdWsSeiProtocoloDTO();
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $mdWsSeiProtocoloDTOConsulta->setNumIdGrupoAcompanhamentoProcedimento($mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento());
            }

            if (!$mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioGeradorAcompanhamento()) {
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioGeradorAcompanhamento(SessaoSEI::getInstance()->getNumIdUsuario());
            } else {
                $mdWsSeiProtocoloDTOConsulta->setNumIdUsuarioGeradorAcompanhamento($mdWsSeiProtocoloDTOParam->getNumIdUsuarioGeradorAcompanhamento());
            }

            if (is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual(0);
            } else {
                $mdWsSeiProtocoloDTOConsulta->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            }

            if (!$mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno(10);
            } else {
                $mdWsSeiProtocoloDTOConsulta->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            }

            $protocoloRN = new ProtocoloRN();
            $mdWsSeiProtocoloDTOConsulta->retTodos();
            $mdWsSeiProtocoloDTOConsulta->retDblIdProtocolo();
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();
            $mdWsSeiProtocoloDTOConsulta->retStrSiglaUnidadeGeradora();
            $mdWsSeiProtocoloDTOConsulta->retStrSinCienciaProcedimento();
            $mdWsSeiProtocoloDTOConsulta->setOrdDthGeracaoAcompanhamento(InfraDTO::$TIPO_ORDENACAO_ASC);
            $mdWsSeiProtocoloDTOConsulta->retStrNomeTipoProcedimentoProcedimento();

            $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTOConsulta);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $mdWsSeiProtocoloDTOConsulta->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que pesquisa todos o procedimentos em todas as unidades
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam
     * @return array
     */
    protected function pesquisarTodosProcessosConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()) {
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if (!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            }

            if ($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $pesquisaPendenciaDTO->setNumIdGrupoAcompanhamentoProcedimento(
                    $mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento()
                );
            }
            if ($mdWsSeiProtocoloDTOParam->isSetStrProtocoloFormatadoPesquisa()) {
                $strProtocoloFormatado = InfraUtil::retirarFormatacao(
                    $mdWsSeiProtocoloDTOParam->getStrProtocoloFormatadoPesquisa(), false
                );
                $pesquisaPendenciaDTO->setStrProtocoloFormatadoPesquisaProtocolo(
                    '%' . $strProtocoloFormatado . '%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $atividadeRN = new MdWsSeiAtividadeRN();
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que retorna os procedimentos com acompanhamento com filtro opcional de grupo de acompanhamento e protocolo
     * formatado
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam
     * @return array
     */
    protected function pesquisarProcedimentoConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()) {
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if (!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            }

            if ($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdGrupoAcompanhamentoProcedimento()) {
                $pesquisaPendenciaDTO->setNumIdGrupoAcompanhamentoProcedimento(
                    $mdWsSeiProtocoloDTOParam->getNumIdGrupoAcompanhamentoProcedimento()
                );
            }
            if ($mdWsSeiProtocoloDTOParam->isSetStrProtocoloFormatadoPesquisa()) {
                $strProtocoloFormatado = InfraUtil::retirarFormatacao(
                    $mdWsSeiProtocoloDTOParam->getStrProtocoloFormatadoPesquisa(), false
                );
                $pesquisaPendenciaDTO->setStrProtocoloFormatadoPesquisaProtocolo(
                    '%' . $strProtocoloFormatado . '%',
                    InfraDTO::$OPER_LIKE
                );
            }

            $atividadeRN = new MdWsSeiAtividadeRN();
            $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que lista os processos
     * @param MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTO
     * @return array
     */
    protected function listarProcessosConectado(MdWsSeiProtocoloDTO $mdWsSeiProtocoloDTOParam)
    {
        try {
            $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();

            $usuarioAtribuicaoAtividade = null;
            if ($mdWsSeiProtocoloDTOParam->isSetNumIdUsuarioAtribuicaoAtividade()) {
                $usuarioAtribuicaoAtividade = $mdWsSeiProtocoloDTOParam->getNumIdUsuarioAtribuicaoAtividade();
            }

            if (!is_null($mdWsSeiProtocoloDTOParam->getNumPaginaAtual())) {
                $pesquisaPendenciaDTO->setNumPaginaAtual($mdWsSeiProtocoloDTOParam->getNumPaginaAtual());
            } else {
                $pesquisaPendenciaDTO->setNumPaginaAtual(0);
            }

            if ($mdWsSeiProtocoloDTOParam->isSetNumMaxRegistrosRetorno()) {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno($mdWsSeiProtocoloDTOParam->getNumMaxRegistrosRetorno());
            } else {
                $pesquisaPendenciaDTO->setNumMaxRegistrosRetorno(10);
            }
            if ($mdWsSeiProtocoloDTOParam->getStrSinApenasMeus() == 'S') {
                $pesquisaPendenciaDTO->setStrStaTipoAtribuicao('M');
            }

            $atividadeRN = new MdWsSeiAtividadeRN();
            $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
            $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
            $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
            $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
            $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
            $pesquisaPendenciaDTO->setStrSinCredenciais('S');
            $pesquisaPendenciaDTO->setStrSinSituacoes('S');
            $pesquisaPendenciaDTO->setStrSinMarcadores('S');

            if ($mdWsSeiProtocoloDTOParam->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_R) {
                $pesquisaPendenciaDTO->setStrSinInicial('N');
                $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            } else if ($mdWsSeiProtocoloDTOParam->getStrSinTipoBusca() == MdWsSeiProtocoloDTO::SIN_TIPO_BUSCA_G) {
                $pesquisaPendenciaDTO->setStrSinInicial('S');
                $ret = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
            } else {
                throw new InfraException('O tipo de busca deve ser (R)ecebidos ou (G)erados');
            }
            $result = $this->montaRetornoListagemProcessos($ret, $usuarioAtribuicaoAtividade);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $pesquisaPendenciaDTO->getNumTotalRegistros());
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }

    }

    /**
     * Metodo que monta o retorno da listagem do processo com base no retorno da consulta
     * @param array $ret
     * @param null $usuarioAtribuicaoAtividade
     * @return array
     */
    private function montaRetornoListagemProcessos(array $ret, $usuarioAtribuicaoAtividade = null)
    {
        $result = array();
        $protocoloRN = new ProtocoloRN();
        foreach ($ret as $dto) {
            $usuarioAtribuido = null;
            $documentoNovo = 'N';
            $documentoPublicado = 'N';
            $possuiAnotacao = 'N';
            $possuiAnotacaoPrioridade = 'N';
            $usuarioVisualizacao = 'N';
            $tipoVisualizacao = 'N';
            $retornoProgramado = 'N';
            $retornoAtrasado = 'N';
            $arrDadosAbertura = array();
            $procedimentoDTO = null;
            $protocoloDTO = new MdWsSeiProtocoloDTO();
            if ($dto instanceof ProcedimentoDTO) {
                $protocoloDTO = new MdWsSeiProtocoloDTO();
                $protocoloDTO->setDblIdProtocolo($dto->getDblIdProcedimento());
                $protocoloDTO->retDblIdProtocolo();
                $protocoloDTO->retNumIdUnidadeGeradora();
                $protocoloDTO->retStrStaProtocolo();
                $protocoloDTO->retStrProtocoloFormatado();
                $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
                $protocoloDTO->retStrDescricao();
                $protocoloDTO->retStrSiglaUnidadeGeradora();
                $protocoloDTO->retStrStaGrauSigilo();
                $protocoloDTO->retStrStaNivelAcessoLocal();
                $protocoloDTO->retStrStaNivelAcessoGlobal();
                $protocoloDTO->retStrSinCienciaProcedimento();
                $protocoloDTO->retStrStaEstado();
                $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
            } else {
                $protocoloDTO = $dto;
            }

            $processoBloqueado = $protocoloDTO->getStrStaEstado() == ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO ? 'S' : 'N';
            $processoRemocaoSobrestamento = 'N';
            $processoDocumentoIncluidoAssinado = 'N';
            $processoPublicado = 'N';

            $atividadeRN = new MdWsSeiAtividadeRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            $atividadeDTOConsulta->retDblIdProtocolo();
            $atividadeDTOConsulta->retNumIdTarefa();
            $atividadeDTOConsulta->retNumTipoVisualizacao();
            $atividadeDTOConsulta->retStrNomeUsuarioAtribuicao();
            $atividadeDTOConsulta->retNumIdUsuarioVisualizacao();
            $atividadeDTOConsulta->retNumIdAtividade();

            $atividadeDTOConsulta->setNumMaxRegistrosRetorno(1);
            $atividadeDTOConsulta->setOrdNumIdAtividade(InfraDTO::$TIPO_ORDENACAO_DESC);

            $arrAtividades = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            if ($arrAtividades) {
                /** @var AtividadeDTO $atividadeDTO */
                $atividadeDTO = $arrAtividades[0];
                $documentoNovo = $atividadeDTO->getNumIdTarefa() == 1 ? 'S' : 'N';
                $usuarioAtribuido = $atividadeDTO->getStrNomeUsuarioAtribuicao();
                $tipoVisualizacao = $atividadeDTO->getNumTipoVisualizacao() == 0 ? 'S' : 'N';
                if ($atividadeDTO->getNumIdUsuarioVisualizacao() == $usuarioAtribuicaoAtividade) {
                    $usuarioVisualizacao = 'S';
                }
            }
            $arrAtividadePendenciaDTO = array();
            if ($dto instanceof ProcedimentoDTO) {
                $procedimentoDTO = $dto;
                $arrAtividadePendenciaDTO = $procedimentoDTO->getArrObjAtividadeDTO();
            } else {
                $pesquisaPendenciaDTO = new MdWsSeiPesquisarPendenciaDTO();
                $pesquisaPendenciaDTO->setNumIdUsuario(SessaoSEI::getInstance()->getNumIdUsuario());
                $pesquisaPendenciaDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $pesquisaPendenciaDTO->setStrStaEstadoProcedimento(array(ProtocoloRN::$TE_NORMAL, ProtocoloRN::$TE_PROCEDIMENTO_BLOQUEADO));
                $pesquisaPendenciaDTO->setStrSinAnotacoes('S');
                $pesquisaPendenciaDTO->setStrSinRetornoProgramado('S');
                $pesquisaPendenciaDTO->setStrSinCredenciais('S');
                $pesquisaPendenciaDTO->setStrSinSituacoes('S');
                $pesquisaPendenciaDTO->setStrSinMarcadores('S');
                $pesquisaPendenciaDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
                $arrProcedimentoDTO = $atividadeRN->listarPendencias($pesquisaPendenciaDTO);
                if ($arrProcedimentoDTO) {
                    $procedimentoDTO = $arrProcedimentoDTO[0];
                    $arrAtividadePendenciaDTO = $procedimentoDTO->getArrObjAtividadeDTO();
                }
            }
            if ($arrAtividadePendenciaDTO) {
                $atividadePendenciaDTO = $arrAtividadePendenciaDTO[0];
                if ($atividadePendenciaDTO->getNumTipoVisualizacao() & AtividadeRN::$TV_REMOCAO_SOBRESTAMENTO) {
                    $processoRemocaoSobrestamento = 'S';
                }
                if ($atividadePendenciaDTO->getNumTipoVisualizacao() & AtividadeRN::$TV_ATENCAO) {
                    $processoDocumentoIncluidoAssinado = 'S';
                }
                if ($atividadePendenciaDTO->getNumTipoVisualizacao() & AtividadeRN::$TV_PUBLICACAO) {
                    $processoPublicado = 'S';
                }
                $retornoProgramadoDTOConsulta = new RetornoProgramadoDTO();
                $retornoProgramadoDTOConsulta->retDblIdProtocoloAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeOrigemAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retStrSiglaUnidadeAtividadeEnvio();
                $retornoProgramadoDTOConsulta->retDtaProgramada();
                $retornoProgramadoDTOConsulta->setNumIdUnidadeAtividadeEnvio(SessaoSEI::getInstance()->getNumIdUnidadeAtual());
                $retornoProgramadoDTOConsulta->setDblIdProtocoloAtividadeEnvio(array_unique(InfraArray::converterArrInfraDTO($arrAtividadePendenciaDTO, 'IdProtocolo')), InfraDTO::$OPER_IN);
                $retornoProgramadoDTOConsulta->setNumIdAtividadeRetorno(null);
                $objRetornoProgramadoRN = new RetornoProgramadoRN();
                $arrRetornoProgramadoDTO = $objRetornoProgramadoRN->listar($retornoProgramadoDTOConsulta);
                if ($arrRetornoProgramadoDTO) {
                    $retornoProgramado = 'S';
                    $strDataAtual = InfraData::getStrDataAtual();
                    foreach ($arrRetornoProgramadoDTO as $retornoProgramadoDTO) {
                        $numPrazo = InfraData::compararDatas($strDataAtual, $retornoProgramadoDTO->getDtaProgramada());
                        if ($numPrazo < 0) {
                            $retornoAtrasado = 'S';
                            $retornoData = array(
                                'date' => $retornoProgramadoDTO->getDtaProgramada(),
                                'unidade' => $retornoProgramadoDTO->getStrSiglaUnidadeOrigemAtividadeEnvio()
                            );

                        }
                    }
                }
            }

            $documentoRN = new DocumentoRN();
            $documentoDTOConsulta = new DocumentoDTO();
            $documentoDTOConsulta->setDblIdProcedimento($protocoloDTO->getDblIdProtocolo());
            $documentoDTOConsulta->retDblIdDocumento();
            $arrDocumentos = $documentoRN->listarRN0008($documentoDTOConsulta);
            if ($arrDocumentos) {
                $arrIdDocumentos = array();
                /** @var DocumentoDTO $documentoDTO */
                foreach ($arrDocumentos as $documentoDTO) {
                    $arrIdDocumentos[] = $documentoDTO->getDblIdDocumento();
                }
                $publiacaoRN = new PublicacaoRN();
                $publicacaoDTO = new PublicacaoDTO();
                $publicacaoDTO->retNumIdPublicacao();
                $publicacaoDTO->setNumMaxRegistrosRetorno(1);
                $publicacaoDTO->adicionarCriterio(
                    array('IdDocumento'),
                    array(InfraDTO::$OPER_IN),
                    array($arrIdDocumentos)
                );
                $arrPublicacaoDTO = $publiacaoRN->listarRN1045($publicacaoDTO);
                $documentoPublicado = count($arrPublicacaoDTO) ? 'S' : 'N';
            }
            $anotacaoRN = new AnotacaoRN();
            $anotacaoDTOConsulta = new AnotacaoDTO();
            $anotacaoDTOConsulta->setNumMaxRegistrosRetorno(1);
            $anotacaoDTOConsulta->retDblIdProtocolo();
            $anotacaoDTOConsulta->retStrDescricao();
            $anotacaoDTOConsulta->retNumIdUnidade();
            $anotacaoDTOConsulta->retNumIdUsuario();
            $anotacaoDTOConsulta->retDthAnotacao();
            $anotacaoDTOConsulta->retStrSinPrioridade();
            $anotacaoDTOConsulta->retStrStaAnotacao();
            $anotacaoDTOConsulta->retNumIdAnotacao();
            $anotacaoDTOConsulta->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());
            //$anotacaoDTOConsulta->setNumIdUnidade($protocoloDTO->getNumIdUnidadeGeradora());
            $arrAnotacao = $anotacaoRN->listar($anotacaoDTOConsulta);
            $possuiAnotacao = count($arrAnotacao) ? 'S' : 'N';
            foreach ($arrAnotacao as $anotacaoDTO) {
                if ($anotacaoDTO->getStrSinPrioridade() == 'S') {
                    $possuiAnotacaoPrioridade = 'S';
                    break;
                }
            }
            $resultAnotacao = array();
            /** @var AnotacaoDTO $anotacaoDTO */
            foreach ($arrAnotacao as $anotacaoDTO) {
                $resultAnotacao[] = array(
                    'idAnotacao' => $anotacaoDTO->getNumIdAnotacao(),
                    'idProtocolo' => $anotacaoDTO->getDblIdProtocolo(),
                    'descricao' => $anotacaoDTO->getStrDescricao(),
                    'idUnidade' => $anotacaoDTO->getNumIdUnidade(),
                    'idUsuario' => $anotacaoDTO->getNumIdUsuario(),
                    'dthAnotacao' => $anotacaoDTO->getDthAnotacao(),
                    'sinPrioridade' => $anotacaoDTO->getStrSinPrioridade(),
                    'staAnotacao' => $anotacaoDTO->getStrStaAnotacao()
                );
            }
            if ($procedimentoDTO && $procedimentoDTO->getStrStaEstadoProtocolo() != ProtocoloRN::$TE_PROCEDIMENTO_ANEXADO) {
                $ret = $this->listarUnidadeAberturaProcedimento($procedimentoDTO);
                if (!$ret['sucesso']) {
                    throw new Exception($ret['mensagem']);
                }
                $arrDadosAbertura = $ret['data'];
            }

            $result[] = array(
                'id' => $protocoloDTO->getDblIdProtocolo(),
                'status' => $protocoloDTO->getStrStaProtocolo(),
                'atributos' => array(
                    'idProcedimento' => $protocoloDTO->getDblIdProtocolo(),
                    'idProtocolo' => $protocoloDTO->getDblIdProtocolo(),
                    'numero' => $protocoloDTO->getStrProtocoloFormatado(),
                    'tipoProcesso' => $protocoloDTO->getStrNomeTipoProcedimentoProcedimento(),
                    'descricao' => $protocoloDTO->getStrDescricao(),
                    'usuarioAtribuido' => $usuarioAtribuido,
                    'unidade' => array(
                        'idUnidade' => $protocoloDTO->getNumIdUnidadeGeradora(),
                        'sigla' => $protocoloDTO->getStrSiglaUnidadeGeradora()
                    ),
                    'dadosAbertura' => $arrDadosAbertura,
                    'anotacoes' => $resultAnotacao,
                    'status' => array(
                        'documentoSigiloso' => $protocoloDTO->getStrStaGrauSigilo(),
                        'documentoRestrito' => $protocoloDTO->getStrStaNivelAcessoLocal() == 1 ? 'S' : 'N',
                        'documentoNovo' => $documentoNovo,
                        'documentoPublicado' => $documentoPublicado,
                        'anotacao' => $possuiAnotacao,
                        'anotacaoPrioridade' => $possuiAnotacaoPrioridade,//verificar
                        'ciencia' => $protocoloDTO->getStrSinCienciaProcedimento(),
                        'retornoProgramado' => $retornoProgramado,
                        'retornoData' => $retornoData,
                        'retornoAtrasado' => $retornoAtrasado,
                        'processoAcessadoUsuario' => $tipoVisualizacao,
                        // foi invertido o processoAcessadoUsuario e processoAcessadoUnidade,
                        // pois em todos os outros metodos e igual e somente neste era diferente...
                        'processoAcessadoUnidade' => $usuarioVisualizacao,
                        //Novos Status de Processo igual listagem
                        'processoRemocaoSobrestamento' => $processoRemocaoSobrestamento,
                        'processoBloqueado' => $processoBloqueado,
                        'processoDocumentoIncluidoAssinado' => $processoDocumentoIncluidoAssinado,
                        'processoPublicado' => $processoPublicado,
                        'nivelAcessoGlobal' => $protocoloDTO->getStrStaNivelAcessoGlobal()
                    )
                )
            );
        }

        return $result;
    }

    protected function listarUnidadeAberturaProcedimentoConectado(ProcedimentoDTO $procedimentoDTO)
    {
        try {
            $result = array();
            $atividadeRN = new MdWsSeiAtividadeRN();
            $strStaNivelAcessoGlobal = $procedimentoDTO->getStrStaNivelAcessoGlobalProtocolo();
            $dblIdProcedimento = $procedimentoDTO->getDblIdProcedimento();
            $atividadeDTO = new AtividadeDTO();
            $atividadeDTO->setDistinct(true);
            $atividadeDTO->retStrSiglaUnidade();
            $atividadeDTO->retNumIdUnidade();
            $atividadeDTO->retStrDescricaoUnidade();

            $atividadeDTO->setOrdStrSiglaUnidade(InfraDTO::$TIPO_ORDENACAO_ASC);

            if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {
                $atividadeDTO->retNumIdUsuario();
                $atividadeDTO->retStrSiglaUsuario();
                $atividadeDTO->retStrNomeUsuario();
            } else {
                $atividadeDTO->retNumIdUsuarioAtribuicao();
                $atividadeDTO->retStrSiglaUsuarioAtribuicao();
                $atividadeDTO->retStrNomeUsuarioAtribuicao();

                //ordena descendente pois no envio de processo que j� existe na unidade e est� atribu�do ficar� com mais de um andamento em aberto
                //desta forma os andamentos com usu�rio nulo (envios do processo) ser�o listados depois
                $atividadeDTO->setOrdStrSiglaUsuarioAtribuicao(InfraDTO::$TIPO_ORDENACAO_DESC);

            }
            $atividadeDTO->setDblIdProtocolo($dblIdProcedimento);
            $atividadeDTO->setDthConclusao(null);

            //sigiloso sem credencial nao considera o usuario atual
            if ($strStaNivelAcessoGlobal == ProtocoloRN::$NA_SIGILOSO) {

                $acessoDTO = new AcessoDTO();
                $acessoDTO->setDistinct(true);
                $acessoDTO->retNumIdUsuario();
                $acessoDTO->setDblIdProtocolo($dblIdProcedimento);
                $acessoDTO->setStrStaTipo(AcessoRN::$TA_CREDENCIAL_PROCESSO);

                $acessoRN = new AcessoRN();
                $arrAcessoDTO = $acessoRN->listar($acessoDTO);

                $atividadeDTO->setNumIdUsuario(InfraArray::converterArrInfraDTO($arrAcessoDTO, 'IdUsuario'), InfraDTO::$OPER_IN);
            }
            $arrAtividadeDTO = $atividadeRN->listarRN0036($atividadeDTO);

            if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                $arrAtividadeDTO = InfraArray::distinctArrInfraDTO($arrAtividadeDTO, 'SiglaUnidade');
            }
            if (count($arrAtividadeDTO) == 0) {
                $result['info'] = 'Processo n�o possui andamentos abertos.';
                $result['lista'] = array();
            } else {
                if (count($arrAtividadeDTO) == 1) {
                    $atividadeDTO = $arrAtividadeDTO[0];
                    if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                        $result['info'] = 'Processo aberto somente na unidade:';
                        $result['unidades'][] = array(
                            'id' => $atividadeDTO->getNumIdUnidade(),
                            'nome' => $atividadeDTO->getStrSiglaUnidade()
                        );
                        $result['lista'][] = array(
                            'sigla' => $atividadeDTO->getStrSiglaUnidade()
                        );
                    } else {
                        $result['info'] = 'Processo aberto com o usu�rio:';
                        $atividadeDTO = $arrAtividadeDTO[0];
                        $result['unidades'][] = array(
                            'id' => $atividadeDTO->getNumIdUnidade(),
                            'nome' => $atividadeDTO->getStrSiglaUnidade()
                        );
                        $result['lista'][] = array(
                            'sigla' => $atividadeDTO->getStrNomeUsuario()
                        );
                    }
                } else {
                    if ($strStaNivelAcessoGlobal != ProtocoloRN::$NA_SIGILOSO) {
                        $result['info'] = 'Processo aberto nas unidades:';
                        foreach ($arrAtividadeDTO as $atividadeDTO) {
                            $result['unidades'][] = array(
                                'id' => $atividadeDTO->getNumIdUnidade(),
                                'nome' => $atividadeDTO->getStrSiglaUnidade()
                            );
                            $sigla = $atividadeDTO->getStrSiglaUnidade();
                            if ($atividadeDTO->getNumIdUsuarioAtribuicao() != null) {
                                $sigla .= ' (atribu�do a ' . $atividadeDTO->getStrNomeUsuarioAtribuicao() . ')';
                            }
                            $result['lista'][] = array(
                                'sigla' => $sigla
                            );
                        }
                    } else {
                        $result['info'] = 'Processo aberto com os usu�rios:';
                        foreach ($arrAtividadeDTO as $atividadeDTO) {
                            $result['unidades'][] = array(
                                'id' => $atividadeDTO->getNumIdUnidade(),
                                'nome' => $atividadeDTO->getStrSiglaUnidade()
                            );
                            $sigla = $atividadeDTO->getStrNomeUsuario() . ' na unidade ' . $atividadeDTO->getStrSiglaUnidade();
                            $result['lista'][] = array(
                                'sigla' => $sigla
                            );
                        }
                    }
                }
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que retorna as ciencias nos processos
     * @param ProtocoloDTO $protocoloDTOParam
     * @return array
     */
    protected function listarCienciaProcessoConectado(ProtocoloDTO $protocoloDTOParam)
    {
        try {
            if (!$protocoloDTOParam->isSetDblIdProtocolo()) {
                throw new InfraException('Protocolo n�o informado.');
            }

            $result = array();
            $mdWsSeiProcessoRN = new MdWsSeiProcessoRN();
            $atividadeDTOConsulta = new AtividadeDTO();
            $atividadeDTOConsulta->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $atividadeDTOConsulta->setNumIdTarefa(TarefaRN::$TI_PROCESSO_CIENCIA);
            $atividadeDTOConsulta->retDthAbertura();
            $atividadeDTOConsulta->retStrSiglaUnidade();
            $atividadeDTOConsulta->retStrNomeTarefa();
            $atividadeDTOConsulta->retStrSiglaUsuarioOrigem();
            $atividadeDTOConsulta->retNumIdAtividade();
            $atividadeRN = new AtividadeRN();
            $ret = $atividadeRN->listarRN0036($atividadeDTOConsulta);
            /** @var AtividadeDTO $atividadeDTO */
            foreach ($ret as $atividadeDTO) {
                $mdWsSeiProcessoDTO = new MdWsSeiProcessoDTO();
                $mdWsSeiProcessoDTO->setStrTemplate($atividadeDTO->getStrNomeTarefa());
                $mdWsSeiProcessoDTO->setNumIdAtividade($atividadeDTO->getNumIdAtividade());
                $result[] = array(
                    'data' => $atividadeDTO->getDthAbertura(),
                    'unidade' => $atividadeDTO->getStrSiglaUnidade(),
                    'nome' => $atividadeDTO->getStrSiglaUsuarioOrigem(),
                    'descricao' => $mdWsSeiProcessoRN->traduzirTemplate($mdWsSeiProcessoDTO)
                );
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }


    /**
     * Metodo que da ciencia ao processo/procedimento
     * @param ProcedimentoDTO $procedimentoDTO
     * @info E obrigatorio informar o id do procedimento
     * @return array
     */
    protected function darCienciaControlado(ProcedimentoDTO $procedimentoDTOParam)
    {
        try {
            if (!$procedimentoDTOParam->isSetDblIdProcedimento()) {
                throw new InfraException('E obrigatorio informar o procedimento!');
            }

            $procedimentoRN = new ProcedimentoRN();
            $procedimentoRN->darCiencia($procedimentoDTOParam);

            return MdWsSeiRest::formataRetornoSucessoREST('Ci�ncia processo realizado com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que conclui o procedimento/processo
     * @param EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI
     * @info ele recebe o n�mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function concluirProcessoControlado(EntradaConcluirProcessoAPI $entradaConcluirProcessoAPI)
    {
        try {
            if (!$entradaConcluirProcessoAPI->getProtocoloProcedimento()) {
                throw new InfraException('E obrigtorio informar o protocolo do procedimento!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->concluirProcesso($entradaConcluirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo conclu�do com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Metodo que atribui o processo a uma pessoa
     * @param EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI
     * @info Os parametros IdUsuario, ProtocoloProcedimento e SinReabrir sao obrigatorios. O parametro ProtocoloProcedimento
     * recebe o n?mero do ProtocoloProcedimentoFormatadoPesquisa da tabela protocolo
     * @return array
     */
    protected function atribuirProcessoControlado(EntradaAtribuirProcessoAPI $entradaAtribuirProcessoAPI)
    {
        try {
            if (!$entradaAtribuirProcessoAPI->getProtocoloProcedimento()) {
                throw new InfraException('E obrigatorio informar o protocolo do processo!');
            }
            if (!$entradaAtribuirProcessoAPI->getIdUsuario()) {
                throw new InfraException('E obrigatorio informar o usu?rio do processo!');
            }

            $objSeiRN = new SeiRN();
            $objSeiRN->atribuirProcesso($entradaAtribuirProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo atribu�do com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Encapsula o objeto ENtradaEnviarProcessoAPI para o metodo enviarProcesso
     * @param array $post
     * @return EntradaEnviarProcessoAPI
     */
    public function encapsulaEnviarProcessoEntradaEnviarProcessoAPI(array $post)
    {
        $entradaEnviarProcessoAPI = new EntradaEnviarProcessoAPI();
        if (isset($post['numeroProcesso'])) {
            $entradaEnviarProcessoAPI->setProtocoloProcedimento($post['numeroProcesso']);
        }
        if (isset($post['unidadesDestino'])) {
            $entradaEnviarProcessoAPI->setUnidadesDestino(explode(',', $post['unidadesDestino']));
        }
        if (isset($post['sinManterAbertoUnidade'])) {
            $entradaEnviarProcessoAPI->setSinManterAbertoUnidade($post['sinManterAbertoUnidade']);
        }
        if (isset($post['sinRemoverAnotacao'])) {
            $entradaEnviarProcessoAPI->setSinRemoverAnotacao($post['sinRemoverAnotacao']);
        }
        if (isset($post['sinEnviarEmailNotificacao'])) {
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao($post['sinEnviarEmailNotificacao']);
        } else {
            $entradaEnviarProcessoAPI->setSinEnviarEmailNotificacao('N');
        }
        if (isset($post['dataRetornoProgramado'])) {
            $entradaEnviarProcessoAPI->setDataRetornoProgramado($post['dataRetornoProgramado']);
        }
        if (isset($post['diasRetornoProgramado'])) {
            $entradaEnviarProcessoAPI->setDiasRetornoProgramado($post['diasRetornoProgramado']);
        }
        if (isset($post['sinDiasUteisRetornoProgramado'])) {
            $entradaEnviarProcessoAPI->setSinDiasUteisRetornoProgramado($post['sinDiasUteisRetornoProgramado']);
        }
        if (isset($post['sinReabrir'])) {
            $entradaEnviarProcessoAPI->setSinReabrir($post['sinReabrir']);
        }

        return $entradaEnviarProcessoAPI;
    }

    /**
     * Metodo que envia o processo para outra unidade
     * @param EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI
     * @info Metodo auxiliar para encapsular dados encapsulaEnviarProcessoEntradaEnviarProcessoAPI
     * @return array
     */
    protected function enviarProcessoControlado(EntradaEnviarProcessoAPI $entradaEnviarProcessoAPI)
    {
        try {
            $objSeiRN = new SeiRN();
            $objSeiRN->enviarProcesso($entradaEnviarProcessoAPI);

            return MdWsSeiRest::formataRetornoSucessoREST('Processo enviado com sucesso!');
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que verifica o acesso a um processo ou documento
     * @param ProtocoloDTO $protocoloDTOParam
     * - Se acesso liberado e chamar autentica��o for false, o usu�rio n�o pode de jeito nenhum visualizar o processo/documento
     * @return array
     */
    protected function verificaAcessoConectado(ProtocoloDTO $protocoloDTOParam)
    {
        try {
            $acessoLiberado = false;
            $chamarAutenticacao = false;
            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = new ProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($protocoloDTOParam->getDblIdProtocolo());
            $protocoloDTO->retStrStaNivelAcessoGlobal();
            $protocoloDTO->retDblIdProtocolo();
            $protocoloDTO = $protocoloRN->consultarRN0186($protocoloDTO);
            if (!$protocoloDTO) {
                throw new Exception('Processo n�o encontrado!');
            }
            if ($protocoloDTO->getStrStaNivelAcessoGlobal() == ProtocoloRN::$NA_SIGILOSO) {
                $objPesquisaProtocoloDTO = new PesquisaProtocoloDTO();
                $objPesquisaProtocoloDTO->setStrStaTipo(ProtocoloRN::$TPP_PROCEDIMENTOS);
                $objPesquisaProtocoloDTO->setStrStaAcesso(ProtocoloRN::$TAP_AUTORIZADO);
                $objPesquisaProtocoloDTO->setDblIdProtocolo($protocoloDTO->getDblIdProtocolo());

                $objProtocoloRN = new ProtocoloRN();
                $arrProtocoloDTO = $objProtocoloRN->pesquisarRN0967($objPesquisaProtocoloDTO);
                if ($arrProtocoloDTO) {
                    $chamarAutenticacao = true;
                }
            } else {
                $acessoLiberado = true;
                $chamarAutenticacao = false;
            }

            return MdWsSeiRest::formataRetornoSucessoREST(
                null,
                array('acessoLiberado' => $acessoLiberado, 'chamarAutenticacao' => $chamarAutenticacao)
            );

        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * Identifica o acesso do usu�rio em um processo
     * @param UsuarioDTO $usuarioDTO
     * @param ProtocoloDTO $protocoloDTO
     * @return array
     */
    public function apiIdentificacaoAcesso(UsuarioDTO $usuarioDTO, ProtocoloDTO $protocoloDTO)
    {
        try {
            $objInfraSip = new InfraSip(SessaoSEI::getInstance());
            $objInfraSip->autenticar(SessaoSEI::getInstance()->getNumIdOrgaoUsuario(), null, SessaoSEI::getInstance()->getStrSiglaUsuario(), $usuarioDTO->getStrSenha());
            AuditoriaSEI::getInstance()->auditar('usuario_validar_acesso');
            $ret = $this->verificaAcesso($protocoloDTO);
            if (!$ret['sucesso']) {
                return $ret;
            }
            $acessoAutorizado = false;
            if ($ret['data']['acessoLiberado'] || $ret['data']['chamarAutenticacao']) {
                $acessoAutorizado = true;
            }

            return MdWsSeiRest::formataRetornoSucessoREST(null, array('acessoAutorizado' => $acessoAutorizado));
        } catch (InfraException $e) {
            $infraValidacaoDTO = $e->getArrObjInfraValidacao()[0];
            $eAuth = new Exception($infraValidacaoDTO->getStrDescricao(), $e->getCode(), $e);
            return MdWsSeiRest::formataRetornoErroREST($eAuth);
        } catch (Exception $e) {
            return MdWsSeiRest::formataRetornoErroREST($e);
        }
    }

    /**
     * M�todo que consulta os processos no Solar
     * @param MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO
     * @return array
     */
    protected function pesquisarProcessosSolarConectado(MdWsSeiPesquisaProtocoloSolrDTO $pesquisaProtocoloSolrDTO)
    {
        try {
            $partialfields = '';

            if ($pesquisaProtocoloSolrDTO->isSetStrDescricao() && $pesquisaProtocoloSolrDTO->getStrDescricao() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(' . SolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrDescricao(), 'desc') . ')';
            }

            if ($pesquisaProtocoloSolrDTO->isSetStrObservacao() && $pesquisaProtocoloSolrDTO->getStrObservacao() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(' . SolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrObservacao(), 'obs_' . SessaoSEI::getInstance()->getNumIdUnidadeAtual()) . ')';
            }

            //o- verificar l�gica do solar
            if ($pesquisaProtocoloSolrDTO->isSetDblIdProcedimento() && $pesquisaProtocoloSolrDTO->getDblIdProcedimento() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }

                $objRelProtocoloProtocoloDTO = new RelProtocoloProtocoloDTO();
                $objRelProtocoloProtocoloDTO->retDblIdProtocolo2();
                $objRelProtocoloProtocoloDTO->setStrStaAssociacao(RelProtocoloProtocoloRN::$TA_PROCEDIMENTO_ANEXADO);
                $objRelProtocoloProtocoloDTO->setDblIdProtocolo1($pesquisaProtocoloSolrDTO->getDblIdProcedimento());

                $objRelProtocoloProtocoloRN = new RelProtocoloProtocoloRN();
                $arrIdProcessosAnexados = InfraArray::converterArrInfraDTO($objRelProtocoloProtocoloRN->listarRN0187($objRelProtocoloProtocoloDTO), 'IdProtocolo2');

                if (count($arrIdProcessosAnexados) == 0) {
                    $partialfields .= '(id_proc:' . $pesquisaProtocoloSolrDTO->getDblIdProcedimento() . ')';
                } else {

                    $strProcessos = 'id_proc:' . $pesquisaProtocoloSolrDTO->getDblIdProcedimento();
                    foreach ($arrIdProcessosAnexados as $dblIdProcessoAnexado) {
                        $strProcessos .= ' OR id_proc:' . $dblIdProcessoAnexado;
                    }

                    $partialfields .= '(' . $strProcessos . ')';
                }
            }

            if ($pesquisaProtocoloSolrDTO->isSetStrProtocoloPesquisa() && $pesquisaProtocoloSolrDTO->getStrProtocoloPesquisa() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(prot_pesq:*' . InfraUtil::retirarFormatacao($pesquisaProtocoloSolrDTO->getStrProtocoloPesquisa(), false) . '*)';
            }

            if ($pesquisaProtocoloSolrDTO->isSetNumIdTipoProcedimento() && $pesquisaProtocoloSolrDTO->getNumIdTipoProcedimento() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(id_tipo_proc:' . $pesquisaProtocoloSolrDTO->getNumIdTipoProcedimento() . ')';
            }

            if ($pesquisaProtocoloSolrDTO->isSetNumIdSerie() && $pesquisaProtocoloSolrDTO->getNumIdSerie() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(id_serie:' . $pesquisaProtocoloSolrDTO->getNumIdSerie() . ')';
            }

            if ($pesquisaProtocoloSolrDTO->isSetStrNumero() && $pesquisaProtocoloSolrDTO->getStrNumero() != null) {
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $partialfields .= '(numero:*' . $pesquisaProtocoloSolrDTO->getStrNumero() . '*)';
            }

            $dtaInicio = null;
            $dtaFim = null;
            if($pesquisaProtocoloSolrDTO->isSetStrStaTipoData()){
                if ($pesquisaProtocoloSolrDTO->getStrStaTipoData() == '0') {
                    $dtaInicio = $pesquisaProtocoloSolrDTO->getDtaInicio();
                    $dtaFim = $pesquisaProtocoloSolrDTO->getDtaFim();
                } else if ($pesquisaProtocoloSolrDTO->getStrStaTipoData() == '30') {
                    $dtaInicio = InfraData::calcularData(30, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS);
                    $dtaFim = InfraData::getStrDataAtual();
                } else if ($pesquisaProtocoloSolrDTO->getStrStaTipoData() == '60') {
                    $dtaInicio = InfraData::calcularData(60, InfraData::$UNIDADE_DIAS, InfraData::$SENTIDO_ATRAS);
                    $dtaFim = InfraData::getStrDataAtual();
                }
            }

            if ($dtaInicio != null && $dtaFim != null) {
                $dia1 = substr($dtaInicio, 0, 2);
                $mes1 = substr($dtaInicio, 3, 2);
                $ano1 = substr($dtaInicio, 6, 4);

                $dia2 = substr($dtaFim, 0, 2);
                $mes2 = substr($dtaFim, 3, 2);
                $ano2 = substr($dtaFim, 6, 4);

                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }

                $partialfields .= 'dta_ger:[' . $ano1 . '-' . $mes1 . '-' . $dia1 . 'T00:00:00Z TO ' . $ano2 . '-' . $mes2 . '-' . $dia2 . 'T00:00:00Z]';
            }

            $objUnidadeDTO = new UnidadeDTO();
            $objUnidadeDTO->setBolExclusaoLogica(false);
            $objUnidadeDTO->retStrSinProtocolo();
            $objUnidadeDTO->setNumIdUnidade(SessaoSEI::getInstance()->getNumIdUnidadeAtual());

            $objUnidadeRN = new UnidadeRN();
            $objUnidadeDTOAtual = $objUnidadeRN->consultarRN0125($objUnidadeDTO);

            if ($objUnidadeDTOAtual->getStrSinProtocolo() == 'N') {

                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }

                $partialfields .= '(tipo_aces:P OR id_uni_aces:*;' . SessaoSEI::getInstance()->getNumIdUnidadeAtual() . ';*)';
            }

            if($pesquisaProtocoloSolrDTO->isSetNumIdGrupoAcompanhamentoProcedimento() && $pesquisaProtocoloSolrDTO->getNumIdGrupoAcompanhamentoProcedimento()) {
                $protocoloRN = new ProtocoloRN();
                $mdWsSeiProtocoloDTO = new MdWsSeiProtocoloDTO();
                $mdWsSeiProtocoloDTO->setNumIdGrupoAcompanhamentoProcedimento($pesquisaProtocoloSolrDTO->getNumIdGrupoAcompanhamentoProcedimento());
                $mdWsSeiProtocoloDTO->retDblIdProtocolo();

                $ret = $protocoloRN->listarRN0668($mdWsSeiProtocoloDTO);
                if(!$ret){
                    return MdWsSeiRest::formataRetornoSucessoREST(null, array(), 0);
                }
                if ($partialfields != '') {
                    $partialfields .= ' AND ';
                }
                $arrIdProcessosAcompanhamento = array();
                /** @var ProtocoloDTO $protocoloDTO */
                foreach($ret as $protocoloDTO){
                    $arrIdProcessosAcompanhamento[] = 'id_proc:' . $protocoloDTO->getDblIdProtocolo();
                }
                $partialfields .= '(' . implode(' OR ', $arrIdProcessosAcompanhamento) . ')';
            }

            $parametros = new stdClass();
            if($pesquisaProtocoloSolrDTO->isSetStrPalavrasChave()){
                $parametros->q = SolrUtil::formatarOperadores($pesquisaProtocoloSolrDTO->getStrPalavrasChave());
            }

            if ($parametros->q != '' && $partialfields != '') {
                $parametros->q = '(' . $parametros->q . ') AND ' . $partialfields;
            } else if ($partialfields != '') {
                $parametros->q = $partialfields;
            }

            $parametros->q = utf8_encode($parametros->q);
            $start = 0;
            $limit = 100;
            if($pesquisaProtocoloSolrDTO->getNumPaginaAtual()){
                $start = $pesquisaProtocoloSolrDTO->getNumPaginaAtual();
            }
            if($pesquisaProtocoloSolrDTO->getNumMaxRegistrosRetorno()){
                $limit = $pesquisaProtocoloSolrDTO->getNumMaxRegistrosRetorno();
            }
            $parametros->start = $start;
            $parametros->rows = $limit;
            $parametros->sort = 'dta_ger desc, id_prot desc';

            $urlBusca = ConfiguracaoSEI::getInstance()->getValor('Solr', 'Servidor') . '/' . ConfiguracaoSEI::getInstance()->getValor('Solr', 'CoreProtocolos') . '/select?' . http_build_query($parametros) . '&hl=true&hl.snippets=2&hl.fl=content&hl.fragsize=100&hl.maxAnalyzedChars=1048576&hl.alternateField=content&hl.maxAlternateFieldLength=100&fl=id,id_proc,id_doc,id_tipo_proc,id_serie,id_anexo,id_uni_ger,prot_doc,prot_proc,numero,id_usu_ger,dta_ger';

            try {
                $resultados = file_get_contents($urlBusca, false);
            } catch (Exception $e) {
                throw new InfraException('Erro realizando pesquisa no Solar.', $e, urldecode($urlBusca), false);
            }

            if ($resultados == '') {
                throw new InfraException('Nenhum retorno encontrado no resultado da pesquisa do Solar, verificar indexa��o.');
            }

            $xml = simplexml_load_string($resultados);
            $arrRet = $xml->xpath('/response/result/@numFound');
            $total = array_shift($arrRet)->__toString();
            $arrIdProcessos = array();
            $registros = $xml->xpath('/response/result/doc');
            $numRegistros = sizeof($registros);

            for ($i = 0; $i < $numRegistros; $i++) {
                $arrIdProcessos[] = SolrUtil::obterTag($registros[$i], 'id_proc', 'long');
            }

            $protocoloRN = new ProtocoloRN();
            $protocoloDTO = new MdWsSeiProtocoloDTO();
            $protocoloDTO->setDblIdProtocolo($arrIdProcessos, InfraDTO::$OPER_IN);
            $protocoloDTO->retDblIdProtocolo();
            $protocoloDTO->retNumIdUnidadeGeradora();
            $protocoloDTO->retStrStaProtocolo();
            $protocoloDTO->retStrProtocoloFormatado();
            $protocoloDTO->retStrNomeTipoProcedimentoProcedimento();
            $protocoloDTO->retStrDescricao();
            $protocoloDTO->retStrSiglaUnidadeGeradora();
            $protocoloDTO->retStrStaGrauSigilo();
            $protocoloDTO->retStrStaNivelAcessoLocal();
            $protocoloDTO->retStrStaNivelAcessoGlobal();
            $protocoloDTO->retStrSinCienciaProcedimento();
            $protocoloDTO->retStrStaEstado();
            $arrProtocoloDTO = $protocoloRN->listarRN0668($protocoloDTO);
            $result = $this->montaRetornoListagemProcessos($arrProtocoloDTO, null);

            return MdWsSeiRest::formataRetornoSucessoREST(null, $result, $total);
        } catch (Exception $e) {

        }
    }


}