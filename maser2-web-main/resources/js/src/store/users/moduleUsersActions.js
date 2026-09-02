import axios from "@/axios.js"

export default {
	indexUsers({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/users`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_USERS', response.data.users)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showUsers({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/users/${id}`, {
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
	editUsers({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/users/${id}/edit`, {
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
	destroyUsers({ commit }, UsersId) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/users/destroy`, {
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
