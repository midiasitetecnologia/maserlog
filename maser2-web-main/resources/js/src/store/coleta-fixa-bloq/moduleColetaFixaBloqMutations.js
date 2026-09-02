export default {
	SET_COLETA_FIXA_BLOQ(state, coletaFixaBloq) {
		state.coletaFixaBloqData = coletaFixaBloq
	},
	REMOVE_RECORD(state, itemId) {
		const coletaFixaBloqIndex = state.coletaFixaBloqData.findIndex((u) => u.id == itemId)
		state.coletaFixaBloqData.splice(coletaFixaBloqIndex, 1)
	},
}
