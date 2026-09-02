import axios from "@/axios.js"

export default {
	indexWisdom({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/wisdom`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_WISDOM', response.data.wisdom)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	editWisdom({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/wisdom/${id}/edit`, {
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
	destroyWisdom({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/wisdom/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { id: id }
				})
				.then(response => {
					if (response.data.status == true) {
						commit('REMOVE_RECORD', id)
					}
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	}
}
