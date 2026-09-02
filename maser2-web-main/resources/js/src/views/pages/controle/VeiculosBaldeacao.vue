<template>
	<div>
		<template>
			<!-- ALERTA VOLTAR PAINEL -->
			<div
				class="flex items-center text-center mb-4"
				v-if="windowWidth < 1200 || $store.state.reduceButton == false"
			>
				<feather-icon icon="ChevronsLeftIcon" svgClasses="w-5 h-5 text-primary" class="mr-2" />
				<span class="text-primary cursor-pointer" @click="fecharVeiculosBaldeacao">Voltar</span>
			</div>

			<div
				class="button-prev"
				type="flat"
				size="Large"
				v-if="windowWidth > 1200 && $store.state.reduceButton == true"
			>
				<vs-button class="p-0" size="small" type="border" @click="fecharVeiculosBaldeacao">
					<feather-icon icon="ChevronLeftIcon" svgClasses="h-12 w-12" class="cursor-pointer" />
				</vs-button>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3">
					<vx-card class="mb-2">
						<div class="flex flex-reverse items-center flex-grow justify-between">
							<h4>Cliente</h4>
							<vs-chip transparent color="warning">
								<feather-icon icon="RepeatIcon" svgClasses="w-4 h-4 text-warning" class="mr-2" />
								<span class="font-semibold">BALDEAÇÃO</span>
							</vs-chip>
						</div>
						<div class="mt-2">
							<h6>{{veiculosBaldeacaoDados.nome_cliente | truncate(55)}}</h6>
							<div class="mt-4">
								<span class="mr-1">Solicitação:</span>
								<span class="font-semibold">{{veiculosBaldeacaoDados.numero}}</span>
							</div>
							<div class="mt-1">
								<span
									class="font-semibold mr-1"
									v-if="exibirDia(veiculosBaldeacaoDados.data_cad) != ''"
								>{{veiculosBaldeacaoDados.data_cad | moment("DD MMM")}}</span>
								<span class="font-semibold">{{veiculosBaldeacaoDados.hora_cad | hora_min }}</span>
							</div>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3">
					<vx-card
						class="mb-2"
						:style="veiculosBaldeacaoDados.etapa == 'Coleta' ? 'background-color: rgb(255, 241, 227)' : ''"
					>
						<div class="flex flex-reverse items-center flex-grow justify-between">
							<h4>Coleta</h4>
							<!-- CHIP usado para alinhamento -->
							<vs-chip
								transparent
								:color="veiculosBaldeacaoDados.etapa == 'Coleta' ? 'rgb(255, 241, 227)' : 'white'"
							></vs-chip>
							<vs-chip
								transparent
								v-if="veiculosBaldeacaoDados.coleta_fixa_id != null"
							>Fixa</vs-chip>
						</div>
						<div class="mt-2">
							<h6>{{veiculosBaldeacaoDados.local_coleta | truncate(55)}}</h6>
							<div class="mt-4">
								<span class="mr-1">Placa:</span>
								<span class="font-semibold">{{veiculosBaldeacaoDados.placa_coleta}}</span>
							</div>
							<div class="mt-1">
								<span class="mr-1">Data:</span>
								<span
									class="font-semibold mr-1"
									v-if="exibirDia(veiculosBaldeacaoDados.dt_efet_coleta) != ''"
								>{{veiculosBaldeacaoDados.dt_efet_coleta | moment("DD MMM")}}</span>
								<span class="font-semibold">{{veiculosBaldeacaoDados.hr_sai_coleta | hora_min }}</span>
								<feather-icon
									v-if="veiculosBaldeacaoDados.hr_sai_coleta != null"
									class="ml-1"
									icon="CheckCircleIcon"
									svgClasses="w-4 h-4 text-success"
								/>
							</div>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3">
					<vx-card
						class="mb-2"
						:style="veiculosBaldeacaoDados.etapa == 'Entrega' ? 'background-color: rgb(255, 241, 227)' : ''"
					>
						<div class="flex flex-reverse items-center flex-grow justify-between">
							<h4>Entrega</h4>
							<!-- CHIP usado para alinhamento -->
							<vs-chip
								transparent
								:color="veiculosBaldeacaoDados.etapa == 'Entrega' ? 'rgb(255, 241, 227)' : 'white'"
							></vs-chip>
							<vs-chip
								transparent
								color="danger"
								v-if="veiculosBaldeacaoDados.entrega_urgente == 'S'"
							>Urgente</vs-chip>
						</div>
						<div class="mt-2">
							<h6>{{veiculosBaldeacaoDados.local_entrega | truncate(55)}}</h6>
							<div class="mt-4">
								<span class="mr-1">Placa:</span>
								<span class="font-semibold mr-1">{{veiculosBaldeacaoDados.placa_entrega}}</span>
							</div>
							<div class="mt-1">
								<span class="mr-1">Previsão:</span>
								<span
									class="font-semibold mr-1"
									v-if="exibirDia(veiculosBaldeacaoDados.dt_prev_entrega) != ''"
								>{{veiculosBaldeacaoDados.dt_prev_entrega | moment("DD MMM")}}</span>
								<span class="font-semibold">{{veiculosBaldeacaoDados.hr_prev_entrega | hora_min }}</span>
							</div>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full">
					<vx-card v-if="veiculosBaldeacaoDados.caract != null && veiculosBaldeacaoDados.caract != ''">
						<span class="font-semibold mr-1" style="font-size: 12px">Características:</span>
						<span style="font-size: 12px">{{veiculosBaldeacaoDados.caract}}</span>
					</vx-card>
				</div>
			</div>

			<div id="veiculos-baldeacao">
				<vs-table ref="table" max-items="100" :data="veiculosBaldeacaoVeiculos">
					<template slot-scope="{data}">
						<tbody>
							<!-- LINHA DO HEADER -->
							<vs-tr>
								<vs-td class="whitespace-no-wrap">
									<div>
										<span class="font-semibold" style="font-size: 12px">Veículo atual</span>
									</div>
									<div>
										<span
											v-if="veiculosBaldeacaoDados.placa_atual != null"
										>{{veiculosBaldeacaoDados.placa_atual}}</span>
										<span v-else>Não definido</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Tipo de veículo</span>
									</div>
									<div v-if="veiculosBaldeacaoDados.tipo_veiculo != null">
										<span>{{veiculosBaldeacaoDados.tipo_veiculo}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Dimensões</span>
									</div>
									<div v-if="veiculosBaldeacaoDados.dim_carga != null">
										<span>{{veiculosBaldeacaoDados.dim_carga}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Peso</span>
									</div>
									<div v-if="veiculosBaldeacaoDados.vol_carga != null">
										<span>{{veiculosBaldeacaoDados.vol_carga}}</span>
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
									<div v-if="data[indextr].tipo_veiculo != null">
										<span>{{data[indextr].tipo_veiculo}}</span>
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
										<span
											v-if="data[indextr].tempo_viagem != ''"
											:class="data[indextr].menor_tempo == 'S' ? 'font-semibold text-success' : ''"
										>{{data[indextr].tempo_viagem}}</span>
									</div>
									<div>
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
									<vx-tooltip text="Autorizar baldeação" position="left">
										<vs-button
											type="border"
											size="small"
											@click="instrucaoBaldeacao(veiculosBaldeacaoDados, data[indextr])"
										>AUTORIZAR</vs-button>
									</vx-tooltip>
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

		<vs-popup class="holamundo minh450" title="Instrução de Baldeação" :active.sync="popupInstrucao">
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<vs-input class="w-full" disabled label="Última instrução enviada" v-model="ultimaInstrucao" />
				</div>
			</div>

			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<vs-input class="w-full" disabled label="Baldear para veículo" v-model="placaInstrucao" />
				</div>
			</div>

			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<vs-input
						maxlength="255"
						class="w-full"
						label="Texto da instrução"
						v-model="digitarInstrucao"
					/>
				</div>
			</div>

			<vs-alert icon-pack="feather" icon="icon-info" class="h-full my-4 mt-8 mb-6" color="warning">
				<div>Uma notificação será enviada para o celular do motorista.</div>
			</vs-alert>
			<vs-button
				type="border"
				:disabled="validarInstrucao"
				@click="enviarInstrucao(coletaIdInstrucao, '05', digitarInstrucao, placaInstrucao)"
			>Enviar instrução</vs-button>
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
	name: "veiculos-baldeacao",
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

			veicComMotorista: "S",

			popupInstrucao: false,
			coletaIdInstrucao: 0,
			ultimaInstrucao: null,
			placaInstrucao: null,
			digitarInstrucao: null
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
		veiculosBaldeacaoDados() {
			return this.$store.state.controle.veiculosBaldeacaoDados;
		},
		veiculosBaldeacaoVeiculos() {
			return this.$store.state.controle.veiculosBaldeacaoVeiculos;
		},
		validarInstrucao() {
			let desabilitado = false;
			if (this.digitarInstrucao == "" || this.digitarInstrucao == null) {
				desabilitado = true;
			}
			return desabilitado;
		}
	},
	watch: {
		veicComMotorista: function(newValue, oldValue) {
			this.refresh();
		}
	},
	methods: {
		async fecharVeiculosBaldeacao() {
			this.$store.commit("controle/EXIBIR_VEICULOS_BALDEACAO");
		},
		exibirColetas(coletas) {
			this.placa_veiculo = coletas.placa;
			this.peso_total = coletas.peso_total_coletas;
			this.coletasVei = coletas.coletas_veiculo;
			this.popupActive = !this.popupActive;
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			const payloadBaldeacao = {
				coleta_id: this.veiculosBaldeacaoDados.coleta_id,
				com_motorista: this.veicComMotorista
			};
			await this.retornarVeiculosBaldeacao(payloadBaldeacao);
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
		},
		instrucaoBaldeacao(coleta, dados) {
			this.coletaIdInstrucao = coleta.coleta_id;
			this.ultimaInstrucao = coleta.txt_instrucao;
			this.placaInstrucao = dados.placa;
			this.digitarInstrucao = null;
			this.popupInstrucao = !this.popupInstrucao;
		},
		async enviarInstrucao(col_id, instr, txt_instr, placa_bald) {
			await this.$vs.loading({ scale: 0.5 });
			const payload = {
				coleta_id: col_id,
				instrucao: instr,
				txt_instrucao: txt_instr,
				placa_baldeacao: placa_bald
			};

			await this.$store
				.dispatch("controle/enviarInstrucaoColeta", payload)
				.catch(err => {
					console.error(err);
				});
			this.popupInstrucao = false;

			// Recarrega as coletas em andamento, entregas pendentes e entregas em andamento,
			// pois existe um FLAG apontando a baldeação.
			await this.getColetasAndamento();
			await this.getEntregasPendentes();
			await this.getEntregasAndamento();

			// Recarrega as coletas do veículo para atualizar a última instrução.
			const payloadCol = {
				placa: this.veiculosBaldeacaoDados.placa_atual,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida
			};
			await this.retornarColetasVeiculoCarga(payloadCol);

			await this.fecharVeiculosBaldeacao();
			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss">
#veiculos-baldeacao {
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