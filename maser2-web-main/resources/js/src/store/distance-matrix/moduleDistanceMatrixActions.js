import axios from "@/axios.js"

export default {
	indexDistanceMatrix({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/distance-matrix`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_DISTANCE_MATRIX', response.data.distanceMatrix)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	editDistanceMatrix({ }, id) {
		return new Promise((resolve, reject) => {
			axios
				.get(`/api/distance-matrix/${id}/edit`, {
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
	destroyDistanceMatrix({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/distance-matrix/destroy`, {
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
