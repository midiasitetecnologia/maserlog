<template>
	<div>
		<div v-if="podeSolicitarColetas == false">
			<bloqueio-solicitacoes />
		</div>
		<div v-show="podeSolicitarColetas == true">
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
							:noDataText="coletaWebData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
							v-model="selected"
							:max-items="itemsPerPage"
							pagination
							search
							stripe
							:data="coletaWebData"
						>
							<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
								<div class="flex mb-1">
									<!-- div para alinhar o dropdown a direita. Se tiver algum filtro simples de uma linha, pode ser colocado aqui.
									Ex: Filtro de ativos dos motoristas-->
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
											>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ coletaWebData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : coletaWebData.length }} de {{ queriedItems }}</span>
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
								<vs-th class="whitespace-no-wrap" sort-key="numero">Solicitação</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="dt_prev_coleta">Prev. Coleta</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Local de Coleta</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Entrega</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Local de Entrega</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="descricao_tipo_veiculo">Tipo Veículo</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="placa_coleta">Placa</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="status">Status</vs-th>
								<!-- Ações -->
								<vs-th></vs-th>
								<vs-th></vs-th>
							</template>

							<template slot-scope="{data}">
								<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
									<vs-td class="whitespace-no-wrap">
										<div>
											<div class="flex items-center text-center">
												<router-link
													v-if="data[indextr].numero != null"
													class="text-inherit hover:underline"
													target="_blank"
													:to="url(tr.id)"
												>{{data[indextr].numero}}</router-link>
												<router-link
													v-else
													:to="url(tr.id)"
													target="_blank"
													class="text-inherit hover:underline"
												>ID: {{data[indextr].id}}</router-link>
												<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
											</div>
										</div>
										<div>
											<vx-tooltip
												:color="data[indextr].cor_fonte"
												:text="data[indextr].nome_empresa"
												position="left"
											>
												<span
													style="font-size: 12px; color:gray"
												>{{exibirDia(data[indextr].data_cad) | moment("DD MMM") }} {{data[indextr].hora_cad | hora_min }}</span>
											</vx-tooltip>
										</div>
									</vs-td>

									<vs-td
										class="whitespace-no-wrap"
										:style="emAtraso(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta)"
									>
										<div>{{exibirDia(data[indextr].dt_prev_coleta) | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min }}</div>
										<div
											style="font-size: 12px; font-weight: 500;"
										>{{calcTempoRestante(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta)}}</div>
									</vs-td>

									<vs-td>{{data[indextr].local_coleta}}</vs-td>

									<vs-td class="whitespace-no-wrap">
										<div
											v-if="data[indextr].dt_prev_entrega != null"
											:style="emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)"
										>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
										<div v-if="data[indextr].entrega_urgente == 'S'">
											<span class="badge_vermelho">urgente</span>
										</div>
										<div
											v-else
											style="font-size: 12px; font-weight: 500;"
										>{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}</div>
									</vs-td>

									<vs-td>										
										<div>
											{{data[indextr].local_entrega}}
										</div>								
										<div class="flex items-center whitespace-no-wrap">								
											<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza">reentrega</span>
										</div>
									</vs-td>

									<vs-td>{{data[indextr].descricao_tipo_veiculo}}</vs-td>

									<vs-td class="whitespace-no-wrap">{{data[indextr].placa_coleta}}</vs-td>

									<vs-td class="whitespace-no-wrap">
										<div class="flex items-center">
											<vs-avatar
												size="small"
												:color="corStatus(data[indextr].status)"
												:text="inicialStatus(data[indextr].status)"
											/>
											<div>
												<div>
													<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
												</div>
												<div v-if="(data[indextr].mot_nao_coleta == '03')">
													<span class="ml-1 font-semibold text-warning" style="font-size: 12px;">Devolvida</span>
												</div>
											</div>
										</div>
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
											@click="confirmDeleteRecord(tr.id)"
										/>
									</vs-td>

									<vs-td class="whitespace-no-wrap">
										<vx-tooltip
											v-if="$acl.check('admin') && (data[indextr].coleta_pos > 0 || data[indextr].coleta_log > 0)"
											text="Auditoria"
											position="left"
										>
											<feather-icon
												icon="FileTextIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="auditoria(data[indextr].id)"
											/>
										</vx-tooltip>
									</vs-td>
								</vs-tr>
							</template>
						</vs-table>
					</vx-card>
				</div>
			</div>

			<auditoria-pop-up />
		</div>
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";
import AuditoriaPopUp from "@/components/rgsoft/AuditoriaPopUp.vue";
import ColFixa from "@/components/rgsoft/ColFixa.vue";
import BloqueioSolicitacoes from "./BloqueioSolicitacoes.vue";

export default {
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: {
		AuditoriaPopUp,
		ColFixa,
		BloqueioSolicitacoes,
	},
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false,

			//Inicializar com null, assim não da o efeito de "Piscação" com os dados ocultados.
			//A variável trata oque pode exibir ou não.
			podeSolicitarColetas: null,
			clienteFromUserData: [],
		};
	},
	async created() {
		if (this.$store.state.AppActiveUser.userRole == "cliente") {
			await this.lerClienteFromUser();
		} else {
			this.podeSolicitarColetas = true;
		}

		this.getColetaWeb();
		this.getDataAtual();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		coletaWebData() {
			return this.$store.state.coleta.coletaWebData;
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
				: this.coletaWebData.length;
		},
	},
	methods: {
		async getColetaWeb() {
			await this.$store.dispatch("coleta/indexColetaWeb").catch((err) => {
				console.error(err);
			});
		},
		getDataAtual() {
			this.$store.dispatch("dataAtual/getDataAtual").catch((err) => {
				console.error(err);
			});
		},
		async lerClienteFromUser() {
			await this.$store
				.dispatch(
					"cliente/lerClienteFromUser",
					this.$store.state.AppActiveUser.uid
				)
				.then((res) => {
					if (res.data.cliente.length > 0) {
						this.clienteFromUserData = res.data.cliente[0];
						this.podeSolicitarColetas =
							this.clienteFromUserData.solicitar_coletas == "S"
								? true
								: false;
					}
				})
				.catch((err) => {
					console.error(err);
				});
		},
		url(id) {
			return "/coleta/" + id;
		},
		editRecord(id) {
			this.$router.push("/coleta-web/" + id + "/edit").catch(() => {});
		},
		addNewData() {
			this.$router.push("/coleta-web/create").catch(() => {});
		},
		confirmDeleteRecord(id) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta coleta "${id}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id },
			});
		},
		deleteRecord: function (parameters) {
			this.$store
				.dispatch("coleta/destroyColeta", parameters["id"])
				.then((res) => {
					if (res.data.status) {
						this.showDeleteSuccess();
					} else {
						this.showDeleteFail(res.data.erros["message"][0]);
					}
				})
				.catch((err) => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Coleta deletada",
				text: "A coleta selecionada foi excluída com sucesso",
			});
		},
		showDeleteFail(msg) {
			this.$vs.notify({
				time: 10000,
				color: "danger",
				title: "Ops!",
				text: msg,
			});
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getColetaWeb();
			await this.$vs.loading.close();
		},
	},
};
</script>

<style lang="scss" scoped>
</style>
