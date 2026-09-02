import axios from "@/axios.js"

export default {
	destroyColetaNf({ commit }, ColetaNFId) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/coleta-nf/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { id: ColetaNFId }
				})
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	}
}
