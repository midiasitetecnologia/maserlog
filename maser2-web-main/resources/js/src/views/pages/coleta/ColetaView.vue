<template>
	<div id="page-coleta-view">
		<vs-alert
			color="danger"
			title="Coleta não encontrada"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de coleta com o id: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todos as</span>
				<router-link :to="{name:'coleta'}" class="text-inherit underline">Coletas</router-link>
			</span>
		</vs-alert>

		<br v-if="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Solicitação" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Tipo de coleta</td>
								<td>
									<vs-chip v-if="coletaData.coleta_fixa == 'D'" transparent color="primary">Diária</vs-chip>
									<vs-chip
										v-if="coletaData.coleta_fixa == 'C' && coletaData.solic_origem_id == null"
										transparent
										color="success"
									>Contrato</vs-chip>
									<vs-chip
										v-if="coletaData.coleta_fixa == 'C' && coletaData.solic_origem_id != null"
										transparent
									>Comanda {{ coletaData.id }}</vs-chip>
									<vs-chip v-if="coletaData.coleta_fixa == 'M'" transparent color="warning">Multi-destinos</vs-chip>
									<vs-chip class="ml-2" v-if="coletaData.coleta_fixa_id != null" transparent>Fixa</vs-chip>
									<vs-chip v-if="['R', 'D'].includes(coletaData.reentrega)" transparent>
										<template v-if="coletaData.coleta_reentrega_numero != null">
											<span>Reentrega</span>
											<feather-icon icon="ArrowLeftIcon" svgClasses="w-4 h-4 text-inherit ml-1 mr-1">									
											</feather-icon>
											<span>{{coletaData.coleta_reentrega_numero}}</span>
										</template>
										<template v-else>
											<span>Reentrega</span>
											<feather-icon icon="ArrowLeftIcon" svgClasses="w-4 h-4 text-inherit ml-1 mr-1">									
											</feather-icon>
											<span>ID {{coletaData.coleta_reentrega_id}}</span>
										</template>
									</vs-chip>									
								</td>
							</tr>
							<tr v-if="coletaData.coleta_fixa_id != null">
								<td class="font-semibold">ID Contrato</td>
								<td>#{{ coletaData.coleta_fixa_id }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Empresa</td>
								<td>{{ coletaData.nome_empresa }}</td>
							</tr>
							<tr v-if="coletaData.numero != null">
								<td class="font-semibold">Número</td>
								<td>{{ coletaData.numero }}</td>
							</tr>
							<tr v-if="coletaData.solic_origem_id != null">
								<td class="font-semibold">Solicitação origem</td>
								<td v-if="coletaData.coleta_origem_numero != null">
									<router-link
										:to="url(coletaData.coleta_origem_id)"
										target="_blank"
										class="text-inherit underline"
									>{{ coletaData.coleta_origem_numero }}</router-link>
								</td>
								<td v-else>
									<router-link
										:to="url(coletaData.coleta_origem_id)"
										target="_blank"
										class="text-inherit underline"
									>{{ coletaData.coleta_origem_id }}</router-link>
								</td>
							</tr>
							<tr>
								<td class="font-semibold">Data cadastro</td>
								<td>{{ coletaData.data_cad | moment("DD MMM YYYY") }} {{ coletaData.hora_cad | hora_min }}</td>
							</tr>
							<tr v-if="$acl.check('admin')">
								<td class="font-semibold">Cliente</td>
								<td>{{ coletaData.nome }} #{{coletaData.cod_cliente}}</td>
							</tr>
							<tr v-if="coletaData.distancia_total != null">
								<td class="font-semibold">Distância total</td>
								<td>{{ formatKmBR(coletaData.distancia_total) }} km</td>
							</tr>
							<tr v-if="coletaData.tempo_total != null">
								<td class="font-semibold">Tempo total</td>
								<td>{{ coletaData.tempo_total | hora_min }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Status</td>
								<td v-if="coletaData.status != 'CN'">{{ coletaData.status | coleta_status }}</td>
								<td v-else>
									<span v-if="coletaData.mot_nao_coleta == '01'">Cancelada: com deslocamento</span>
									<span v-else>Cancelada: sem deslocamento</span>
								</td>
							</tr>
							<tr v-if="coletaData.status == 'CN'">
								<td v-if="coletaData.obs_nao_coleta != null" class="font-semibold">Motivo</td>
								<td>{{ coletaData.obs_nao_coleta }}</td>
							</tr>
							<tr v-if="['EN', 'EP'].includes(coletaData.status)">
								<td v-if="coletaData.mot_nao_entrega != null" class="font-semibold">Motivo</td>
								<td>
									<span>{{ coletaData.mot_nao_entrega | mot_nao_entrega }}</span>
									<span v-if="coletaData.obs_nao_entrega != null">- {{ coletaData.obs_nao_entrega }}</span>
								</td>
							</tr>
							<tr>
								<td class="font-semibold">Origem</td>
								<td>{{ coletaData.origem_reg | coleta_origem_reg }}</td>
							</tr>
						</table>

						<br />

						<div class="vx-col w-full flex">
							<vs-button
								v-if="$acl.check('admin') && (coletaData.origem_reg != 'A3') && ['C0', 'C1', 'E0', 'E1'].includes(coletaData.status)"
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'coleta-web-edit', params: { id: $route.params.id }}"
							>Editar</vs-button>
							<vs-button
								v-if="!$acl.check('admin') && (coletaData.origem_reg == 'A3') && (coletaData.status == 'C0')"
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'coleta-web-edit', params: { id: $route.params.id }}"
							>Editar</vs-button>
							<vs-button
								v-if="$acl.check('admin') && ['C0', 'C1'].includes(coletaData.status)"
								type="border"
								color="danger"
								@click="confirmar(coletaData)"
							>Cancelar coleta</vs-button>
						</div>
					</vx-card>
				</div>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Coleta" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Data prevista</td>
								<td>{{ coletaData.dt_prev_coleta | moment("DD MMM YYYY") }} {{ coletaData.hr_prev_coleta | hora_min }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Local de coleta</td>
								<td
									v-if="coletaData.coleta_fixa == 'C' && coletaData.solic_origem_id != null"
								>{{ coletaData.local_coleta_cmd }}</td>
								<td v-else>{{ coletaData.local_coleta | truncate(25) }} #{{coletaData.cod_loc_coleta}}</td>
							</tr>
							<tr v-if="coletaData.solicitante != null">
								<td class="font-semibold">Solicitante</td>
								<td>{{ coletaData.solicitante }}</td>
							</tr>
							<tr v-if="coletaData.descr_veiculo != null">
								<td class="font-semibold">Tipo de veículo</td>
								<td>{{ coletaData.descr_veiculo }}</td>
							</tr>
							<tr v-if="coletaData.placa_coleta != null">
								<td class="font-semibold">Veículo</td>
								<td>{{ coletaData.placa_coleta }}</td>
							</tr>
							<tr v-if="coletaData.mot_coleta != null">
								<td class="font-semibold">Motorista</td>
								<td>{{ coletaData.mot_coleta }}</td>
							</tr>
							<tr v-if="coletaData.dt_efet_coleta != null">
								<td class="font-semibold">Data coleta</td>
								<td>{{ coletaData.dt_efet_coleta | moment("DD MMM YYYY")}}</td>
							</tr>
							<tr v-if="coletaData.hr_partida_coleta != null">
								<td class="font-semibold">Partida</td>
								<td>{{ coletaData.hr_partida_coleta | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.hr_cheg_coleta != null">
								<td class="font-semibold">Chegada</td>
								<td>{{ coletaData.hr_cheg_coleta | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.hr_atend_coleta != null">
								<td class="font-semibold">Atendimento</td>
								<td>{{ coletaData.hr_atend_coleta | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.dur_prev_coleta != null">
								<td class="font-semibold">Duração prevista</td>
								<td>{{ coletaData.dur_prev_coleta | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.hr_sai_coleta != null">
								<td class="font-semibold">Saída</td>
								<td>{{ coletaData.hr_sai_coleta | hora_min }}</td>
							</tr>
							<tr v-if="coletaData.distancia_coleta != null">
								<td class="font-semibold">Distância percorrida</td>
								<td>{{ formatKmBR(coletaData.distancia_coleta) }} km</td>
							</tr>							
							<tr v-if="coletaData.tempo_desloc_pavilhao != null">
								<td class="font-semibold">Deslocamento pavilhão</td>
								<td>{{ coletaData.tempo_desloc_pavilhao | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.tempo_coleta != null">
								<td class="font-semibold">Tempo gasto</td>
								<td>{{ coletaData.tempo_coleta | hora_min}}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Entrega" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Data prevista</td>
								<td>
									<span class="mr-2">
										{{ coletaData.dt_prev_entrega | moment("DD MMM YYYY") }} {{ coletaData.hr_prev_entrega | hora_min }}
									</span>
									<span v-if="coletaData.entrega_urgente == 'S'" class="badge_vermelho">urgente</span>
								</td>
							</tr>
							<tr>
								<td class="font-semibold">Local de entrega</td>
								<td
									v-if="coletaData.coleta_fixa == 'C' && coletaData.solic_origem_id != null"
								>{{ coletaData.local_entrega_cmd }}</td>
								<td v-else>{{ coletaData.local_entrega | truncate(25) }} #{{coletaData.cod_loc_entrega}}</td>
							</tr>
							<tr v-if="coletaData.recebedor != null">
								<td class="font-semibold">Recebedor</td>
								<td>{{ coletaData.recebedor }}</td>
							</tr>
							<tr v-if="coletaData.placa_entrega != null">
								<td class="font-semibold">Veículo</td>
								<td>{{ coletaData.placa_entrega }}</td>
							</tr>
							<tr v-if="coletaData.mot_entrega != null">
								<td class="font-semibold">Motorista</td>
								<td>{{ coletaData.mot_entrega }}</td>
							</tr>
							<tr v-if="coletaData.dt_efet_entrega != null">
								<td class="font-semibold">Data entrega</td>
								<td>{{ coletaData.dt_efet_entrega | moment("DD MMM YYYY")}}</td>
							</tr>
							<tr v-if="coletaData.hr_partida_entrega != null">
								<td class="font-semibold">Partida</td>
								<td>{{ coletaData.hr_partida_entrega | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.hr_cheg_entrega != null">
								<td class="font-semibold">Chegada</td>
								<td>{{ coletaData.hr_cheg_entrega | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.hr_atend_entrega != null">
								<td class="font-semibold">Atendimento</td>
								<td>{{ coletaData.hr_atend_entrega | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.dur_prev_entrega != null">
								<td class="font-semibold">Duração prevista</td>
								<td>{{ coletaData.dur_prev_entrega | hora_min}}</td>
							</tr>
							<tr v-if="coletaData.hr_sai_entrega != null">
								<td class="font-semibold">Saída</td>
								<td>{{ coletaData.hr_sai_entrega | hora_min }}</td>
							</tr>
							<tr v-if="coletaData.distancia_entrega != null">
								<td class="font-semibold">Distância percorrida</td>
								<td>{{ formatKmBR(coletaData.distancia_entrega) }} km</td>
							</tr>
							<tr v-if="coletaData.tempo_entrega != null">
								<td class="font-semibold">Tempo gasto</td>
								<td>{{ coletaData.tempo_entrega | hora_min}}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Detalhes" class="mb-base">
						<table>
							<tr v-if="coletaData.caract_coleta != null">
								<td class="font-semibold">Características</td>
								<td>{{ coletaData.caract_coleta }}</td>
							</tr>
							<tr v-if="coletaData.obs_coleta != null">
								<td class="font-semibold">Observações</td>
								<td>{{ coletaData.obs_coleta }}</td>
							</tr>
							<tr v-if="coletaData.volumes > 0 || coletaData.especie != null || coletaData.peso > 0">
								<td class="font-semibold">Carga</td>
								<td>{{ montarCarga(coletaData.volumes, coletaData.especie, coletaData.peso) }} {{ montarDimensao(coletaData.comp_carga, coletaData.larg_carga, coletaData.alt_carga) }}</td>
							</tr>
							<tr v-if="coletaData.sis_carga != null">
								<td class="font-semibold">Sistema de carga</td>
								<td>{{ coletaData.sis_carga | descr_sis_carga }}</td>
							</tr>
							<tr v-if="coletaData.tipo_frete != null">
								<td class="font-semibold">Tipo de Frete</td>
								<td>{{ coletaData.tipo_frete | descr_tipo_frete }}</td>
							</tr>
						</table>

						<vs-checkbox
							v-if="coletaData.entrega_consolidada == 'S'"
							class="mb-3"
							disabled
							v-model="coletaData.entrega_consolidada"
							vs-value="S"
						>Entrega consolidada</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Entrega consolidada</vs-checkbox>

						<vs-checkbox
							v-if="coletaData.receber_nf_frete == 'S'"
							class="mb-3"
							disabled
							v-model="coletaData.receber_nf_frete"
							vs-value="S"
						>Receber NF de frete junto com notas fiscais</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Receber NF de frete junto com notas fiscais</vs-checkbox>

						<vs-checkbox
							v-if="coletaData.nfs_comerciais == 'S'"
							class="mb-3"
							disabled
							v-model="coletaData.nfs_comerciais"
							vs-value="S"
						>Notas fiscais com fins comerciais</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Notas fiscais com fins comerciais</vs-checkbox>						

						<span v-if="$acl.check('admin') && habilitarFotoRomaneio(coletaData)">
							<div class="flex mb-3">
								<vs-switch class="mr-2" color="primary" v-model="aceitarFotoRom" />
								<label>Romaneio como documento de carga</label>
							</div>
						</span>
						<span v-else>
							<vs-checkbox
								v-if="coletaData.aceitar_foto_rom == 'S'"
								class="mb-3"
								disabled
								v-model="coletaData.aceitar_foto_rom"
								vs-value="S"
							>Romaneio como documento de carga</vs-checkbox>
							<vs-checkbox v-else class="mb-3" disabled>Romaneio como documento de carga</vs-checkbox>
						</span>						

						<vs-checkbox
							v-if="coletaData.ocultar_resumo == 'S'"
							class="mb-3"
							disabled
							v-model="coletaData.ocultar_resumo"
							vs-value="S"
						>Não mostrar a solicitação no Resumo do Dia</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Não mostrar a solicitação no Resumo do Dia</vs-checkbox>
					</vx-card>
				</div>
			</div>

			<div class="vx-row" v-if="$acl.check('admin')">
				<div class="vx-col w-full">
					<vx-card title="Sistema de Gestão" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Coleta exportada</td>
								<td v-if="coletaData.coleta_export != null">{{ coletaData.coleta_export | sim_nao }}</td>
								<td v-else>Não</td>
							</tr>
							<tr v-if="coletaData.dt_coleta_export != null">
								<td class="font-semibold">Exportação coleta</td>
								<td>{{ coletaData.dt_coleta_export | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Entrega exportada</td>
								<td v-if="coletaData.entrega_export != null">{{ coletaData.entrega_export | sim_nao }}</td>
								<td v-else>Não</td>
							</tr>
							<tr v-if="coletaData.dt_entrega_export != null">
								<td class="font-semibold">Exportação entrega</td>
								<td>{{ coletaData.dt_entrega_export | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>

			<div class="vx-row" v-if="$acl.check('admin')">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ coletaData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ coletaData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>
		</div>

		<vs-prompt
			class="max550"
			color="danger"
			@accept="cancelarColetaSemDesloc(promptDados)"
			:is-valid="promptObs != null && promptObs != '' && autorizarCancelamento != null"
			:title="promptDados.numero != null ? 'Cancelar coleta: ' + promptDados.numero : 'Cancelar coleta: ID ' + promptDados.id"
			accept-text="CONFIRMAR"
			cancel-text="FECHAR"
			:active.sync="activePrompt"
		>
			<div class="con-exemple-prompt">
				<div class="vx-row mb-3">
					<div class="vx-col w-full">
						<vs-input class="w-full" disabled label="Motivo" v-model="promptMotivo" />
					</div>
				</div>
				<div class="vx-row mb-3">
					<div class="vx-col w-full">
						<vs-input
							maxlength="255"
							data-vv-validate-on="blur"
							v-validate="'required'"
							name="obs"
							class="w-full"
							label="Observações"
							v-model="promptObs"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('obs')"
						>Informe o motivo do cancelamento desta coleta.</span>
					</div>
				</div>
				<div class="vx-row mb-3">
					<div class="vx-col">
						<vs-checkbox
							v-model="autorizarCancelamento"
							vs-value="S"
						>Eu {{ $store.state.AppActiveUser.displayName | first_name }}, estou ciente que este processo é irreversível.</vs-checkbox>
					</div>
				</div>
			</div>
		</vs-prompt>
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";

export default {
	mixins: [procsMixins],
	data() {
		return {
			coletaData: [],
			data_not_found: false,

			activePrompt: false,
			promptMotivo: "Cancelada: sem deslocamento",
			promptObs: null,
			promptDados: [],
			autorizarCancelamento: null,

			aceitarFotoRom: false,
		};
	},
	created() {
		this.showColeta();
	},
	watch: {
		aceitarFotoRom: function (newValue, oldValue) {
			if (this.habilitarFotoRomaneio(this.coletaData)) {
				let flag = newValue == true ? "S" : "N";
				if (flag != this.coletaData.aceitar_foto_rom) {
					this.coletaData.aceitar_foto_rom = flag;
					this.marcarAceitarFotoRom();
				}
			}
		},
	},
	methods: {
		async showColeta() {
			await this.$store
				.dispatch("coleta/showColeta", this.$route.params.id)
				.then((res) => {
					if (res.data.coleta.length > 0) {
						this.coletaData = res.data.coleta[0];
						this.aceitarFotoRom =
							this.coletaData.aceitar_foto_rom == "S"
								? true
								: false;
					} else {
						this.data_not_found = true;
					}
				})
				.catch((err) => {
					console.error(err);
				});
		},
		voltar() {
			this.$router.back();
		},
		url(id) {
			return "/coleta/" + id;
		},
		confirmar(dados) {
			this.autorizarCancelamento = null;
			this.promptDados = dados;
			this.activePrompt = true;
		},
		habilitarFotoRomaneio(dados) {
			let retorno = false;
			let arr = ["C0", "C1", "C2", "C3", "C4"];

			if (dados.coleta_fixa != "M") {
				if (arr.includes(dados.status)) {
					retorno = true;
				}
			}
			return retorno;
		},
		formatKmBR(value) {
			if (!value || isNaN(value)) return '0,000';
			return parseFloat(value).toLocaleString('pt-BR', {
				minimumFractionDigits: 3,
				maximumFractionDigits: 3
			});
		},
		async cancelarColetaSemDesloc(dados) {
			await this.$vs.loading({ scale: 0.5 });
			await this.$http
				.post(
					`api/CancelarColetaSemDesloc`,
					{
						coleta_id: dados.id,
						obs_nao_coleta: this.promptObs,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then(async (response) => {
					await this.$vs.loading.close();
					await this.showColeta();
					this.activePrompt = false;

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
				.catch(async (error) => {
					await this.$vs.loading.close();
				});
		},
		async marcarAceitarFotoRom() {
			try {
				await this.$http.put(
					`api/coleta/${this.$route.params.id}`,
					{
						id: this.$route.params.id,
						aceitar_foto_rom:
							this.aceitarFotoRom == true ? "S" : "N",
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				);
			} catch (error) {
				console.log("error", error);
			}
		},
	},
};
</script>

<style lang="scss">
#page-coleta-view {
	table {
		td {
			vertical-align: top;
			min-width: 180px;
			padding-bottom: 0.8rem;
			word-break: break-all;
		}
	}
}
.con-vs-dialog.max550 .vs-dialog {
	max-width: 550px;
}
</style>
