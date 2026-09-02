<template>
	<div>
		<div>
			<vx-card class="mb-6">
				<div class="vx-row">
					<div class="flex flex-grow justify-between">
						<div class="vx-row pl-2">
							<div class="vx-col mb-1">
								<div type="flat" size="Large">
									<vs-button size="small" type="border" @click="fecharPainel">
										<feather-icon icon="ChevronLeftIcon" svgClasses="h-12 w-12" class="cursor-pointer" />
									</vs-button>
								</div>
							</div>

							<div class="vx-col mb-1 mt-2">
								<div>
									<span>Rota do veículo</span>
								</div>
								<div>
									<span
										class="font-semibold whitespace-no-wrap"
										style="font-size: 18px"
									>{{veiculoCargaData.placa}}</span>
								</div>
							</div>
						</div>
						<div class="flex items-center">
							<feather-icon
								@click="refresh"
								icon="RotateCwIcon"
								svgClasses="h-4 w-4"
								class="cursor-pointer mr-4"
							/>
						</div>
					</div>
				</div>
			</vx-card>
		</div>

		<!-- COLETAS DO VEÍCULO - CARGA -->
		<div class="vx-row">
			<div class="vx-col w-full">
				<div id="carga-veiculo-css">
					<vs-table
						ref="table"
						:noDataText="coletasVeiculoCargaData.coletas.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						max-items="100"
						:data="coletasVeiculoCargaData.coletas"
					>
						<template slot-scope="{data}">
							<!-- LINHA DO HEADER -->
							<vs-tr>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Solicitação</span>
								</vs-td>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Coleta</span>
								</vs-td>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Prev. Entrega</span>
								</vs-td>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Entrega</span>
								</vs-td>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Status</span>
								</vs-td>
								<vs-td></vs-td>								
							</vs-tr>

							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center text-center">
										<span v-if="data[indextr].numero != null">{{data[indextr].numero}}</span>
										<span v-else>ID: {{data[indextr].coleta_id}}</span>
										<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<span>{{data[indextr].local_coleta | truncate(25)}}</span>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div
										v-if="data[indextr].dt_prev_entrega == data[indextr].data_cad"
									>{{data[indextr].hr_prev_entrega | hora_min }}</div>
									<div
										v-else
									>{{data[indextr].dt_prev_entrega | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<span>{{data[indextr].local_entrega | truncate(25)}}</span>
								</vs-td>

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

								<vs-td class="whitespace-no-wrap">
									<!-- Permitimos transferência somente dos contratos -->
									<vs-button									
										v-if="((data[indextr].coleta_fixa == 'C') && (data[indextr].status == 'C4'))"
										class="mr-2"
										color="primary"
										type="border"
										size="small"
										@click="transferir(data[indextr])"
									>TRANSFERIR</vs-button>
									<vs-button
										v-if="data[indextr].notas_fiscais > 0"
										color="primary"
										type="border"
										size="small"
										@click="notasFiscais(data[indextr].coleta_id)"
									>NOTAS</vs-button>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</div>
			</div>
		</div>

		<vs-popup class="holamundo" title="Fazer transferência" :active.sync="popupTransferir">
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<label class="vs-input--label">Transferir para veículo</label>
					<v-select
						label="placa"
						v-model="veiculosTransferirCombo"
						:options="veiculosTransferirData"
						clearable
					>
						<template v-slot:option="option">
							<div class="flex flex-items-center">
								<feather-icon
									v-if="option.motorista_id > 0"
									icon="UserIcon"
									svgClasses="h-4 w-4"
									class="mr-2"
								/>
								<!-- span apenas para espaçamento -->
								<span v-else class="mr-6" />
								<span>{{ option.placa }} - {{ option.descr_tipo_veiculo }}</span>
							</div>
						</template>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div class="vx-row mb-6">
				<div class="vx-col">
					<vs-checkbox
						v-model="autorizarTransferencia"
						vs-value="S"
					>Executar transferência sem autorização do motorista</vs-checkbox>
				</div>
			</div>

			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />

			<vs-button
				type="border"
				:disabled="validarTransferencia"
				@click="executarTransferenciaPatio(coletaIdTransferencia, veicTransSel[0].placa)"
			>Confirmar</vs-button>
		</vs-popup>
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";

import MapaPopUp from "@/components/rgsoft/MapaPopUp.vue";
import vSelect from "vue-select";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "operacao-veiculo-rota",
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: { MapaPopUp, vSelect, ColFixa },
	data() {
		return {
			popupTransferir: false,
			coletaIdTransferencia: 0,

			veiculosTransferirData: [],
			veicTransSel: [
				{
					placa: null,
					descr_tipo_veiculo: 0
				}
			],
			autorizarTransferencia: null
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
		veiculoCargaData() {
			return this.$store.state.controle.dadosVeiculoCargaData;
		},
		coletasVeiculoCargaData() {
			return this.$store.state.controle.dadosColetasVeiculoCargaData;
		},
		veiculosTransferirCombo: {
			get() {
				if (this.veicTransSel[0].placa == null) {
					return "Selecione o veículo";
				} else {
					return {
						placa:
							this.veicTransSel[0].placa +
							" - " +
							this.veicTransSel[0].descr_tipo_veiculo
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.veicTransSel[0].placa = obj.placa;
					this.veicTransSel[0].descr_tipo_veiculo =
						obj.descr_tipo_veiculo;
				} else {
					this.veicTransSel[0].placa = null;
					this.veicTransSel[0].descr_tipo_veiculo = null;
				}
			}
		},
		validarTransferencia() {
			let desabilitado = true;

			if (
				this.veicTransSel[0].placa != null &&
				this.autorizarTransferencia != null
			) {
				desabilitado = false;
			}

			return desabilitado;
		}
	},
	methods: {
		async fecharPainel() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getVeiculosFrota(
				this.$store.state.controle.veiculosFrotaFiltros
			);
			await this.$store.commit(
				"operacao/EXIBIR_PAINEL_OPERACAO_VEICULO_ROTA"
			);
			await this.$vs.loading.close();
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			await this.getDataAtual();
			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);

			const payload = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida
			};
			await this.retornarColetasVeiculoCarga(payload);

			await this.$vs.loading.close();
		},
		async transferir(dados) {
			this.coletaIdTransferencia = dados.coleta_id;
			this.autorizarTransferencia = null;
			await this.retornarVeiculosBaldeacaoSimples(
				this.coletaIdTransferencia,
				"N"
			);
			this.popupTransferir = !this.popupTransferir;
		},
		async retornarVeiculosBaldeacaoSimples(id, comMotorista) {
			this.veicTransSel[0].placa = null;
			this.veicTransSel[0].descr_tipo_veiculo = null;
			await this.$http
				.post(
					`api/controle/RetornarVeiculosBaldeacaoSimples`,
					{
						coleta_id: id,
						com_motorista: comMotorista
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
						this.veiculosTransferirData = response.data.dados;
					}
				})
				.catch();
		},
		async executarTransferenciaPatio(col_id, placa) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: col_id,
				placa_destino: placa
			};

			await this.$store
				.dispatch("controle/executarBaldeacaoPatio", payload)
				.then(async (response) => {					

					if (response.data.retorno.cod_retorno != "Z100") {
						await this.$vs.notify({
							time: 8000,
							text: response.data.retorno.msg_retorno,
							iconPack: "feather",
							icon: "icon-alert-circle",
							color: "danger",
						});
					}
				})
				.catch(err => {
					console.error(err);
				});

			this.popupTransferir = false;

			await this.getDataAtual();
			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);

			const payloadCol = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida
			};
			await this.retornarColetasVeiculoCarga(payloadCol);

			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss">
#carga-veiculo-css {
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
</style>

