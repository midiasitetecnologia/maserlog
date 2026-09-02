<template>
	<div>
		<template>
			<!-- ALERTA VOLTAR PAINEL -->
			<div
				:class="{'flex items-center': true, 'text-center': true}"
				class="mb-4"
				v-if="windowWidth < 1200 || $store.state.reduceButton == false"
			>
				<feather-icon icon="ChevronsLeftIcon" svgClasses="w-5 h-5 text-primary" class="mr-2" />
				<span class="text-primary cursor-pointer" @click="definirVeiculos">Voltar</span>
			</div>

			<div
				class="button-prev"
				type="flat"
				size="Large"
				v-if="windowWidth > 1200 && $store.state.reduceButton == true"
			>
				<vs-button class="p-0" size="small" type="border" @click="definirVeiculos">
					<feather-icon icon="ChevronLeftIcon" svgClasses="h-12 w-12" class="cursor-pointer" />
				</vs-button>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3">
					<vx-card class="mb-2">
						<div class="flex flex-reverse items-center flex-grow justify-between">
							<h4>Cliente</h4>
							<!-- CHIP usado para alinhamento -->
							<vs-chip
								transparent
								color="white"
								v-if="dadosColetaData.coleta_fixa_id != null || dadosColetaData.entrega_urgente == 'S'"
							></vs-chip>
						</div>
						<div class="mt-2">
							<h6>{{dadosColetaData.nome_cliente | truncate(55)}}</h6>
							<div class="mt-4">
								<span class="mr-1">Solicitação:</span>
								<span class="font-semibold">{{dadosColetaData.numero}}</span>
							</div>
							<div class="mt-1">
								<span
									class="font-semibold mr-1"
									v-if="exibirDia(dadosColetaData.data_cad) != ''"
								>{{dadosColetaData.data_cad | moment("DD MMM")}}</span>
								<span class="font-semibold">{{dadosColetaData.hora_cad | hora_min }}</span>
							</div>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3">
					<vx-card class="mb-2">
						<div class="flex flex-reverse items-center flex-grow justify-between">
							<h4>Coleta</h4>
							<!-- CHIP usado para alinhamento -->
							<vs-chip
								transparent
								color="white"
								v-if="dadosColetaData.coleta_fixa_id != null || dadosColetaData.entrega_urgente == 'S'"
							></vs-chip>
							<vs-chip
								transparent
								v-if="dadosColetaData.coleta_fixa_id != null"
							>Fixa</vs-chip>
						</div>
						<div class="mt-2">
							<h6>{{dadosColetaData.local_coleta | truncate(55)}}</h6>
							<div class="mt-4">
								<span class="mr-1">Atendimento:</span>
								<span
									class="font-semibold mr-1"
									v-if="montarHorario(dadosColetaData.hr_ini_coleta_man, dadosColetaData.hr_fim_coleta_man, dadosColetaData.hr_ini_coleta_tar, dadosColetaData.hr_fim_coleta_tar) != null"
								>{{montarHorario(dadosColetaData.hr_ini_coleta_man, dadosColetaData.hr_fim_coleta_man, dadosColetaData.hr_ini_coleta_tar, dadosColetaData.hr_fim_coleta_tar)}}</span>
								<span v-else class="font-semibold">Não definido</span>
							</div>
							<div class="mt-1">
								<span class="mr-1">Previsão:</span>
								<span
									class="font-semibold mr-1"
									v-if="exibirDia(dadosColetaData.dt_prev_coleta) != ''"
								>{{dadosColetaData.dt_prev_coleta | moment("DD MMM")}}</span>
								<span class="font-semibold">{{dadosColetaData.hr_prev_coleta | hora_min }}</span>
							</div>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3">
					<vx-card class="mb-2">
						<div class="flex flex-reverse items-center flex-grow justify-between">
							<h4>Entrega</h4>
							<!-- CHIP usado para alinhamento -->
							<vs-chip
								transparent
								color="white"
								v-if="dadosColetaData.coleta_fixa_id != null || dadosColetaData.entrega_urgente == 'S'"
							></vs-chip>
							<vs-chip transparent color="danger" v-if="dadosColetaData.entrega_urgente == 'S'">Urgente</vs-chip>
						</div>
						<div class="mt-2">
							<h6>{{dadosColetaData.local_entrega | truncate(55)}}</h6>
							<div class="mt-4">
								<span class="mr-1">Atendimento:</span>
								<span
									class="font-semibold mr-1"
									v-if="montarHorario(dadosColetaData.hr_ini_entrega_man, dadosColetaData.hr_fim_entrega_man, dadosColetaData.hr_ini_entrega_tar, dadosColetaData.hr_fim_entrega_tar) != null"
								>{{montarHorario(dadosColetaData.hr_ini_entrega_man, dadosColetaData.hr_fim_entrega_man, dadosColetaData.hr_ini_entrega_tar, dadosColetaData.hr_fim_entrega_tar)}}</span>
								<span v-else class="font-semibold">Não definido</span>
							</div>
							<div class="mt-1">
								<span class="mr-1">Previsão:</span>
								<span
									class="font-semibold mr-1"
									v-if="exibirDia(dadosColetaData.dt_prev_entrega) != ''"
								>{{dadosColetaData.dt_prev_entrega | moment("DD MMM")}}</span>
								<span class="font-semibold">{{dadosColetaData.hr_prev_entrega | hora_min }}</span>
							</div>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full">
					<vx-card v-if="dadosColetaData.caract != null && dadosColetaData.caract != ''">
						<span class="font-semibold mr-1" style="font-size: 12px">Características:</span>
						<span style="font-size: 12px">{{dadosColetaData.caract}}</span>
					</vx-card>
				</div>
			</div>

			<div id="definir-veiculo">
				<vs-table ref="table" max-items="100" :data="veiculosData">
					<template slot-scope="{data}">
						<tbody>
							<!-- LINHA DO HEADER -->
							<vs-tr>
								<vs-td class="whitespace-no-wrap">
									<div>
										<span class="font-semibold" style="font-size: 12px">Veículo atual</span>
									</div>
									<div>
										<span v-if="dadosColetaData.placa_coleta != null">{{dadosColetaData.placa_coleta}}</span>
										<span v-else>Não definido</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Tipo de veículo</span>
									</div>
									<div v-if="dadosColetaData.tipo_veiculo != null">
										<span>{{dadosColetaData.tipo_veiculo}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Sistema de carga</span>
									</div>
									<div v-if="dadosColetaData.sis_carga != null">
										<span>{{dadosColetaData.sis_carga | descr_sis_carga}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Dimensões</span>
									</div>
									<div v-if="dadosColetaData.dim_carga != null">
										<span>{{dadosColetaData.dim_carga}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Peso</span>
									</div>
									<div v-if="dadosColetaData.vol_carga != null">
										<span>{{dadosColetaData.vol_carga}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" style="font-size: 12px" align="center">
									<span class="font-semibold">Ocupação</span>
								</vs-td>
								<vs-td class="whitespace-no-wrap" style="font-size: 12px" align="center">
									<span class="font-semibold">Foto</span>
								</vs-td>
								<vs-td class="whitespace-no-wrap" style="font-size: 12px" align="center">
									<span class="font-semibold">Distância</span>
								</vs-td>
								<vs-td class="whitespace-no-wrap" style="font-size: 12px" align="center">
									<span class="font-semibold">Tempo</span>
								</vs-td>
								<vs-td class="whitespace-no-wrap" style="font-size: 12px" align="center">
									<span class="font-semibold">Solic.</span>
								</vs-td>
								<vs-td align="center">
									<div class="flex flex-wrap-reverse flex-items-center justify-between">
										<div>
											<div>
												<span class="font-semibold" style="font-size: 12px">Motorista</span>
											</div>
											<div>
												<vs-checkbox v-model="veicComMotorista" vs-value="S" />
											</div>
										</div>
										<feather-icon
											@click="refresh"
											icon="RotateCwIcon"
											svgClasses="h-4 w-4"
											class="cursor-pointer"
										/>
									</div>
								</vs-td>
							</vs-tr>

							<!-- LINHA DOS DADOS -->
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td>
									<div :class="{'flex items-center': true, 'text-center': true}">
										<vx-tooltip
											v-if="data[indextr].local_veiculo != ''"
											:text="data[indextr].local_veiculo"
											position="top"
											class="mt-1"
										>
											<feather-icon
												icon="TruckIcon"
												:svgClasses="data[indextr].ignicao == 'S' ? 'w-5 h-5 text-success' : 'w-5 h-5 text-danger'"
												class="mr-2"
												@click="exibirLocalizacao(data[indextr])"
											/>
										</vx-tooltip>
										<vx-tooltip
											:title="data[indextr].pontos_veiculo + ' pontos'"
											:text="data[indextr].det_pontos"
											position="top"
										>
											<span class="whitespace-no-wrap">{{data[indextr].placa}}</span>
										</vx-tooltip>
									</div>
									<div v-if="data[indextr].nome_motorista != null">
										<span style="font-size: 12px; color:gray">{{data[indextr].nome_motorista}}</span>
									</div>
								</vs-td>

								<vs-td align="center">
									<div>
										<feather-icon
											:icon="data[indextr].tipo_veiculo_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].tipo_veiculo_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-warning'"
										></feather-icon>
									</div>
									<div v-if="data[indextr].tipo_veiculo != null">
										<span>{{data[indextr].tipo_veiculo}}</span>
									</div>
								</vs-td>

								<vs-td align="center">
									<div>
										<feather-icon
											:icon="data[indextr].sis_carga_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].sis_carga_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-warning'"
										></feather-icon>
									</div>
									<div>
										<span class="mr-2" v-if="data[indextr].sis_carga_empilha == 'S'">E</span>
										<span class="mr-2" v-if="data[indextr].sis_carga_ponte == 'S'">PR</span>
										<span v-if="data[indextr].sis_carga_manual == 'S'">M</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<feather-icon
											:icon="data[indextr].dimensoes_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].dimensoes_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-warning'"
										></feather-icon>
									</div>
									<div>
										<span>{{data[indextr].dimensoes}}</span>
									</div>
								</vs-td>

								<vs-td align="center">
									<div>
										<feather-icon
											:icon="data[indextr].capacid_peso_ok == 'S' ? 'CheckCircleIcon' : 'XCircleIcon'"
											:svgClasses="data[indextr].capacid_peso_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-danger'"
										></feather-icon>
									</div>
									<div>
										<span>{{data[indextr].capacid_peso_kg}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div align="center">
										<feather-icon
											:icon="data[indextr].ocup_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].ocup_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-warning'"
										></feather-icon>
									</div>
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

								<vs-td align="center">
									<span>{{data[indextr].distancia}}</span>
								</vs-td>

								<vs-td align="center">
									<div>
										<vx-tooltip
											v-if="data[indextr].msg_tempo != ''"
											:text="data[indextr].msg_tempo"
											position="top"
										>
											<feather-icon
												:icon="data[indextr].tempo_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
												:svgClasses="data[indextr].tempo_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-warning'"
											></feather-icon>
										</vx-tooltip>
										<feather-icon
											v-else
											:icon="data[indextr].tempo_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].tempo_ok == 'S' ? 'w-6 h-6 text-success' : 'w-6 h-6 text-warning'"
										></feather-icon>
									</div>
									<div>
										<span
											class="mr-1"
											v-if="data[indextr].tempo_viagem != ''"
											:class="data[indextr].menor_tempo == 'S' ? 'font-semibold text-success' : ''"
										>{{data[indextr].tempo_viagem}}</span>
										<span
											:class="data[indextr].menor_tempo == 'S' ? 'font-semibold text-success' : ''"
										>({{data[indextr].hr_prev_chegada | hora_min}})</span>
									</div>
								</vs-td>

								<vs-td align="center">
									<div v-if="data[indextr].qtde_coletas > 0">
										<vx-tooltip
											:text="data[indextr].qtde_solic_and > 0 ? 'Solicitações em andamento: ' + data[indextr].qtde_solic_and : 'Veículo com solicitações na fila: ' + data[indextr].qtde_coletas"
											position="top"
										>
											<feather-icon
												:icon="'ClipboardIcon'"
												:svgClasses="data[indextr].qtde_solic_and > 0 ? 'w-6 h-6 text-warning hover:text-success stroke-current cursor-pointer' : 'w-6 h-6 hover:text-success stroke-current cursor-pointer'"
												@click="exibirColetas(data[indextr])"
											></feather-icon>
										</vx-tooltip>
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
									<div v-if="data[indextr].qtde_coletas > 0">
										<span>{{data[indextr].qtde_coletas}}</span>
									</div>
								</vs-td>

								<vs-td align="center">
									<vs-button
										type="border"
										size="small"
										@click="definirVeiculoColeta(dadosColetaData.coleta_id, data[indextr].placa)"
									>DEFINIR</vs-button>
								</vs-td>
							</vs-tr>
						</tbody>
					</template>
				</vs-table>
			</div>
		</template>

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

						<vs-td class="whitespace-no-wrap" :data="data[indextr].status">
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
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "definicao-veiculos-coleta",
	mixins: [controleMixins, procsMixins],
	components: {
		ColFixa
	},
	data() {
		return {
			placa_veiculo: null,
			peso_total: null,
			coletasVei: [],
			popupActive: false,

			veicComMotorista: "S"
		};
	},
	mounted() {
		this.$store.commit("UPDATE_NAVBAR_TYPE", "hidden");
		window.scrollTo(0, 0);
	},
	beforeDestroy() {
		this.$store.commit("UPDATE_NAVBAR_TYPE", "floating");
	},
	computed: {
		dadosColetaData() {
			return this.$store.state.controle.dadosColetaData;
		},
		veiculosData() {
			return this.$store.state.controle.veiculosData;
		}
	},
	watch: {
		veicComMotorista: function(newValue, oldValue) {
			this.refresh();
		}
	},
	methods: {
		exibirColetas(coletas) {
			this.placa_veiculo = coletas.placa;
			this.peso_total = coletas.peso_total_coletas;
			this.coletasVei = coletas.coletas_veiculo;
			this.popupActive = !this.popupActive;
		},
		async definirVeiculos() {
			this.$store.commit("controle/SET_DEFINIR_VEICULOS");
		},
		async definirVeiculoColeta(coleta_id, placa) {
			this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: coleta_id,
				placa: placa,
				autorizar: "N" //A autorização da coleta será feita por outra ação comandada pelo usuário
			};

			await this.$store
				.dispatch("controle/definirVeiculoColeta", payload)
				.catch(err => {
					console.error(err);
				});

			await this.getColetasPendentes(
				this.$store.state.controle.coletasPendentesFiltros
			);

			await this.$store.commit("controle/SET_DEFINIR_VEICULOS");

			await this.$vs.loading.close();
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			const payloadVC = {
				coleta_id: this.dadosColetaData.coleta_id,
				com_motorista: this.veicComMotorista
			};
			await this.retornarVeiculosColeta(payloadVC);
			await this.$vs.loading.close();
		},
		exibirFotoCarga(dados) {
			let titulo = "Carga do veículo: " + dados.placa;
			this.exibirFoto(dados.url_imagem, titulo);
		},
		async exibirLocalizacao(dados) {
			if (
				dados.geo_lat != 0 &&
				dados.geo_lat != null &&
				dados.geo_lng != 0 &&
				dados.geo_lng != null
			) {
				await this.$store.commit("EXIBIR_MAPA_POPUP", dados);
			}
		}
	}
};
</script>

<style lang="scss">
#definir-veiculo {
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

.button-prev {
	position: fixed;
	bottom: 45%;
	left: 75px;
	z-index: 2500;
	background-color: transparent;
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