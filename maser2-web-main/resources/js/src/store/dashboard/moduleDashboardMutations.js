export default {
	SET_MSG_WISDOM_USER(state, dados) {
		state.msgWisdomUserData = dados
	},
	SET_TAREFAS_HOME(state, dados) {
		state.tarefasHomeData = dados
	},
	SET_CORRIGIR_CAD(state, dados) {
		state.corrigirCadData = dados
	},
	SET_COLETAS_EMISSAO_NOTAS(state, dados) {
		state.coletasEmissaoNotasData = dados
	},
	SET_COLETAS_MD_REALIZADAS(state, dados) {
		state.coletasMDRealizadasData = dados
	},
	SET_ENTREGAS_NAO_REALIZADAS_REENTREGA(state, dados) {
		state.entregasNaoRealizadasReentregaData = dados
	},
	SET_RESUMO_FROTA_HOME(state, dados) {
		state.resumoFrotaHomeData = dados
	},
	SET_RESUMO_COLETAS_HOME(state, dados) {
		state.resumoColetasHomeData = dados
	},
	SET_RESUMO_KM_TEMPO_HOME(state, dados) {
		state.resumoKmTempoHomeData = dados
	},
	SET_MOTORISTAS_DISPONIVEIS(state, dados) {
		state.motoristasDisponiveisData = dados
	},
	SET_SOLIC(state, solic) {
		state.solicData = solic
	},
	SET_URL_IMG(state, url_img) {
		state.dashboardUrlImgData = url_img
	},
}
