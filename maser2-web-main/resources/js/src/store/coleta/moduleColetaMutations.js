export default {
	SET_COLETA(state, coleta) {
		state.coletaData = coleta
	},
	SET_URL_IMG(state, url_img) {
		state.coletaUrlImgData = url_img
	},
	SET_COLETA_WEB(state, coleta) {
		state.coletaWebData = coleta
	},
	REMOVE_RECORD(state, itemId) {
		const coletaWebIndex = state.coletaWebData.findIndex((u) => u.id == itemId)
		state.coletaWebData.splice(coletaWebIndex, 1)
	},
	SET_NOTAS_FISCAIS(state, notas_fiscais) {
		state.notasFiscaisData = notas_fiscais
	},
	SET_PERMISSAO_NOTA_FISCAL(state, permissao) {
		state.permissaoNotaFiscalData = permissao
	},
	SET_DADOS_COLETA_NOTA_FISCAL(state, dados_coleta_nf) {
		state.dadosColetaNotaFiscalData = dados_coleta_nf
	},
	SET_URL_IMG_RECIBOS(state, url_img) {
		state.notasUrlImgReciboData = url_img
	},
	SET_COLETA_LOG(state, coletasLog) {
		state.coletaLogData = coletasLog
	},
	SET_COLETA_POS(state, coletasPos) {
		state.coletaPosData = coletasPos
	},
	SET_COLETA_INSTR(state, dados) {
		state.coletaInstrData = dados
	},
	SET_RESUMO_KM_TEMPO_CLIENTE(state, dados) {
		state.resumoKmTempoClienteData = dados
	},
	SET_RESUMO_KM_TEMPO_VEICULO(state, dados) {
		state.resumoKmTempoVeiculoData = dados
	},
	SET_RESUMO_KM_TEMPO_TIPO_VEICULO(state, dados) {
		state.resumoKmTempoTipoVeiculoData = dados
	},
	SET_RESUMO_KM_TEMPO_MOTORISTA(state, dados) {
		state.resumoKmTempoMotoristaData = dados
	},	
}
