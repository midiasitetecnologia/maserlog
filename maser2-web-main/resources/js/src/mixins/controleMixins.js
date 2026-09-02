const controleMixins = {
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false
		}
	},
	computed: {
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		},
		dataHoraAtual() {
			return this.$store.state.dataAtual.dataHoraAtual;
		},
		currentPage() {
			if (this.isMounted) {
				return this.$refs.table.currentx;
			}
			return 1;
		},
	},
	methods: {
		async getDataAtual() {
			await this.$store.dispatch("dataAtual/getDataAtual").catch(err => {
				console.error(err);
			});
		},
		async countColetasPendentes(payload) {
			await this.$store
				.dispatch("controle/countColetasPendentes", payload)
				.catch(err => {
					console.error(err);
				});
		},
		async countColetasAndamento() {
			await this.$store
				.dispatch("controle/countColetasAndamento")
				.catch(err => {
					console.error(err);
				});
		},
		async countEntregasPendentes() {
			await this.$store
				.dispatch("controle/countEntregasPendentes")
				.catch(err => {
					console.error(err);
				});
		},
		async countEntregasAndamento() {
			await this.$store
				.dispatch("controle/countEntregasAndamento")
				.catch(err => {
					console.error(err);
				});
		},
		async countSolicitacoesFinalizadas() {
			await this.$store
				.dispatch("controle/countSolicitacoesFinalizadas")
				.catch(err => {
					console.error(err);
				});
		},
		async getColetasPendentes(payload) {
			await this.$store
				.dispatch("controle/getColetasPendentes", payload)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarVeiculosColeta(payload) {
			await this.$store
				.dispatch("controle/retornarVeiculosColeta", payload)
				.catch(err => {
					console.error(err);
				});
		},
		async getEntregasPendentes() {
			await this.$store
				.dispatch("controle/getEntregasPendentes")
				.catch(err => {
					console.error(err);
				});
		},
		async getColetasAndamento() {
			await this.$store
				.dispatch("controle/getColetasAndamento")
				.catch(err => {
					console.error(err);
				});
		},
		async getEntregasAndamento() {
			await this.$store
				.dispatch("controle/getEntregasAndamento")
				.catch(err => {
					console.error(err);
				});
		},
		async getSolicitacoesFinalizadas() {
			await this.$store
				.dispatch("controle/getSolicitacoesFinalizadas")
				.catch(err => {
					console.error(err);
				});
		},
		async getVeiculosFrota(payload) {
			await this.$store
				.dispatch("controle/getVeiculosFrota", payload)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarDadosVeiculoCarga(placa) {
			await this.$store
				.dispatch("controle/retornarDadosVeiculoCarga", placa)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarEntregasPendentesCarga(placa) {
			await this.$store
				.dispatch("controle/retornarEntregasPendentesCarga", placa)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarColetasVeiculoCarga(payload) {
			await this.$store
				.dispatch("controle/retornarColetasVeiculoCarga", payload)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarColetasResumoDia(payload) {
			await this.$store
				.dispatch("controle/retornarColetasResumoDia", payload)
				.catch(err => {
					console.error(err);
				});
		},
		async retornarVeiculosBaldeacao(payload) {
			await this.$store
				.dispatch("controle/retornarVeiculosBaldeacao", payload)
				.catch(err => {
					console.error(err);
				});
		},
		url(id) {
			return "/coleta/" + id;
		},
		emAtraso(data, hora) {
			if (this.dataHoraAtual > data + " " + hora) {
				return "color:red;font-weight: 300;!important;";
			}
		},
		exibirDia(data) {
			//Se for igual a Hoje (Mesmo dia) não vamos exibir.
			if (data === this.dataAtual) return "";
			else return data;
		},
		calcTempoRestante(data, hora) {

			var retorno = '';

			if ((data != null) && (hora != null)) {

				var moment = require("moment");
				var hora_param = moment(data + ' ' + hora).format('HH:mm');
				var hora_atual = moment(this.dataHoraAtual).format('HH:mm');

				var horas = moment(data + ' ' + hora).diff(moment(this.dataHoraAtual), 'hours');
				var minutos = moment(data + ' ' + hora).diff(moment(this.dataHoraAtual), 'minutes');

				//A diferença em minutos da o total e não só de uma hora para outra. Desta forma descontamos todas horas cheias.
				if (minutos >= 60) {
					minutos = minutos - (60 * horas)
				}

				if ((data == this.dataAtual) && (hora_param > hora_atual)) {

					if (horas > 0) {

						retorno = horas + 'h';

						if (minutos > 0) {
							retorno = retorno + " " + minutos + ' min';
						}

					} else {
						if (minutos > 0) {
							retorno = minutos + ' min';
						}
					}
				}
			}

			return retorno;
		},
		dataPrevSaida(dados) {
			var moment = require("moment");
			var hr_prev_saida = "";
			var data_prev_saida = "";
			var data_hora_chegada;
			var hora_atual = moment(this.dataHoraAtual).format("YYYY-MM-DD HH:mm:ss"); //Utilizado para comparação
			var hora_atual_exibir = moment(this.dataHoraAtual).format("HH:mm"); //Utilizado para comparação

			if (dados.status == "C3" || dados.status == "C4") {
				data_hora_chegada = moment(
					dados.dt_efet_coleta + " " + dados.hr_cheg_coleta
				);

				if (dados.dur_prev_coleta != null) {
					// Formato hh:mm:ss
					// Adiciona as horas
					data_hora_chegada.add(
						parseInt(dados.dur_prev_coleta.substr(0, 2)),
						"hours"
					);
					// Adiciona os minutos
					data_hora_chegada.add(
						parseInt(dados.dur_prev_coleta.substr(3, 2)),
						"minutes"
					);

					data_prev_saida = data_hora_chegada.format("YYYY-MM-DD HH:mm:ss"); //Utilizado para comparação
					hr_prev_saida = data_hora_chegada.format("HH:mm"); //Utilizado na exibição					
				}
			} else {
				data_hora_chegada = moment(
					dados.dt_efet_entrega + " " + dados.hr_cheg_entrega
				);

				if (dados.dur_prev_entrega != null) {
					// Formato hh:mm:ss
					// Adiciona as horas
					data_hora_chegada.add(
						parseInt(dados.dur_prev_entrega.substr(0, 2)),
						"hours"
					);
					// Adiciona os minutos
					data_hora_chegada.add(
						parseInt(dados.dur_prev_entrega.substr(3, 2)),
						"minutes"
					);

					data_prev_saida = data_hora_chegada.format("YYYY-MM-DD HH:mm:ss"); //Utilizado para comparação
					hr_prev_saida = data_hora_chegada.format("HH:mm"); //Utilizado na exibição
				}
			}

			if (data_prev_saida < hora_atual) {
				return hora_atual_exibir;
			} else {
				return hr_prev_saida;
			}
		},
		montarEnderecoCompleto(endereco, bairro, cidade, uf, cep) {
			var enderecoCompleto;
			enderecoCompleto = null;

			enderecoCompleto = this.concatenaComSeparador(
				enderecoCompleto,
				"",
				endereco
			);

			enderecoCompleto = this.concatenaComSeparador(
				enderecoCompleto,
				" - ",
				bairro
			);

			enderecoCompleto = this.concatenaComSeparador(
				enderecoCompleto,
				", ",
				cidade
			);

			enderecoCompleto = this.concatenaComSeparador(
				enderecoCompleto,
				" - ",
				uf
			);

			enderecoCompleto = this.concatenaComSeparador(
				enderecoCompleto,
				", ",
				cep
			);

			return enderecoCompleto;
		},
		concatenaComSeparador(str1, sep, str2) {
			if (str1 != null) {
				return str1 + sep + str2;
			} else {
				return str2;
			}
		},
		montarHorario(horaIniMan, horaFimMan, horaIniTar, horaFimTar) {

			var horario;
			horario = null;

			if (horaIniMan != null) {
				horario = horaIniMan.slice(0, 5);
			}

			if (horaFimMan != null) {
				if (horario != null) {
					horario =
						horario + " às " + horaFimMan.slice(0, 5);
				} else {
					horario = horaFimMan.slice(0, 5);
				}
			}

			if (horaIniTar != null) {
				if (horario != null) {
					horario =
						horario + " e " + horaIniTar.slice(0, 5);
				} else {
					horario = horaIniTar.slice(0, 5);
				}
			}

			if (horaFimTar != null) {
				if (horario != null) {
					horario =
						horario + " às " + horaFimTar.slice(0, 5);
				} else {
					horario = horaFimTar.slice(0, 5);
				}
			}

			return horario;

		},
		instrucaoLabel(str) {
			if (str === "02") return "Manter carga no veículo";
			else if (str === "03") return "Descarregar no pavilhão";
			else if (str === "04") return "Ir para pavilhão";
			else if (str === "99") return "Digitar instrução";
		},
		async rotaPopUp(dados) {
			await this.$vs.loading({ scale: 0.5 });

			var placa_veiculo;
			if (dados.status.charAt(0) == 'C') {
				placa_veiculo = dados.placa_coleta
			} else {
				placa_veiculo = dados.placa_entrega
			}
			await this.retornarDadosVeiculoCarga(placa_veiculo);

			await this.getDataAtual();
			var moment = require("moment");
			var hora_atual = moment(this.dataHoraAtual).format("HH:mm");

			const payload = {
				placa: placa_veiculo,
				local_saida_descr: "Veículo",
				local_saida: "V",
				hora_saida: hora_atual
			};
			await this.retornarColetasVeiculoCarga(payload);

			await this.$store.commit("SET_COLETAS_ROTA_POPUP",
				JSON.parse(JSON.stringify(this.$store.state.controle.dadosColetasVeiculoCargaData.coletas)));

			const dadosRota = {
				placa: placa_veiculo,
				ignicao: this.$store.state.controle.dadosVeiculoCargaData.ignicao,
				geo_lat: this.$store.state.controle.dadosVeiculoCargaData.geo_lat,
				geo_lng: this.$store.state.controle.dadosVeiculoCargaData.geo_lng,
				coleta_id: dados.id,
				hora_atual: hora_atual
			};

			await this.$store.commit("EXIBIR_ROTA_POPUP", dadosRota);
			await this.$vs.loading.close();
		},
		async notasFiscais(coleta_id) {
			await this.$vs.loading({ scale: 0.5 });
			await this.getNotasFiscais(coleta_id);
			await this.$store.commit("EXIBIR_NOTAS_FISCAIS");
			await this.$vs.loading.close();
		},
		async romaneios(url, dados) {
			await this.$vs.loading({ scale: 0.5 });
						
			const payload = {
				img_rom_coleta:
					dados.img_rom_coleta != null
						? url + dados.img_rom_coleta
						: null,
				img_rom_entrega:
					dados.img_rom_entrega != null
						? url + dados.img_rom_entrega
						: null
			};

			await this.$store.commit("SET_FOTOS_ROMANEIOS", payload);
			await this.$store.commit("EXIBIR_ROMANEIOS");
			await this.$vs.loading.close();
		},
		async auditoria(coleta_id) {
			await this.$vs.loading({ scale: 0.5 });
			await this.getColetaLog(coleta_id);
			await this.getColetaPos(coleta_id);
			await this.retornarInstrucoesColeta(coleta_id)
			await this.$store.commit("EXIBIR_AUDITORIA");
			await this.$vs.loading.close();
		},
		async enviarInstrBaldeacao(coleta_id) {
			await this.$vs.loading({ scale: 0.5 });

			const payloadBaldeacao = {
				coleta_id: coleta_id,
				com_motorista: "S"
			};
			await this.retornarVeiculosBaldeacao(payloadBaldeacao);
			await this.$vs.loading.close();

			await this.$store.commit("controle/EXIBIR_VEICULOS_BALDEACAO");
		},
	}
}

export default controleMixins