<template>
	<div id="page-coleta-fixa-view">
		<vs-alert color="danger" title="Coleta Fixa não encontrada" :active.sync="data_not_found">
			<span>Registro de coleta fixa com id: {{$route.params.id}} não encontrada.</span>
			<span>
				<span>Verifique todas as</span>
				<router-link :to="{name:'coleta-fixa'}" class="text-inherit underline">Coletas Fixas</router-link>
			</span>
		</vs-alert>

		<br v-show="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Contrato" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">ID</td>
								<td>#{{ coletaFixaData.id }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Empresa</td>
								<td>{{ coletaFixaData.nome_empresa }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Cliente</td>
								<td>{{ coletaFixaData.nome }} #{{ coletaFixaData.cod_cliente }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Tipo de coleta</td>
								<td>{{this.tipoColetaLabel(coletaFixaData.tipo_coleta)}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Vigência</td>
								<td>{{ coletaFixaData.dt_ini | moment("DD MMM YYYY") }} a {{ coletaFixaData.dt_fim | moment("DD MMM YYYY") }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Situação</td>
								<td>
									<vs-chip
										:color="situacaoColor(coletaFixaData.cont_cancel, coletaFixaData.dt_ini, coletaFixaData.dt_fim)"
									>{{situacao(coletaFixaData.cont_cancel, coletaFixaData.dt_ini, coletaFixaData.dt_fim)}}</vs-chip>&nbsp;
									<span
										v-if="coletaFixaData.cont_cancel == 'S'"
									>Contrato cancelado em {{coletaFixaData.dt_cancel}}</span>
								</td>
							</tr>
						</table>

						<br />
						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'coleta-fixa-edit', params: { id: $route.params.id }}"
							>Editar</vs-button>
							<vs-button
								type="border"
								color="danger"
								icon-pack="feather"
								icon="icon-trash"
								class="mr-4"
								@click="confirmDeleteRecord"
							>Excluir</vs-button>
							<vs-button type="border" color="danger" @click="voltar()">Voltar</vs-button>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full">
					<vx-card title="Coleta" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Local de coleta</td>
								<td>{{ coletaFixaData.nome_coleta }} #{{coletaFixaData.cod_loc_coleta}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Local de entrega</td>
								<td>{{ coletaFixaData.nome_entrega }} #{{coletaFixaData.cod_loc_entrega}}</td>
							</tr>
							<tr v-if="coletaFixaData.caract_coleta != null">
								<td class="font-semibold">Características</td>
								<td>{{ coletaFixaData.caract_coleta }}</td>
							</tr>
							<tr v-if="coletaFixaData.placa_coleta != null">
								<td class="font-semibold">Placa</td>
								<td>{{ coletaFixaData.placa_coleta }}</td>
							</tr>
							<tr v-if="coletaFixaData.cod_tipo_veiculo != null">
								<td class="font-semibold">Tipo de veículo</td>
								<td>{{ coletaFixaData.descricao_veiculo }}</td>
							</tr>
							<tr v-if="coletaFixaData.sis_carga != null">
								<td class="font-semibold">Sistema de carga</td>
								<td>{{ coletaFixaData.sis_carga | descr_sis_carga }}</td>
							</tr>
							<tr v-if="coletaFixaData.tipo_frete != null">
								<td class="font-semibold">Tipo de Frete</td>
								<td>{{ coletaFixaData.tipo_frete | descr_tipo_frete }}</td>
							</tr>
						</table>

						<vs-checkbox
							v-if="coletaFixaData.receber_nf_frete == 'S'"
							class="mb-3"
							disabled
							v-model="coletaFixaData.receber_nf_frete"
							vs-value="S"
						>Receber NF de frete junto com notas fiscais</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Receber NF de frete junto com notas fiscais</vs-checkbox>

						<vs-checkbox
							v-if="coletaFixaData.aceitar_foto_rom == 'S'"
							class="mb-3"
							disabled
							v-model="coletaFixaData.aceitar_foto_rom"
							vs-value="S"
						>Romaneio como documento de carga</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Romaneio como documento de carga</vs-checkbox>

						<vs-checkbox
							v-if="coletaFixaData.autoriza_coleta == 'S'"
							class="mb-3"
							disabled
							v-model="coletaFixaData.autoriza_coleta"
							vs-value="S"
						>Autorizar automaticamente a coleta quando tiver um veículo definido</vs-checkbox>
						<vs-checkbox v-else class="mb-3" disabled>Autorizar automaticamente a coleta quando tiver um veículo definido</vs-checkbox>

						<vs-checkbox
							v-if="coletaFixaData.ocultar_resumo == 'S'"							
							disabled
							v-model="coletaFixaData.ocultar_resumo"
							vs-value="S"
						>Não mostrar a solicitação no Resumo do Dia</vs-checkbox>
						<vs-checkbox v-else disabled>Não mostrar a solicitação no Resumo do Dia</vs-checkbox>						
					</vx-card>
				</div>
			</div>

			<div class="vx-col w-full">
				<vx-card title="Dias e horários" class="mb-base">
					<table>
						<tr>
							<td class="font-semibold">Dias</td>
							<td>
								<vs-chip v-if="coletaFixaData.segunda == 'S'" transparent color="success">
									<span>{{coletaFixaData.segunda | segundaLabel}}</span>
								</vs-chip>
								<vs-chip v-if="coletaFixaData.terca == 'S'" transparent color="success">
									<span>{{coletaFixaData.terca | tercaLabel}}</span>
								</vs-chip>
								<vs-chip v-if="coletaFixaData.quarta == 'S'" transparent color="success">
									<span>{{coletaFixaData.quarta | quartaLabel}}</span>
								</vs-chip>
								<vs-chip v-if="coletaFixaData.quinta == 'S'" transparent color="success">
									<span>{{coletaFixaData.quinta | quintaLabel}}</span>
								</vs-chip>
								<vs-chip v-if="coletaFixaData.sexta == 'S'" transparent color="success">
									<span>{{coletaFixaData.sexta | sextaLabel}}</span>
								</vs-chip>
								<vs-chip v-if="coletaFixaData.sabado == 'S'" transparent color="success">
									<span>{{coletaFixaData.sabado | sabadoLabel}}</span>
								</vs-chip>
							</td>
						</tr>
						<tr v-if="coletaFixaData.hr_prev_coleta != null">
							<td class="font-semibold">Hora da coleta</td>
							<td>{{ coletaFixaData.hr_prev_coleta | hora_min }}</td>
						</tr>
						<tr v-if="coletaFixaData.hr_prev_entrega != null">
							<td class="font-semibold">Hora da entrega</td>
							<td>{{ coletaFixaData.hr_prev_entrega | hora_min }}</td>
						</tr>
						<tr>
							<td class="font-semibold">Dois turnos</td>
							<td>{{coletaFixaData.dois_turnos | doisTurnosLabel}}</td>
						</tr>
						<tr>
							<td class="font-semibold">Horários</td>
							<td>
								<span
									v-if="coletaFixaData.t1_hora_ini != null"
								>{{coletaFixaData.t1_hora_ini | hora_min }} às {{coletaFixaData.t1_hora_fim | hora_min}}</span>
								<span
									v-if="coletaFixaData.dois_turnos == 'S'"
								>| {{coletaFixaData.t2_hora_ini | hora_min }} às {{coletaFixaData.t2_hora_fim | hora_min }}</span>
							</td>
						</tr>
					</table>
				</vx-card>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ coletaFixaData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ coletaFixaData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import labelsMixins from "@/mixins/labelsMixins";
import ChipEmp from "@/components/rgsoft/ChipEmp.vue";
export default {
	mixins: [labelsMixins],
	components: {
		ChipEmp
	},
	data() {
		return {
			coletaFixaData: [],
			data_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch("coletaFixa/showColetaFixa", this.$route.params.id)
			.then(res => {
				if (res.data.coletaFixa.length > 0) {
					this.coletaFixaData = res.data.coletaFixa[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
		this.getDataAtual();
	},
	filters: {
		doisTurnosLabel(str) {
			if (str === "S") return "Sim";
			else if (str === "N") return "Não";
			else return str;
		},
		segundaLabel(str) {
			if (str === "S") return "Seg";
		},
		tercaLabel(str) {
			if (str === "S") return "Ter";
		},
		quartaLabel(str) {
			if (str === "S") return "Qua";
		},
		quintaLabel(str) {
			if (str === "S") return "Qui";
		},
		sextaLabel(str) {
			if (str === "S") return "Sex";
		},
		sabadoLabel(str) {
			if (str === "S") return "Sáb";
		}
	},
	computed: {
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		}
	},
	methods: {
		getDataAtual() {
			this.$store.dispatch("dataAtual/getDataAtual").catch(err => {
				console.error(err);
			});
		},
		voltar() {
			this.$router.back();
		},
		situacao(cont_cancel, dt_ini, dt_fim) {
			if (cont_cancel != "S") {
				if (this.dataAtual >= dt_ini && this.dataAtual <= dt_fim) {
					return "Ativo";
				}

				if (this.dataAtual < dt_ini) {
					return "Não iniciado";
				}

				if (this.dataAtual > dt_fim) {
					return "Finalizado";
				}
			}

			if (cont_cancel == "S") {
				return "Cancelado";
			}
		},
		situacaoColor(cont_cancel, dt_ini, dt_fim) {
			if (cont_cancel != "S") {
				if (this.dataAtual >= dt_ini && this.dataAtual <= dt_fim) {
					return "primary";
				}

				if (this.dataAtual < dt_ini) {
					return "warning";
				}

				if (this.dataAtual > dt_fim) {
					return "success";
				}
			}

			if (cont_cancel == "S") {
				return "danger";
			}
		},
		confirmDeleteRecord() {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta coleta fixa "${this.coletaFixaData.id} - ${this.coletaFixaData.nome}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: this.coletaFixaData.id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("coletaFixa/destroyColetaFixa", parameters["id"])
				.then(res => {
					if (res.data.status) {
						this.showDeleteSuccess();
						this.$router.push("/coleta-fixa/").catch(() => {});
					} else {
						this.showDeleteFail(res.data.erros["message"][0]);
					}
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Coleta Fixa deletada",
				text: "A coleta fixa selecionada foi excluída com sucesso"
			});
		},
		showDeleteFail(msg) {
			this.$vs.notify({
				color: "danger",
				title: "Ops!",
				text: msg
			});
		}
	}
};
</script>

<style lang="scss">
#page-coleta-fixa-view {
	table {
		td {
			vertical-align: top;
			min-width: 140px;
			padding-bottom: 0.8rem;
			word-break: break-all;
		}
	}
}
</style>
