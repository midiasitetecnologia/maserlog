<template>
	<div>
		<div class="flex flex-wrap-reverse items-center data-list-btn-container">
			<div
				class="btn-add-new p-3 mb-4 mr-4 rounded-lg cursor-pointer flex items-center justify-center text-lg font-medium text-base text-primary border border-solid border-primary"
				@click="addNewData"
			>
				<feather-icon icon="PlusIcon" svgClasses="h-4 w-4" />
				<span class="ml-2 text-base text-primary">Adicionar</span>
			</div>
		</div>

		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="coletaFixaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="coletaFixaData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<vs-switch color="primary" v-model="contratosAtivos" />&nbsp;
								<label>Contratos ativos</label>
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ coletaFixaData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : coletaFixaData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="sigla">Empresa</vs-th>
							<vs-th sort-key="id">ID</vs-th>
							<vs-th sort-key="nome">Cliente</vs-th>
							<vs-th sort-key="local_coleta">Coleta / Entrega</vs-th>
							<vs-th sort-key="placa_coleta">Veículo</vs-th>
							<vs-th>Dias e horários</vs-th>
							<vs-th>Status</vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td>
									<chip-emp
										:cor_fundo="data[indextr].cor_fundo"
										:cor_fonte="data[indextr].cor_fonte"
										:sigla="data[indextr].sigla"
									/>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<router-link :to="url(tr.id)" @click.stop.prevent class="text-inherit hover:underline">
										<span>{{data[indextr].id}}</span>
										<col-fixa :coleta_fixa="data[indextr].tipo_coleta" />
									</router-link>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>{{data[indextr].nome | truncate(25)}}</div>
									<div>
										<span
											style="font-size: 12px; color:gray"
										>{{data[indextr].dt_ini | moment("DD MMM YY") }} a {{data[indextr].dt_fim | moment("DD MMM YY") }}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>
										<span style="font-size: 12px; color:gray">{{data[indextr].local_coleta | truncate(25)}}</span>
									</div>
									<div>
										<span style="font-size: 12px; color:gray">{{data[indextr].local_entrega | truncate(25)}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div v-if="data[indextr].placa_coleta != null">{{data[indextr].placa_coleta}}</div>
									<div v-if="data[indextr].descricao_tipo_veiculo != null">
										<span style="font-size: 12px; color:gray">{{data[indextr].descricao_tipo_veiculo}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>
										<span v-if="data[indextr].segunda == 'S'">
											<span>{{data[indextr].segunda | segundaLabel}}</span>
										</span>
										<span v-if="data[indextr].terca == 'S'">
											<span>{{data[indextr].terca | tercaLabel}}</span>
										</span>
										<span v-if="data[indextr].quarta == 'S'">
											<span>{{data[indextr].quarta | quartaLabel}}</span>
										</span>
										<span v-if="data[indextr].quinta == 'S'">
											<span>{{data[indextr].quinta | quintaLabel}}</span>
										</span>
										<span v-if="data[indextr].sexta == 'S'">
											<span>{{data[indextr].sexta | sextaLabel}}</span>
										</span>
										<span v-if="data[indextr].sabado == 'S'">
											<span>{{data[indextr].sabado | sabadoLabel}}</span>
										</span>
									</div>
									<div>
										<span v-if="data[indextr].tipo_coleta == 'C'">
											<span
												style="font-size: 12px; color:gray"
											>{{data[indextr].t1_hora_ini | hora_min}}-{{data[indextr].t1_hora_fim | hora_min}}</span>
											<span
												v-if="data[indextr].dois_turnos == 'S'"
												class="ml-2"
												style="font-size: 12px; color:gray"
											>{{data[indextr].t2_hora_ini | hora_min}}-{{data[indextr].t2_hora_fim | hora_min}}</span>
										</span>
										<span
											v-else
											style="font-size: 12px; color:gray"
										>{{data[indextr].hr_prev_coleta | hora_min}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<vs-chip
										:color="situacaoColor(data[indextr].cont_cancel, data[indextr].dt_ini, data[indextr].dt_fim)"
									>{{situacao(data[indextr].cont_cancel, data[indextr].dt_ini, data[indextr].dt_fim)}}</vs-chip>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<feather-icon
										icon="EditIcon"
										svgClasses="w-5 h-5 hover:text-primary stroke-current cursor-pointer"
										@click="editRecord(tr.id)"
									/>
									<feather-icon
										icon="TrashIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										class="ml-2"
										@click="confirmDeleteRecord(tr.id, tr.nome)"
									/>
								</vs-td>

								<vs-td align="center">
									<router-link
										v-if="data[indextr].nro_bloqs > 0"
										:to="{ name: 'coleta-fixa-bloq', query: { coleta_fixa_id: tr.id }}"
									>
										<vx-tooltip text="Bloqueios" position="top">
											<vs-button color="primary" type="flat" icon-pack="feather" icon="icon-lock">
												<span class="mr-1">{{data[indextr].nro_bloqs}}</span>
												<span v-if="data[indextr].nro_bloqs_fut > 0">({{data[indextr].nro_bloqs_fut}})</span>
											</vs-button>
										</vx-tooltip>
									</router-link>
									<router-link
										v-else
										:to="{ name: 'coleta-fixa-bloq-create', params: { coleta_fixa_id: tr.id }}"
									>
										<vx-tooltip text="Adicionar bloqueios" position="top">
											<vs-button color="primary" type="flat" icon-pack="feather" icon="icon-lock">+</vs-button>
										</vx-tooltip>
									</router-link>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
import ChipEmp from "@/components/rgsoft/ChipEmp.vue";
import ColFixa from "@/components/rgsoft/ColFixa.vue";
export default {
	components: {
		ChipEmp,
		ColFixa
	},
	data() {
		return {
			contratosAtivos: this.$store.state.coletaFixa.coletaFixaFiltros
				.contratosAtivos,

			selected: [],
			itemsPerPage: 10,
			isMounted: false
		};
	},
	async created() {
		await this.getColetaFixa();
		await this.getDataAtual();
	},
	mounted() {
		this.isMounted = true;
	},
	watch: {
		contratosAtivos: function(newValue, oldValue) {
			this.refresh();
		}
	},
	filters: {
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
		coletaFixaData() {
			return this.$store.state.coletaFixa.coletaFixaData;
		},
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		},
		currentPage() {
			if (this.isMounted) {
				return this.$refs.table.currentx;
			}
			return 0;
		},
		queriedItems() {
			return this.$refs.table
				? this.$refs.table.queriedResults.length
				: this.coletaFixaData.length;
		}
	},
	methods: {
		async getColetaFixa() {
			await this.$store
				.dispatch(
					"coletaFixa/indexColetaFixa",
					this.$store.state.coletaFixa.coletaFixaFiltros
				)
				.catch(err => {
					console.error(err);
				});
		},
		getDataAtual() {
			this.$store.dispatch("dataAtual/getDataAtual").catch(err => {
				console.error(err);
			});
		},
		url(id) {
			return "/coleta-fixa/" + id;
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
		editRecord(id) {
			this.$router.push("/coleta-fixa/" + id + "/edit").catch(() => {});
		},
		addNewData() {
			this.$router.push("/coleta-fixa/create").catch(() => {});
		},
		confirmDeleteRecord(id, nome) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta coleta fixa "${id} - ${nome}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("coletaFixa/destroyColetaFixa", parameters["id"])
				.then(res => {
					if (res.data.status) {
						this.showDeleteSuccess();
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
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			const payload = {
				contratosAtivos: this.contratosAtivos
			};
			await this.$store.commit(
				"coletaFixa/SET_COLETA_FIXA_FILTROS",
				payload
			);
			await this.getColetaFixa();
			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss" scoped>
</style>
