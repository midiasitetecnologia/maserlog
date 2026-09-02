import axios from "@/axios.js"

export default {
	indexCliente({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/cliente`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						comUsuarios: payload.comUsuarios,
						semGeoLocalizacao: payload.semGeoLocalizacao
					}
				})
				.then(response => {
					commit('SET_CLIENTE', response.data.cliente)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getUsersCliente({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/getUsersCliente`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						id: id
					}
				})
				.then(response => {
					commit('SET_USERS_CLIENTE', response.data.usersCliente)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showCliente({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/cliente/${id}`, {
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
	lerClienteFromUser({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/lerClienteFromUser`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						id: id
					}
				})
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
}
