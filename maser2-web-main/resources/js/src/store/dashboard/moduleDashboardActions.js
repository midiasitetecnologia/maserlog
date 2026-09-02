import axios from "@/axios.js"

export default {
	retornarMsgWisdomUser({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarMsgWisdomUser`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_MSG_WISDOM_USER', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarTarefasHome({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarTarefasHome`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_TAREFAS_HOME', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},	
	retornarColetasEmissaoNotas({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarColetasEmissaoNotas`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_COLETAS_EMISSAO_NOTAS', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarClientesColetasCadIncomp({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarClientesColetasCadIncomp`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_CORRIGIR_CAD', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarColetasMultiDestinosRealizadas({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarColetasMultiDestinosRealizadas`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_COLETAS_MD_REALIZADAS', response.data.dados.coletas)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarEntregasNaoRealizadasReentrega({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarEntregasNaoRealizadasReentrega`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_ENTREGAS_NAO_REALIZADAS_REENTREGA', response.data.dados.coletas)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarResumoColetasHome({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarResumoColetasHome`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_RESUMO_COLETAS_HOME', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarResumoFrotaHome({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarResumoFrotaHome`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_RESUMO_FROTA_HOME', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarResumoKmTempoHome({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarResumoKmTempoHome`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_RESUMO_KM_TEMPO_HOME', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarMotoristasDisponiveis({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarMotoristasDisponiveis`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_MOTORISTAS_DISPONIVEIS', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getSolicitacoes({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/getSolicitacoes`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_SOLIC', response.data.solic)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	}
}
