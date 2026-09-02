import axios from "@/axios.js"

export default {
	indexLogPro({ commit }, filtros) {
		return new Promise((resolve, reject) => {			
			axios
				.get(`api/log-pro`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						evento: filtros.eventoSelected.value,
						period1: filtros.filtro_ini,
						period2: filtros.filtro_fim,
						status: filtros.statusSelected.value
					}
				})
				.then(response => {
					commit('SET_LOG_PRO', response.data.logPro)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	detalheLogPro({ commit }, proc_id) {
		return new Promise((resolve, reject) => {			
			axios
				.get(`api/log-pro-detalhe`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						proc_id: proc_id
					}
				})
				.then(response => {
					commit('SET_LOG_PRO_DETALHE', response.data.logProDetalhe)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	}
}
