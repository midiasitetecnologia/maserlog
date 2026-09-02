import axios from "@/axios.js"

export default {
	indexTipoVeiculo({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/tipo-veiculo`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_TIPO_VEICULO', response.data.tipoVeiculo)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showTipoVeiculo({ }, codigo) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/tipo-veiculo/${codigo}`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				}
			})
				.then((response) => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	editTipoVeiculo({ }, codigo) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/tipo-veiculo/${codigo}/edit`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				}
			})
				.then((response) => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	destroyTipoVeiculo({ commit }, tipoVeiculoCodigo) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/tipo-veiculo/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { codigo: tipoVeiculoCodigo }
				})
				.then(response => {

					if (response.data.status == true) {
						commit('REMOVE_RECORD', tipoVeiculoCodigo)
					}

					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	},
	lerTipoVeiculo({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/lerTipoVeiculo`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_TIPO_VEICULO_PESQ', response.data.tipoVeiculo)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
}
