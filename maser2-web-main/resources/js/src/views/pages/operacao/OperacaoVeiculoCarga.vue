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
									<span>Carga do veículo</span>
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
						:noDataText="coletasVeiculoCargaData.carga.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						max-items="100"
						:data="coletasVeiculoCargaData.carga"
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
								<vs-td class="whitespace-no-wrap">
									<span class="font-semibold" style="font-size: 12px">Prev. Entrega</span>
								</vs-td>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Entrega</span>
								</vs-td>
								<vs-td>
									<span class="font-semibold" style="font-size: 12px">Status</span>
								</vs-td>
								<vs-td>									
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
										v-if="data[indextr].dt_prev_entrega != null"
										:style="emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)"
									>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
									<div v-if="data[indextr].entrega_urgente == 'S'">
										<span class="badge_vermelho">urgente</span>
									</div>
									<div v-else style="font-size: 12px; font-weight: 500;">
										{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}
									</div>
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
									<div class="flex flex-items-center text-center" v-if="['E1', 'E2', 'E3', 'E4'].includes(data[indextr].status)">										
										<vx-tooltip
											v-if="data[indextr].entrega_consolidada == 'S'"
											text="Desmarcar entrega consolidada"
											position="left"											
										>												
											<!-- O "style" está sendo alterado de cor com os hexadecimais, porque a linha toda pode ficar colorida e sobrepor a regra, 
											desta forma a regra do style com !important vai comandar.-->
											<feather-icon
												icon="Share2Icon"
												svgClasses="w-5 h-5 hover:text-success"
												class="shareRotate"
												:style="'color:#28C76F; !important'"
												@click="setarEntregaConsolidada(data[indextr].coleta_id, false)"
											/>
										</vx-tooltip>
										<vx-tooltip
											v-else
											text="Marcar entrega como consolidada"
											position="left"
										>	
											<!-- O "style" está sendo alterado de cor com os hexadecimais, porque a linha toda pode ficar colorida e sobrepor a regra, 
											desta forma a regra do style com !important vai comandar.-->
											<feather-icon
												icon="Share2Icon"
												svgClasses="w-5 h-5 hover:text-success"
												class="shareRotate"
												:style="'color:#626262; !important'"
												@click="setarEntregaConsolidada(data[indextr].coleta_id, true)"
											/>
										</vx-tooltip>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<vs-button
										v-if="podeAutorizarBaldeacao(data[indextr])"
										class="mr-2"
										color="primary"
										type="border"
										size="small"
										@click="baldear(data[indextr])"
									>BALDEAR</vs-button>
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

		<vs-popup class="holamundo" title="Fazer baldeação" :active.sync="popupBaldeacao">
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<label class="vs-input--label">Baldear para veículo</label>
					<v-select
						label="placa"
						v-model="veiculosBaldeacaoCombo"
						:options="veiculosBaldeacaoData"
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
						v-model="autorizarBaldeacao"
						vs-value="S"
					>Executar baldeação sem autorização do motorista</vs-checkbox>
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
				:disabled="validarBaldeacao"
				@click="executarBaldeacaoPatio(coletaIdBaldeacao, veicBaldSel[0].placa)"
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
	name: "operacao-veiculo-carga",
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: { MapaPopUp, vSelect, ColFixa },
	data() {
		return {
			popupBaldeacao: false,
			coletaIdBaldeacao: 0,			

			veiculosBaldeacaoData: [],			
			veicBaldSel: [
				{
					placa: null,
					descr_tipo_veiculo: 0,
				},
			],
			autorizarBaldeacao: null,
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
		veiculosBaldeacaoCombo: {
			get() {
				if (this.veicBaldSel[0].placa == null) {
					return "Selecione o veículo";
				} else {
					return {
						placa:
							this.veicBaldSel[0].placa +
							" - " +
							this.veicBaldSel[0].descr_tipo_veiculo,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.veicBaldSel[0].placa = obj.placa;
					this.veicBaldSel[0].descr_tipo_veiculo =
						obj.descr_tipo_veiculo;
				} else {
					this.veicBaldSel[0].placa = null;
					this.veicBaldSel[0].descr_tipo_veiculo = null;
				}
			},
		},
		validarBaldeacao() {
			let desabilitado = true;

			if (
				this.veicBaldSel[0].placa != null &&
				this.autorizarBaldeacao != null
			) {
				desabilitado = false;
			}

			return desabilitado;
		},
	},
	methods: {
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
					.coletasVeiculoCargaFiltros.hora_saida,
			};
			await this.retornarColetasVeiculoCarga(payload);

			await this.$vs.loading.close();
		},
		async fecharPainel() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getVeiculosFrota(
				this.$store.state.controle.veiculosFrotaFiltros
			);
			await this.$store.commit(
				"operacao/EXIBIR_PAINEL_OPERACAO_VEICULO_CARGA"
			);
			await this.$vs.loading.close();
		},
		podeAutorizarBaldeacao(dados) {
			//O teste da carga está levemente diferente do teste da Rota. Cuidar as alterações e campos.

			// E3 - Liberamos a baldeação para o status "E3" => quando o veículo já chegou no local de ENTREGA e o atendimento vai demorar.
			// A carga pode ser baldeada para outro veículo para aguardar o atendimento e assim liberar o veículo anterior para outro serviço.
			let retorno = false;
			if ((["CR", "EN", "EP"].includes(dados.status) && (dados.carga_pavilhao == null)) || ["E1", "E3"].includes(dados.status)) {
				retorno = true;
				if (dados.coleta_fixa == "M" && this.vueIgualZeroNull(dados.solic_origem_id)) {
					retorno = false;
				}
			}
			return retorno;
		},
		async baldear(dados) {
			this.coletaIdBaldeacao = dados.coleta_id;
			this.autorizarBaldeacao = null;
			await this.retornarVeiculosBaldeacaoSimples(
				this.coletaIdBaldeacao,
				"N"
			);
			this.popupBaldeacao = !this.popupBaldeacao;
		},
		async retornarVeiculosBaldeacaoSimples(id, comMotorista) {
			this.veicBaldSel[0].placa = null;
			this.veicBaldSel[0].descr_tipo_veiculo = null;
			await this.$http
				.post(
					`api/controle/RetornarVeiculosBaldeacaoSimples`,
					{
						coleta_id: id,
						com_motorista: comMotorista,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then((response) => {
					if (response.data.status) {
						this.veiculosBaldeacaoData = response.data.dados;
					}
				})
				.catch();
		},
		async executarBaldeacaoPatio(col_id, placa) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: col_id,
				placa_destino: placa,
			};

			await this.$store
				.dispatch("controle/executarBaldeacaoPatio", payload)
				.catch((err) => {
					console.error(err);
				});

			this.popupBaldeacao = false;

			await this.getDataAtual();
			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);

			const payloadCol = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida,
			};
			await this.retornarColetasVeiculoCarga(payloadCol);

			await this.$vs.loading.close();
		},
		async setarEntregaConsolidada(col_id, consol) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: col_id,
				consolidada: consol,
			};

			await this.$store
				.dispatch("controle/setarEntregaConsolidada", payload)
				.then(async response => {

					if (response.data.retorno.cod_retorno != "Z100") {
						await this.$vs.notify({
							time: 10000,
							text: response.data.retorno.msg_retorno,
							iconPack: "feather",
							icon: "icon-alert-circle",
							color: "danger"
						});
					} 
				})
				.catch(async error => {
					await this.$vs.loading.close();
				});

			const payloadCol = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida_descr,
				local_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.local_saida,
				hora_saida: this.$store.state.controle
					.coletasVeiculoCargaFiltros.hora_saida,
			};
			await this.retornarColetasVeiculoCarga(payloadCol);

			await this.$vs.loading.close();
		},
	},
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

.shareRotate {   
  display:inline-block;  
  transform: rotate(90deg)
 }
</style>

