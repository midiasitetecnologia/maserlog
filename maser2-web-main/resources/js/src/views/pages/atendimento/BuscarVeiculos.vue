<template>
	<div>
		<vx-card>
			<div class="vx-row mb-2">
				<div class="vx-col md:w-5/6 w-full">
					<label class="text-sm opacity-75">Local de coleta</label>
					<v-select class="w-full" label="nome" :options="clienteData" clearable v-model="clienteCombo">
						<template v-slot:option="option">{{ option.nome_empresa }} - {{ option.nome }}</template>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div class="vx-row">
				<div class="vx-col md:w-5/6 w-full">
					<div class="vx-row">
						<div class="vx-col md:w-1/3 w-full">
							<label class="whitespace-no-wrap text-sm opacity-75">Tipo de veículo</label>
							<v-select
								class="w-full"
								label="descricao"
								:options="tipoVeiculo"
								clearable
								v-model="tipoVeiculoCombo"
							>
								<div slot="no-options">Opção não disponível</div>
							</v-select>
						</div>
						<div class="vx-col md:w-1/3 w-full">
							<label class="whitespace-no-wrap text-sm opacity-75">Sistema de carga</label>
							<v-select
								:options="sisCargaOptions"
								:clearable="false"
								v-model="filtros[0].sisCargaSelected"
							>
								<div slot="no-options">Opção não disponível</div>
							</v-select>
						</div>
						<div class="vx-col md:w-1/3 w-full">
							<label class="text-sm opacity-75">Hora coleta</label>
							<flat-pickr
								:config="configdateTimePickerTime"
								v-model="filtros[0].hora_coleta"
								class="w-full vs-inputx vs-input--input normal hasValue"
								style="border: 1px solid rgba(0, 0, 0, 0.2);"
							/>
						</div>
					</div>
				</div>
				<div class="vx-col md:w-1/6 w-full">
					<label class="text-sm opacity-0">Pesquisar</label>
					<vs-button
						color="primary"
						type="border"
						icon-pack="feather"
						icon="icon-search"
						@click="refresh"
					></vs-button>
				</div>
			</div>
		</vx-card>

		<br />

		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="buscarVeiculosData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="buscarVeiculosData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
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
								<vs-dropdown vs-trigger-click class="cursor-pointer mr-4 items-per-page-handler">
									<div
										class="p-2 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium whitespace-no-wrap"
									>
										<span
											class
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ buscarVeiculosData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : buscarVeiculosData.length }} de {{ queriedItems }}</span>
										<feather-icon icon="ChevronDownIcon" svgClasses="h-4 w-4" />
									</div>

									<vs-dropdown-menu>
										<vs-dropdown-item @click="itemsPerPage=10">
											<span>10</span>
										</vs-dropdown-item>
										<vs-dropdown-item @click="itemsPerPage=15">
											<span>15</span>
										</vs-dropdown-item>
										<vs-dropdown-item @click="itemsPerPage=20">
											<span>20</span>
										</vs-dropdown-item>
									</vs-dropdown-menu>
								</vs-dropdown>
							</div>
						</div>

						<template slot="thead">
							<vs-th class="whitespace-no-wrap" sort-key="placa">Placa</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="tipo_veiculo">Tipo de veículo</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="consumo">
								<span class="pl-2">NC</span>
							</vs-th>
							<vs-th class="whitespace-no-wrap">Sis.Carga</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="capacid_peso_kg">Capacidade</vs-th>
							<vs-th></vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="ocup_veiculo">Ocupação</vs-th>
							<vs-th></vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="distancia">Distância</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="tempo_viagem">Tempo</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="qtde_coletas">Solic.</vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap" :data="data[indextr].placa">
									<div :class="{'flex items-center': true, 'text-center': true}">
										<vx-tooltip
											:text="data[indextr].ignicao == 'S' ? 'Veículo ligado' : 'Veículo desligado'"
											position="top"
										>
											<feather-icon
												icon="TruckIcon"
												:svgClasses="data[indextr].ignicao == 'S' ? 'w-5 h-5 text-success' : 'w-5 h-5 text-danger'"
												class="mr-2 mt-1"
											/>
										</vx-tooltip>
										<span>{{data[indextr].placa}}</span>
									</div>
									<div v-if="data[indextr].nome_motorista != null">
										<span style="font-size: 12px; color:gray">{{data[indextr].nome_motorista | truncate(15)}}</span>
									</div>
								</vs-td>

								<vs-td
									class="whitespace-no-wrap"
									:data="data[indextr].tipo_veiculo"
								>{{data[indextr].tipo_veiculo}}</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].consumo">
									<vs-avatar
										v-if="data[indextr].consumo == '1'"
										size="small"
										color="rgb(90, 225, 155)"
										text="1"
									/>
									<vs-avatar v-if="data[indextr].consumo == '2'" size="small" color="success" text="2" />
									<vs-avatar
										v-if="data[indextr].consumo == '3'"
										size="small"
										color="rgb(255, 200, 100)"
										text="3"
									/>
									<vs-avatar v-if="data[indextr].consumo == '4'" size="small" color="warning" text="4" />
									<vs-avatar
										v-if="data[indextr].consumo == '5'"
										size="small"
										color="rgb(240, 145, 140)"
										text="5"
									/>
									<vs-avatar v-if="data[indextr].consumo == '6'" size="small" color="danger" text="6" />
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<span class="mr-2" v-if="data[indextr].sis_carga_empilha == 'S'">E</span>
									<span class="mr-2" v-if="data[indextr].sis_carga_ponte == 'S'">PR</span>
									<span v-if="data[indextr].sis_carga_manual == 'S'">M</span>
								</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].capacid_peso_kg">
									<div>{{data[indextr].capacid_peso_kg}}</div>

									<div v-if="data[indextr].dimensoes != null">
										<span style="font-size: 12px; color:gray">{{data[indextr].dimensoes}}</span>
									</div>
								</vs-td>

								<vs-td>
									<feather-icon
										v-if="((data[indextr].geo_lat != 0) & 
										       (data[indextr].geo_lat != null) & 
											   (data[indextr].geo_lng != 0) & 
											   (data[indextr].geo_lng != null))"
										icon="MapPinIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										class="ml-2"
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

								<vs-td class="whitespace-no-wrap" :data="data[indextr].distancia_txt">
									<div>
										<span
											:class="data[indextr].menor_distancia == 'S' ? 'font-semibold text-success' : ''"
										>{{data[indextr].distancia_txt}}</span>
									</div>

									<div v-if="data[indextr].local_veiculo != null">
										<span style="font-size: 12px; color:gray">{{data[indextr].local_veiculo}}</span>
									</div>
								</vs-td>

								<vs-td>
									<div>
										<span
											class="mr-1"
											v-if="data[indextr].tempo_viagem != ''"
											:class="data[indextr].tempo_ok == 'N' ? data[indextr].menor_tempo == 'S' ? 'font-semibold text-danger' : 'text-danger' : data[indextr].menor_tempo == 'S' ? 'font-semibold text-success' : ''"
										>{{data[indextr].tempo_viagem}}</span>
									</div>
									<div>
										<span
											style="font-size: 12px"
											:class="data[indextr].tempo_ok == 'N' ? data[indextr].menor_tempo == 'S' ? 'font-semibold text-danger' : 'text-danger' : data[indextr].menor_tempo == 'S' ? 'font-semibold text-success' : ''"
										>({{data[indextr].hr_prev_chegada | hora_min}})</span>
									</div>
								</vs-td>

								<vs-td>
									<div v-if="data[indextr].qtde_coletas > 0">
										<div class="flex items-center text-center">
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
								<div :class="{'flex items-center': true, 'text-center': false}">
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
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import MapaPopUp from "@/components/rgsoft/MapaPopUp.vue";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	mixins: [procsMixins],
	components: {
		MapaPopUp,
		vSelect,
		flatPickr,
		ColFixa
	},
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false,

			placa_veiculo: null,
			peso_total: null,
			coletasVei: [],
			popupActive: false,

			comMotorista: true,
			comCarga: null,

			filtros: [
				{
					cliente_id: null,
					nome_cliente: null,
					nome_empresa: null,
					cod_tipo_veiculo: null,
					descricao_veiculo: null,
					sisCargaSelected: { label: "Todos", value: "todos" },
					hora_coleta: new Date(),
					comMotorista: true,
					comCarga: null
				}
			],

			clienteData: [],

			sisCargaOptions: [
				{ label: "Todos", value: "todos" },
				{ label: "Empilhadeira", value: "E" },
				{ label: "Ponte Rolante", value: "P" },
				{ label: "Manual", value: "M" }
			],

			configdateTimePickerTime: {
				enableTime: true,
				enableSeconds: false,
				noCalendar: true,
				locale: Portuguese
			}
		};
	},
	created() {
		//Não queremos que entre pesquisando.
		//this.getBuscarVeiculos();
		this.getCliente();
		this.lerTipoVeiculo();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		buscarVeiculosData() {
			return this.$store.state.atendimento.buscarVeiculosData;
		},
		currentPage() {
			if (this.isMounted) {
				return this.$refs.table.currentx;
			}
			return 1;
		},
		queriedItems() {
			return this.$refs.table
				? this.$refs.table.queriedResults.length
				: this.buscarVeiculosData.length;
		},
		clienteCombo: {
			get() {
				if (this.filtros[0].cliente_id == null) {
					return "";
				} else {
					return {
						nome:
							this.filtros[0].nome_empresa +
							" - " +
							this.filtros[0].nome_cliente
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.filtros[0].cliente_id = obj.id;
					this.filtros[0].nome_cliente = obj.nome;
					this.filtros[0].nome_empresa = obj.nome_empresa;
				} else {
					this.filtros[0].cliente_id = null;
					this.filtros[0].nome_cliente = null;
					this.filtros[0].nome_empresa = null;
				}
			}
		},
		tipoVeiculo() {
			return this.$store.state.tipoVeiculo.tipoVeiculoPesqData;
		},
		tipoVeiculoCombo: {
			get() {
				if (this.filtros[0].descricao_veiculo == null) {
					return "Todos";
				} else {
					return {
						descricao: this.filtros[0].descricao_veiculo
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.filtros[0].cod_tipo_veiculo = obj.codigo;
					this.filtros[0].descricao_veiculo = obj.descricao;
				} else {
					this.filtros[0].cod_tipo_veiculo = null;
					this.filtros[0].descricao_veiculo = null;
				}
			}
		}
	},
	watch: {
		comMotorista: function(newValue, oldValue) {
			this.filtros[0].comMotorista = newValue;
			this.refresh();
		},
		comCarga: function(newValue, oldValue) {
			this.filtros[0].comCarga = newValue;
			this.refresh();
		}
	},
	methods: {
		async getBuscarVeiculos() {
			await this.$store
				.dispatch("atendimento/getBuscarVeiculos", this.filtros[0])
				.catch(err => {
					console.error(err);
				});
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getBuscarVeiculos();
			await this.$vs.loading.close();
		},
		getCliente() {
			this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: 0 //Vai trazer os clientes de todas as empresas.
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(response => {
					if (response.data.status) {
						this.clienteData = response.data.cliente;
					}
				})
				.catch();
		},
		async lerTipoVeiculo() {
			this.$store.dispatch("tipoVeiculo/lerTipoVeiculo").catch(err => {
				console.error(err);
			});
		},
		exibirFotoCarga(dados) {
			let titulo = "Carga do veículo: " + dados.placa;
			this.exibirFoto(dados.url_imagem, titulo);
		},
		exibirColetas(coletas) {
			this.placa_veiculo = coletas.placa;
			this.peso_total = coletas.peso_total_coletas;
			this.coletasVei = coletas.coletas_veiculo;
			this.popupActive = !this.popupActive;
		},
		exibirLocalizacao(dados) {
			this.$store.commit("EXIBIR_MAPA_POPUP", dados);
		}
	}
};
</script>

<style lang="scss">
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