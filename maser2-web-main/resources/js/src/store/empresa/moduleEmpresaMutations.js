export default {
	SET_EMPRESA(state, empresa) {
		state.empresaData = empresa
	},
	REMOVE_RECORD(state, itemId) {
		const empresaIndex = state.empresaData.findIndex((u) => u.codigo == itemId)
		state.empresaData.splice(empresaIndex, 1)
	},
}
