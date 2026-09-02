export default {
	SET_MOTORISTA(state, motorista) {
		state.motoristaData = motorista
	},
	REMOVE_RECORD(state, itemId) {
		const motoristaIndex = state.motoristaData.findIndex((u) => u.id == itemId)
		state.motoristaData.splice(motoristaIndex, 1)
	},
	SET_MOTORISTA_FILTROS(state, payload) {
		state.motoristaFiltros = payload
	},
}
