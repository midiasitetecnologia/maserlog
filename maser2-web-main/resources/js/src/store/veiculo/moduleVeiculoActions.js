import axios from "@/axios.js"

export default {
	indexVeiculo({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/veiculo`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_VEICULO', response.data.veiculo)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showVeiculo({ commit }, placa) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/veiculo/${placa}`, {
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
	editVeiculo({ commit }, placa) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/veiculo/${placa}/edit`, {
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
	destroyVeiculo({ commit }, VeiculoPlaca) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/veiculo/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { placa: VeiculoPlaca }
				})
				.then(response => {

					if (response.data.status == true) {
						commit('REMOVE_RECORD', VeiculoPlaca)
					}

					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	},
	lerVeiculo({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/lerVeiculo`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_VEICULO_PESQ', response.data.veiculo)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	desvincularMotoristaVeiculo({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/DesvincularMotoristaVeiculo`,
				{
					placa: payload.placa,
					motorista_id: payload.motorista_id,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
}
