import axios from "@/axios.js"

export default {
	indexColetaFixaBloq({ commit }, coletaFixaId) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/coleta-fixa-bloq?coleta_fixa_id=${coletaFixaId}`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_COLETA_FIXA_BLOQ', response.data.coletaFixaBloq)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showColetaFixaBloq({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/coleta-fixa-bloq/${id}`, {
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
	editColetaFixaBloq({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/coleta-fixa-bloq/${id}/edit`, {
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
	destroyColetaFixaBloq({ commit }, coletaFixaBloqId) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/coleta-fixa-bloq/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { id: coletaFixaBloqId }
				})
				.then(response => {

					if (response.data.status == true) {
						commit('REMOVE_RECORD', coletaFixaBloqId)
					}

					resolve(response)

				})
				.catch((error) => { reject(error) })

		})
	}
}
