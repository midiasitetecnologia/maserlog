export default {
	SET_VEICULO(state, veiculo) {
		state.veiculoData = veiculo
	},
	SET_URL_IMG(state, url_img) {
		state.veiculoUrlImgData = url_img
	},
	SET_VEICULO_PESQ(state, veiculo) {
		state.veiculoPesqData = veiculo
	},
	REMOVE_RECORD(state, itemId) {
		const veiculoIndex = state.veiculoData.findIndex((u) => u.placa == itemId)
		state.veiculoData.splice(veiculoIndex, 1)
	},
}
