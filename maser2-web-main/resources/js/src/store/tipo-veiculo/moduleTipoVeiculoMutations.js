export default {
	SET_TIPO_VEICULO(state, tipoVeiculo) {
		state.tipoVeiculoData = tipoVeiculo
	},
	SET_TIPO_VEICULO_PESQ(state, tipoVeiculo) {
		state.tipoVeiculoPesqData = tipoVeiculo
	},
	REMOVE_RECORD(state, itemId) {
		const tipoVeiculoIndex = state.tipoVeiculoData.findIndex((u) => u.codigo == itemId)
		state.tipoVeiculoData.splice(tipoVeiculoIndex, 1)
	},
}
