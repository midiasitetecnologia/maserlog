<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="veiculosFrotaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					search
					:max-items="veiculosFrotaData.length"
					stripe
					:data="veiculosFrotaData"
				>
					<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex mb-1">
							<label class="font-semibold mr-3">Veículos</label>
							<vs-switch class="mr-2" color="primary" v-model="comMotorista" />
							<label class="mr-2">Com motorista</label>
							<vs-switch class="mr-2" color="primary" v-model="comCarga" />
							<label class="mr-2">Com carga</label>
							<vx-tooltip
								v-if="veiculosFrotaData.length > 0"
								text="Localizar veículos no mapa"
								position="top"
							>
								<feather-icon
									class="ml-4"
									icon="MapIcon"
									svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
									@click="exibirLocalizacao(veiculosFrotaData)"
								></feather-icon>
							</vx-tooltip>
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

					<template slot="thead">
						<vs-th sort-key="placa">Placa</vs-th>
						<vs-th sort-key="nome_motorista">Motorista</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="tipo_veiculo">Tipo de veículo</vs-th>
						<vs-th sort-key="ignicao"></vs-th>
						<vs-th sort-key="local_veiculo">Localização</vs-th>
						<vs-th></vs-th>
						<!-- Localizar no mapa -->
						<vs-th sort-key="ocup_veiculo">Ocupação</vs-th>
						<vs-th></vs-th>
						<!-- Imagem -->
						<vs-th sort-key="qtde_coletas">Solic.</vs-th>
						<!-- Botão CARGA -->
						<vs-th></vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
							<vs-td class="whitespace-no-wrap" :data="data[indextr].placa">{{data[indextr].placa}}</vs-td>

							<vs-td
								class="whitespace-no-wrap"
								:data="data[indextr].nome_motorista"
							>{{data[indextr].nome_motorista | truncate(15)}}</vs-td>

							<vs-td
								class="whitespace-no-wrap"
								:data="data[indextr].tipo_veiculo"
							>{{data[indextr].tipo_veiculo}}</vs-td>

							<vs-td :data="data[indextr].ignicao" align="center">
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

							<vs-td
								class="whitespace-no-wrap"
								:data="data[indextr].local_veiculo"
							>{{data[indextr].local_veiculo}}</vs-td>

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

							<vs-td class="whitespace-no-wrap" :data="data[indextr].ocup_veiculo">
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
												:svgClasses="data[indextr].qtde_solic_and > 0 ? 'w-6 h-6 text-warning hover:text-success stroke-current cursor-pointer' : 'w-6 h-6 hover:text-success stroke-current cursor-pointer'"
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

							<vs-td align="center">
								<vs-button type="border" size="small" @click="veiculoCarga(data[indextr].placa)">CARGA</vs-button>
							</vs-td>
						</vs-tr>
					</template>
				</vs-table>
			</vx-card>
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
</template>

<script>
import MapaPopUp from "@/components/rgsoft/MapaPopUp.vue";
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "veiculos-frota",
	components: { MapaPopUp, ColFixa },
	mixins: [controleMixins, procsMixins],
	data() {
		return {
			comMotorista: true,
			comCarga: null,
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
		this.isMounted = true;
	},
	computed: {
		veiculosFrotaData() {
			return this.$store.state.controle.veiculosFrotaData;
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
		async veiculoCarga(placa) {
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
			await this.retornarEntregasPendentesCarga(placa);
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

			await this.$store.commit("controle/EXIBIR_PAINEL_VEICULO_CARGA");
			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss">
.con-vs-popup.width60 .vs-popup {
	width: 60%;
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