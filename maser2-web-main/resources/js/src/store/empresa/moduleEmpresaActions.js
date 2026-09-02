import axios from "@/axios.js"

export default {
	indexEmpresa({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/empresa`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_EMPRESA', response.data.empresa)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showEmpresa({ }, codigo) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/empresa/${codigo}`, {
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
	editEmpresa({ }, codigo) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/empresa/${codigo}/edit`, {
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
	destroyEmpresa({ commit }, empresaCodigo) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/empresa/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { codigo: empresaCodigo }
				})
				.then(response => {
					commit('REMOVE_RECORD', empresaCodigo)
					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	}
}
