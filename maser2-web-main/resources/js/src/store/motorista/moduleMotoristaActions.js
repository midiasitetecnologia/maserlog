import axios from "@/axios.js"

export default {
	indexMotorista({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/motorista`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						ativo: payload.ativos == true ? 'S' : null
					}
				})
				.then(response => {
					commit('SET_MOTORISTA', response.data.motorista)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showMotorista({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/motorista/${id}`, {
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
	editMotorista({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/motorista/${id}/edit`, {
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
	destroyMotorista({ commit }, motoId) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/motorista/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { id: motoId }
				})
				.then(response => {
					commit('REMOVE_RECORD', motoId)
					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	}
}
