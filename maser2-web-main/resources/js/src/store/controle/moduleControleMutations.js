export default {
	COUNT_COLETAS_PENDENTES(state, countColetasPendentes) {
		state.countColetasPendentesData = countColetasPendentes
	},
	COUNT_COLETAS_ANDAMENTO(state, countColetasAndamento) {
		state.countColetasAndamentoData = countColetasAndamento
	},
	COUNT_ENTREGAS_PENDENTES(state, countEntregasPendentes) {
		state.countEntregasPendentesData = countEntregasPendentes
	},
	COUNT_ENTREGAS_ANDAMENTO(state, countEntregasAndamento) {
		state.countEntregasAndamentoData = countEntregasAndamento
	},
	COUNT_SOLICITACOES_FINALIZADAS(state, countSolicitacoesFinalizadas) {
		state.countSolicitacoesFinalizadasData = countSolicitacoesFinalizadas
	},
	SET_COLETAS_PENDENTES(state, coletasPendentes) {
		state.coletasPendentesData = coletasPendentes
	},
	SET_COLETAS_PENDENTES_FILTROS(state, payload) {
		state.coletasPendentesFiltros = payload
	},
	SET_DEFINIR_VEICULOS(state) {
		state.definirVeiculos = !state.definirVeiculos
	},
	SET_RETORNAR_VEICULOS_COLETA(state, dados) {
		state.dadosColetaData = dados.dados_coleta
		state.veiculosData = dados.veiculos
	},
	SET_ENTREGAS_PENDENTES(state, entregasPendentes) {
		state.entregasPendentesData = entregasPendentes
	},
	SET_COLETAS_ANDAMENTO(state, coletasAndamento) {
		state.coletasAndamentoData = coletasAndamento
	},
	SET_ENTREGAS_ANDAMENTO(state, entregasAndamento) {
		state.entregasAndamentoData = entregasAndamento
	},
	SET_SOLICITACOES_FINALIZADAS(state, solicitacoesFinalizadas) {
		state.solicitacoesFinalizadasData = solicitacoesFinalizadas
	},
	SET_VEICULOS_FROTA(state, veiculosFrota) {
		state.veiculosFrotaData = veiculosFrota
	},
	SET_VEICULOS_FROTA_FILTROS(state, payload) {
		state.veiculosFrotaFiltros = payload
	},
	SET_RETORNAR_DADOS_VEICULO_CARGA(state, dados) {
		state.dadosVeiculoCargaData = dados
	},
	SET_RETORNAR_ENTREGAS_PEND_CARGA(state, dados) {
		state.dadosEntregasPendCargaData = dados
	},
	SET_RETORNAR_COLETAS_VEICULO_CARGA(state, dados) {
		state.dadosColetasVeiculoCargaData = dados
	},
	SET_COL_VEICULO_CARGA_FILTROS(state, payload) {
		state.coletasVeiculoCargaFiltros = payload
	},
	EXIBIR_PAINEL_VEICULO_CARGA(state) {
		state.veiculoCarga = !state.veiculoCarga
	},
	SET_RETORNAR_COLETAS_RESUMO_DIA(state, dados) {
		state.dadosColetasResumoDiaData = dados
	},
	SET_URL_IMG(state, url_img) {
		state.coletaUrlImgData = url_img
	},
	EXIBIR_VEICULOS_BALDEACAO(state) {
		state.exibirVeiculosBaldeacao = !state.exibirVeiculosBaldeacao
	},
	SET_VEICULOS_BALDEACAO(state, dados) {
		state.veiculosBaldeacaoDados = dados.dados_coleta
		state.veiculosBaldeacaoVeiculos = dados.veiculos
	},
}