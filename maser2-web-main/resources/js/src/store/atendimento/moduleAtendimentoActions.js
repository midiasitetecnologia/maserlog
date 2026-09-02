import axios from "@/axios.js"

export default {	
	getBuscarVeiculos({ commit }, filtros) {
		return new Promise((resolve, reject) => {			
			axios
				.get(`api/controle/RetornarVeiculosFrota`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {						
						cliente_id: filtros.cliente_id,
						cod_tipo_veiculo: filtros.cod_tipo_veiculo,
						sis_carga: filtros.sisCargaSelected.value,
						hr_prev_coleta: filtros.hora_coleta,
						com_motorista: filtros.comMotorista == true ? 'S' : 'N',
						com_carga: filtros.comCarga == true ? 'S' : 'N'						
					}
				})
				.then(response => {
					commit('SET_BUSCAR_VEICULOS', response.data.dados.veiculos)
					resolve(response)
				})
				.catch((error) => { reject(error) })
		})
	}
}
