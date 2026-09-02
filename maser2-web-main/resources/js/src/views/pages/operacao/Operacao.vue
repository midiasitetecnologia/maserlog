<template>
	<div>
		<div class="vx-row" v-show="(pnOperacaoVeiculoCarga == false && pnOperacaoVeiculoRota == false)">
			<div class="vx-col w-full">
				<vx-card>
					<div class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex">
							<label class="mr-3 font-semibold">Operação Pátio</label>
							<vs-switch class="mr-2" color="primary" v-model="comMotorista" />
							<label class="mr-2">Com motorista</label>
							<vs-switch class="mr-2" color="primary" v-model="comCarga" />
							<label class="mr-2">Com carga</label>
						</div>

						<div class="flex flex-wrap-reverse items-center">
							<feather-icon
								@click="refresh"
								icon="RotateCwIcon"
								svgClasses="h-4 w-4"
								class="cursor-pointer mr-4"
							/>
						</div>
					</div>
				</vx-card>
			</div>
		</div>

		<div class="vx-row" v-show="(pnOperacaoVeiculoCarga == false && pnOperacaoVeiculoRota == false)">
			<div class="vx-col w-full">
				<div id="operacao-css">
					<vs-table
						ref="table"
						:noDataText="veiculosFrotaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						:max-items="itemsPerPage"
						:data="veiculosFrotaData"
					>
						<template slot="thead">
							<vs-th sort-key="placa">Placa</vs-th>
							<vs-th sort-key="nome_motorista">Motorista</vs-th>
							<vs-th sort-key="ignicao"></vs-th>
							<vs-th sort-key="local_veiculo">Localização</vs-th>
							<vs-th></vs-th>
							<vs-th sort-key="ocup_veiculo">Ocupação</vs-th>
							<vs-th></vs-th>
							<vs-th sort-key="qtde_coletas">Solic.</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">{{data[indextr].placa}}</vs-td>

								<vs-td class="whitespace-no-wrap">{{data[indextr].nome_motorista | truncate(15)}}</vs-td>

								<vs-td align="center">
									<vx-tooltip
										:text="data[indextr].ignicao == 'S' ? 'Veículo ligado' : 'Veículo desligado'"
										position="top"
									>
										<feather-icon
											icon="TruckIcon"
											:svgClasses="data[indextr].ignicao == 'S' ? 'w-5 h-5 text-success' : 'w-5 h-5 text-danger'"
											class="ml-2"
										/>
									</vx-tooltip>
								</vs-td>

								<vs-td class="whitespace-no-wrap">{{data[indextr].local_veiculo}}</vs-td>

								<vs-td>
									<feather-icon
										v-if="((data[indextr].geo_lat != 0) & 
										   (data[indextr].geo_lat != null) & 
										   (data[indextr].geo_lng != 0) & 
										   (data[indextr].geo_lng != null))"
										icon="MapPinIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										@click="exibirLocalizacao(data[indextr])"
									/>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div :class="{'flex items-center': true, 'text-center': true}">
										<span class="mr-2">{{data[indextr].ocup_veiculo}}%</span>
										<vs-progress
											:percent="data[indextr].ocup_veiculo"
											:color="retCorPercOcup(data[indextr].ocup_veiculo)"
										></vs-progress>
									</div>
								</vs-td>

								<vs-td align="center">
									<div v-if="data[indextr].img_carga != ''">
										<vx-tooltip text="Clique para visualizar a foto da carga" position="top">
											<feather-icon
												icon="ImageIcon"
												svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
												@click="exibirFotoCarga(data[indextr])"
											></feather-icon>
										</vx-tooltip>
									</div>
								</vs-td>

								<vs-td>
									<div v-if="data[indextr].qtde_coletas > 0">
										<div :class="{'flex items-center': true, 'text-center': false}">
											<vx-tooltip
												:text="data[indextr].qtde_solic_and > 0 ? 'Solicitações em andamento: ' + data[indextr].qtde_solic_and : 'Veículo com solicitações na fila: ' + data[indextr].qtde_coletas"
												position="top"
											>
												<feather-icon
													class="mr-2"
													:icon="'ClipboardIcon'"
													:svgClasses="data[indextr].qtde_solic_and != '' ? 'w-6 h-6 text-warning hover:text-success stroke-current cursor-pointer' : 'w-6 h-6 hover:text-success stroke-current cursor-pointer'"
													@click="exibirColetas(data[indextr])"
												></feather-icon>
											</vx-tooltip>
											<span>{{data[indextr].qtde_coletas}}</span>
										</div>
									</div>
									<div v-else>
										<vx-tooltip :text="'Veículo sem solicitações'" position="top">
											<feather-icon
												style="color:rgb(220, 220, 220)"
												:icon="'ClipboardIcon'"
												:svgClasses="'w-6 h-6'"
											></feather-icon>
										</vx-tooltip>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<vs-button
										class="mr-2"
										type="border"
										size="small"
										@click="operacaoVeiculoCarga(data[indextr].placa)"
									>CARGA</vs-button>
									<vs-button
										v-if="data[indextr].qtde_coletas > 0"
										type="border"
										size="small"
										@click="operacaoVeiculoRota(data[indextr].placa)"
									>ROTA</vs-button>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</div>
			</div>

			<vs-popup
				class="holamundo width60"
				:title="'Solicitações do veículo: ' + placa_veiculo + ' (' + peso_total + ')'"
				:active.sync="popupActive"
			>
				<vs-table stripe :data="coletasVei">
					<template slot="thead">
						<vs-th>Número</vs-th>
						<vs-th>Cliente</vs-th>
						<vs-th>Previsão</vs-th>
						<vs-th>Local</vs-th>
						<vs-th>Peso</vs-th>
						<vs-th>Status</vs-th>
					</template>
					<template slot-scope="{data}">
						<vs-tr :key="indextr" v-for="(tr, indextr) in data">
							<vs-td class="whitespace-no-wrap">
								<span v-if="data[indextr].numero != null">{{data[indextr].numero}}</span>
								<span v-else>ID: {{data[indextr].coleta_id}}</span>
								<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
							</vs-td>

							<vs-td>{{ data[indextr].cliente }}</vs-td>

							<vs-td class="whitespace-no-wrap">
								<!-- Só vamos mostrar a previsão de coleta se ela ainda não foi efetivada,
								como este campo tem o valor das duas situação, só ficará a data prevista de acordo com o status.-->
								<div
									class="tooltip"
									v-if="data[indextr].dt_efet_coleta == null && data[indextr].dt_prev_coleta != null"
								>
									<span
										v-if="data[indextr].dt_prev_coleta == data[indextr].data_cad"
									>{{data[indextr].hr_prev_coleta | hora_min }}</span>
									<span
										v-else
									>{{data[indextr].dt_prev_coleta | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min }}</span>
									<span class="tooltiptext" style="font-size: 12px">Previsão de coleta</span>
								</div>
								<div
									class="tooltip"
									v-if="data[indextr].dt_efet_coleta != null && data[indextr].dt_prev_entrega != null"
								>
									<span
										v-if="data[indextr].dt_prev_entrega == data[indextr].data_cad"
									>{{data[indextr].hr_prev_entrega | hora_min }}</span>
									<span
										v-else
									>{{data[indextr].dt_prev_entrega | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</span>
									<span class="tooltiptext" style="font-size: 12px">Previsão de entrega</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div>
									<span class="mr-1 font-semibold">[C]</span>
									<span style="font-size: 12px; color:gray">{{data[indextr].local_coleta | truncate(25)}}</span>
								</div>
								<div>
									<span class="mr-1 font-semibold">[E]</span>
									<span style="font-size: 12px; color:gray">{{data[indextr].local_entrega | truncate(25)}}</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">{{ data[indextr].peso }}</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div class="flex items-center text-center">
									<vs-avatar
										size="small"
										:color="corStatus(data[indextr].status)"
										:text="inicialStatus(data[indextr].status)"
									/>
									<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
								</div>
							</vs-td>
						</vs-tr>
					</template>
				</vs-table>
			</vs-popup>

			<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
				<div align="center">
					<img :src="foto" height="800px" />
				</div>
			</vs-popup>

			<mapa-pop-up />
		</div>

		<operacao-veiculo-carga v-if="(pnOperacaoVeiculoCarga == true)" />
		<operacao-veiculo-rota v-if="(pnOperacaoVeiculoRota == true)" />
		<notas-fiscais-pop-up />
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";

import MapaPopUp from "@/components/rgsoft/MapaPopUp.vue";
import OperacaoVeiculoCarga from "./OperacaoVeiculoCarga.vue";
import OperacaoVeiculoRota from "./OperacaoVeiculoRota.vue";
import NotasFiscaisPopUp from "@/components/rgsoft/NotasFiscaisPopUp.vue";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	mixins: [controleMixins, procsMixins],
	components: {
		MapaPopUp,
		OperacaoVeiculoCarga,
		OperacaoVeiculoRota,
		NotasFiscaisPopUp,
		ColFixa
	},
	data() {
		return {
			comMotorista: false,
			comCarga: true,

			placa_veiculo: null,
			peso_total: null,
			coletasVei: [],
			popupActive: false
		};
	},
	async created() {
		const payload = {
			comMotorista: this.comMotorista,
			comCarga: this.comCarga
		};
		await this.$store.commit(
			"controle/SET_VEICULOS_FROTA_FILTROS",
			payload
		);
		await this.getVeiculosFrota(payload);
	},
	mounted() {
		this.wasSidebarOpen = this.$store.state.reduceButton;
		this.$store.commit("TOGGLE_REDUCE_BUTTON", true);
		this.isMounted = true;
	},
	beforeDestroy() {
		if (!this.wasSidebarOpen) {
			this.$store.commit("TOGGLE_REDUCE_BUTTON", false);
		}
		if (this.pnOperacaoVeiculoCarga == true) {
			this.$store.commit("operacao/EXIBIR_PAINEL_OPERACAO_VEICULO_CARGA");
		}
		if (this.pnOperacaoVeiculoRota == true) {
			this.$store.commit("operacao/EXIBIR_PAINEL_OPERACAO_VEICULO_ROTA");
		}
	},
	computed: {
		veiculosFrotaData() {
			return this.$store.state.controle.veiculosFrotaData;
		},
		queriedItems() {
			return this.$refs.table
				? this.$refs.table.queriedResults.length
				: this.veiculosFrotaData.length;
		},
		pnOperacaoVeiculoCarga() {
			return this.$store.state.operacao.operacaoVeiculoCarga;
		},
		pnOperacaoVeiculoRota() {
			return this.$store.state.operacao.operacaoVeiculoRota;
		}
	},
	watch: {
		comMotorista: function(newValue, oldValue) {
			this.refresh();
		},
		comCarga: function(newValue, oldValue) {
			this.refresh();
		}
	},
	methods: {
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				comMotorista: this.comMotorista,
				comCarga: this.comCarga
			};
			await this.$store.commit(
				"controle/SET_VEICULOS_FROTA_FILTROS",
				payload
			);

			await this.getVeiculosFrota(payload);
			await this.$vs.loading.close();
		},
		exibirLocalizacao(dados) {
			this.$store.commit("EXIBIR_MAPA_POPUP", dados);
		},
		exibirColetas(coletas) {
			this.placa_veiculo = coletas.placa;
			this.peso_total = coletas.peso_total_coletas;
			this.coletasVei = coletas.coletas_veiculo;
			this.popupActive = !this.popupActive;
		},
		exibirFotoCarga(dados) {
			let titulo = "Carga do veículo: " + dados.placa;
			this.exibirFoto(dados.url_imagem, titulo);
		},
		async operacaoVeiculoCarga(placa) {
			await this.$vs.loading({ scale: 0.5 });

			const payloadFiltros = {
				comMotorista: this.comMotorista,
				comCarga: this.comCarga
			};
			await this.$store.commit(
				"controle/SET_VEICULOS_FROTA_FILTROS",
				payloadFiltros
			);

			await this.retornarDadosVeiculoCarga(placa);
			await this.getDataAtual();

			var moment = require("moment");
			var hora_atual = moment(this.dataHoraAtual).format("HH:mm");

			const payload = {
				placa: placa,
				local_saida_descr: "Veículo",
				local_saida: "V",
				hora_saida: hora_atual
			};
			await this.retornarColetasVeiculoCarga(payload);
			await this.$store.commit(
				"controle/SET_COL_VEICULO_CARGA_FILTROS",
				payload
			);

			await this.$store.commit(
				"operacao/EXIBIR_PAINEL_OPERACAO_VEICULO_CARGA"
			);
			await this.$vs.loading.close();
		},
		async operacaoVeiculoRota(placa) {
			await this.$vs.loading({ scale: 0.5 });

			const payloadFiltros = {
				comMotorista: this.comMotorista,
				comCarga: this.comCarga
			};
			await this.$store.commit(
				"controle/SET_VEICULOS_FROTA_FILTROS",
				payloadFiltros
			);

			await this.retornarDadosVeiculoCarga(placa);
			await this.getDataAtual();

			var moment = require("moment");
			var hora_atual = moment(this.dataHoraAtual).format("HH:mm");

			const payload = {
				placa: placa,
				local_saida_descr: "Veículo",
				local_saida: "V",
				hora_saida: hora_atual
			};
			await this.retornarColetasVeiculoCarga(payload);
			await this.$store.commit(
				"controle/SET_COL_VEICULO_CARGA_FILTROS",
				payload
			);

			await this.$store.commit(
				"operacao/EXIBIR_PAINEL_OPERACAO_VEICULO_ROTA"
			);
			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss">
#operacao-css {
	.vs-con-table {
		.vs-table {
			border-collapse: separate;
			border-top: none;
			border-spacing: 0 0.7rem;
			tr {
				box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
				td {
					padding: 20px;
					&:first-child {
						border-top-left-radius: 0.5rem;
						border-bottom-left-radius: 0.5rem;
					}
					&:last-child {
						border-top-right-radius: 0.5rem;
						border-bottom-right-radius: 0.5rem;
					}
				}
			}
		}
	}
}

.con-vs-popup.width60 .vs-popup {
	width: 60%;
	/* height: 100%; */
}

.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}

.tooltip {
	position: relative;
	display: inline-block;
}

.tooltip .tooltiptext {
	visibility: hidden;
	width: 150px;
	background-color: black;
	color: #fff;
	text-align: center;
	border-radius: 6px;
	padding: 5px 0;
	position: absolute;
	z-index: 1;
	bottom: 125%;
	left: 50%;
	margin-left: -60px;
	opacity: 0;
	transition: opacity 0.3s;
}

.tooltip .tooltiptext::after {
	content: "";
	position: absolute;
	top: 100%;
	left: 50%;
	margin-left: -5px;
	border-width: 5px;
	border-style: solid;
	border-color: #555 transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
	visibility: visible;
	opacity: 1;
}
</style>