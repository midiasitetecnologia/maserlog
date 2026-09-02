import axios from "@/axios.js"

export default {
	getDataAtual({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/getDataAtual`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {					
					commit('SET_DATA_ATUAL', response.data.dataAtual)
					commit('SET_DATAHORA_ATUAL', response.data.dataHoraAtual)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	}
}
