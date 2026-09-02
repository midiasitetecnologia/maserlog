import axios from "@/axios.js"

export default {
	countColetasPendentes({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/countColetasPendentes`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						hoje: payload.somenteHoje,
						tarde: payload.antesTarde,
					}
				})
				.then(response => {
					commit('COUNT_COLETAS_PENDENTES', response.data.countColetasPendentes)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	countColetasAndamento({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/countColetasAndamento`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('COUNT_COLETAS_ANDAMENTO', response.data.countColetasAndamento)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	countEntregasPendentes({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/countEntregasPendentes`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('COUNT_ENTREGAS_PENDENTES', response.data.countEntregasPendentes)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	countEntregasAndamento({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/countEntregasAndamento`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('COUNT_ENTREGAS_ANDAMENTO', response.data.countEntregasAndamento)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	countSolicitacoesFinalizadas({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/countSolicitacoesFinalizadas`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('COUNT_SOLICITACOES_FINALIZADAS', response.data.countSolicitacoesFinalizadas)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getColetasPendentes({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/getColetasPendentes`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						hoje: payload.somenteHoje,
						tarde: payload.antesTarde,
					}
				})
				.then(response => {
					commit('SET_COLETAS_PENDENTES', response.data.coletasPendentes)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarVeiculosColeta({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/RetornarVeiculosColeta`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						coleta_id: payload.coleta_id,
						com_motorista: payload.com_motorista
					}
				})
				.then(response => {
					commit('SET_RETORNAR_VEICULOS_COLETA', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	definirVeiculoColeta({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/DefinirVeiculoColeta`,
				{
					coleta_id: payload.coleta_id,
					placa: payload.placa,
					autorizar: payload.autorizar,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getColetasAndamento({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/getColetasAndamento`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_COLETAS_ANDAMENTO', response.data.coletasAndamento)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getEntregasPendentes({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/getEntregasPendentes`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_ENTREGAS_PENDENTES', response.data.entregasPendentes)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	definirVeiculoEntrega({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/DefinirVeiculoEntrega`,
				{
					coleta_id: payload.coleta_id,
					placa: payload.placa,
					autorizar: payload.autorizar,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	enviarInstrucaoColeta({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/EnviarInstrucaoColeta`,
				{
					coleta_id: payload.coleta_id,
					instrucao: payload.instrucao,
					txt_instrucao: payload.txt_instrucao,
					placa_baldeacao: payload.placa_baldeacao,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getEntregasAndamento({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/getEntregasAndamento`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_ENTREGAS_ANDAMENTO', response.data.entregasAndamento)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getSolicitacoesFinalizadas({ commit }) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/getSolicitacoesFinalizadas`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				})
				.then(response => {
					commit('SET_SOLICITACOES_FINALIZADAS', response.data.solicitacoesFinalizadas)
					commit('SET_URL_IMG', response.data.url_img)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	getVeiculosFrota({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/RetornarVeiculosFrota`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						com_motorista: payload.comMotorista == true ? 'S' : 'N',
						com_carga: payload.comCarga == true ? 'S' : 'N'
					}
				})
				.then(response => {
					commit('SET_VEICULOS_FROTA', response.data.dados.veiculos)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarDadosVeiculoCarga({ commit }, placa) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/RetornarDadosVeiculoCarga`,
				{
					placa: placa
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					commit('SET_RETORNAR_DADOS_VEICULO_CARGA', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarEntregasPendentesCarga({ commit }, placa) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/RetornarEntregasPendentesCarga`,
				{
					placa: placa
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					commit('SET_RETORNAR_ENTREGAS_PEND_CARGA', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarColetasVeiculoCarga({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/RetornarColetasVeiculoCarga`,
				{
					placa: payload.placa,
					local_saida: payload.local_saida,
					hora_saida: payload.hora_saida,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					commit('SET_RETORNAR_COLETAS_VEICULO_CARGA', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarColetasResumoDia({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/RetornarColetasResumoDia`,
				{
					data_prevista: payload.data_prevista,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					commit('SET_RETORNAR_COLETAS_RESUMO_DIA', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	definirMotoristaPrevisto({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/DefinirMotoristaPrevisto`,
				{
					coleta_id: payload.coleta_id,
					motorista_id: payload.motorista_id,
					etapa: payload.etapa,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	desvincularVeiculoSolicitacao({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/DesvincularVeiculoSolicitacao`,
				{
					coleta_id: payload.coleta_id,
					placa: payload.placa
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	retornarVeiculosBaldeacao({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios
				.get(`api/controle/RetornarVeiculosBaldeacao`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						coleta_id: payload.coleta_id,
						com_motorista: payload.com_motorista
					}
				})
				.then(response => {
					commit('SET_VEICULOS_BALDEACAO', response.data.dados)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	executarBaldeacaoPatio({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/ExecutarBaldeacaoPatio`,
				{
					coleta_id: payload.coleta_id,
					placa_destino: payload.placa_destino,
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
	setarEntregaConsolidada({ commit }, payload) {
		return new Promise((resolve, reject) => {
			axios.post(
				`api/controle/SetarEntregaConsolidada`,
				{
					coleta_id: payload.coleta_id,
					consolidada: payload.consolidada == true ? 'S' : 'N',
				},
				{
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					}
				}
			)
				.then(response => {
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	},
}
