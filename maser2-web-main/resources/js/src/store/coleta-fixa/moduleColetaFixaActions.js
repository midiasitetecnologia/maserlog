import axios from "@/axios.js"

export default {
	indexColetaFixa({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/coleta-fixa`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						ativo: payload.contratosAtivos == true ? 'S' : null
					}
				})
				.then(response => {
					commit('SET_COLETA_FIXA', response.data.coletaFixa)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showColetaFixa({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/coleta-fixa/${id}`, {
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
	editColetaFixa({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/coleta-fixa/${id}/edit`, {
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
	destroyColetaFixa({ commit }, UsersId) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/coleta-fixa/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { id: UsersId }
				})
				.then(response => {

					if (response.data.status == true) {
						commit('REMOVE_RECORD', UsersId)
					}

					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	}
}
