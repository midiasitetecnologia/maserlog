export default {
	SET_COLETA_FIXA(state, coletaFixa) {
		state.coletaFixaData = coletaFixa
	},
	REMOVE_RECORD(state, itemId) {
		const coletaFixaIndex = state.coletaFixaData.findIndex((u) => u.id == itemId)
		state.coletaFixaData.splice(coletaFixaIndex, 1)
	},
	SET_COLETA_FIXA_FILTROS(state, payload) {
		state.coletaFixaFiltros = payload
	},
}
