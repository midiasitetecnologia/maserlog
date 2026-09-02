import axios from "@/axios.js"

export default {
	indexColeta({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.get(`api/coleta`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				},
				params: {
					cod_empresa: payload.cod_empresa,
					cliente_id: payload.cliente_id,
					placa: payload.placa,
					periodo: payload.periodo,
					filtro_ini: payload.filtro_ini,
					filtro_fim: payload.filtro_fim,
					nro_nf: payload.nro_nf
				}
			})
				.then(response => {
					commit('SET_COLETA', response.data.coleta)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	showColeta({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/coleta/${id}`, {
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
	editColeta({ }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`/api/coleta/${id}/edit`, {
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
	indexColetaWeb({ commit }) {
		return new Promise((resolve, reject) => {
			axios.get(`api/coletaWeb`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				}
			})
				.then(response => {
					commit('SET_COLETA_WEB', response.data.coleta)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	destroyColeta({ commit }, colId) {
		return new Promise((resolve, reject) => {
			axios
				.delete(`api/coleta/destroy`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					data: { id: colId }
				})
				.then(response => {

					if (response.data.status == true) {
						commit('REMOVE_RECORD', colId)
					}

					resolve(response)
				})
				.catch((error) => { reject(error) })

		})
	},
	getNotasFiscais({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`api/getNotasFiscais`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				},
				params: {
					coleta_id: id
				}
			})
				.then(response => {
					commit('SET_NOTAS_FISCAIS', response.data.notas_fiscais)
					commit('SET_PERMISSAO_NOTA_FISCAL', response.data.permissao)
					commit('SET_DADOS_COLETA_NOTA_FISCAL', response.data.dados_coleta_nf)
					commit('SET_URL_IMG_RECIBOS', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getColetaLog({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`api/getColetaLog`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				},
				params: {
					coleta_id: id
				}
			})
				.then(response => {
					commit('SET_COLETA_LOG', response.data.coletasLog)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getColetaPos({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios.get(`api/getColetaPos`, {
				headers: {
					Authorization:
						"Bearer " + localStorage.getItem("apiToken")
				},
				params: {
					coleta_id: id
				}
			})
				.then(response => {
					commit('SET_COLETA_POS', response.data.coletasPos)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarInstrucoesColeta({ commit }, id) {
		return new Promise((resolve, reject) => {
			axios.post(`api/controle/RetornarInstrucoesColeta`,
				{
					coleta_id: id
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					commit('SET_COLETA_INSTR', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarTotaisKmTempoCliente({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarTotaisKmTempoCliente`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						data_ini: payload.data_ini,
						data_fim: payload.data_fim,
					}
				})
				.then(response => {
					commit('SET_RESUMO_KM_TEMPO_CLIENTE', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarTotaisKmTempoVeiculo({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarTotaisKmTempoVeiculo`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						data_ini: payload.data_ini,
						data_fim: payload.data_fim,
					}
				})
				.then(response => {
					commit('SET_RESUMO_KM_TEMPO_VEICULO', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarTotaisKmTempoTipoVeiculo({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarTotaisKmTempoTipoVeiculo`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						data_ini: payload.data_ini,
						data_fim: payload.data_fim,
					}
				})
				.then(response => {
					commit('SET_RESUMO_KM_TEMPO_TIPO_VEICULO', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarTotaisKmTempoMotorista({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/RetornarTotaisKmTempoMotorista`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						data_ini: payload.data_ini,
						data_fim: payload.data_fim,
					}
				})
				.then(response => {
					commit('SET_RESUMO_KM_TEMPO_MOTORISTA', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
}
