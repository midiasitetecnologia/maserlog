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
						:noDataText="distanceMatrixData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="distanceMatrixData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<span class="font-semibold">Serviços de Cálculo de Distâncias e Rotas</span>						
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ distanceMatrixData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : distanceMatrixData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="id">ID</vs-th>
							<vs-th sort-key="api_key">Serviço</vs-th>
							<vs-th sort-key="api_key">Conta</vs-th>							
							<vs-th sort-key="api_usage">Solicitações</vs-th>
							<vs-th sort-key="api_usage">Limite</vs-th>
							<vs-th sort-key="api_usage">Prioridade</vs-th>
							<vs-th sort-key="active">Ativo</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].id">
									{{data[indextr].id}}
								</vs-td>

								<vs-td :data="data[indextr].api_key">
									{{serviceLabel(data[indextr].api_service) | truncate(20)}}
								</vs-td>

								<vs-td :data="data[indextr].api_account">
									{{data[indextr].api_account | truncate(40)}}
								</vs-td>								

								<vs-td :data="data[indextr].api_usage">
									{{data[indextr].api_usage | format_number}}
								</vs-td>

								<vs-td :data="data[indextr].api_limit">
									{{data[indextr].api_limit | format_number}}
								</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].api_priority">
									<vs-avatar v-if="data[indextr].api_priority == '1'" size="small" color="rgb(90, 225, 155)" text="1" />
									<vs-avatar v-if="data[indextr].api_priority == '2'" size="small" color="success" text="2" />
									<vs-avatar v-if="data[indextr].api_priority == '3'" size="small" color="rgb(255, 200, 100)" text="3" />
									<vs-avatar v-if="data[indextr].api_priority == '4'" size="small" color="warning" text="4" />
									<vs-avatar v-if="data[indextr].api_priority == '5'"	size="small" color="rgb(240, 145, 140)" text="5" />
									<vs-avatar v-if="data[indextr].api_priority == '6'" size="small" color="danger" text="6" />
								</vs-td>

								<vs-td :data="data[indextr].active">
									<vs-chip transparent :color="chipColor(data[indextr].active)">
										<span>{{ data[indextr].active | active_status }}</span>
									</vs-chip>
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
							</vs-tr>
						</template>
					</vs-table>

					<!-- Rodapé -->													
					<div class="invoice-hidden flex mt-6">
						<div class="flex items-center whitespace-no-wrap mr-2">																														
							<feather-icon icon="InfoIcon" svgClasses="h-4 w-4" class="mr-2" />
							<span>Ordem do algoritimo:</span>
						</div>
						<div class="flex items-center whitespace-no-wrap">																																					
							<span>Serviços ativos, prioridade (1 ~ 6), menor número de solicitações, e dentro do limite mensal de requisições.</span>
						</div>						
					</div>

				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
import labelsMixins from "@/mixins/labelsMixins";

export default {
	mixins: [labelsMixins],
	components: {},
	data() {
		return {
			selected: [],

			itemsPerPage: 10,
			isMounted: false
		};
	},
	created() {
		this.getDistanceMatrix();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		distanceMatrixData() {
			return this.$store.state.distanceMatrix.distanceMatrixData;
		},
		chipColor() {
			return value => {
				if (value === "S") return "success";
				else if (value === "N") return "danger";
				else if (value === "B") return "warning";
				else "primary";
			};
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
				: this.distanceMatrixData.length;
		}
	},
	methods: {
		async getDistanceMatrix() {
			await this.$store.dispatch("distanceMatrix/indexDistanceMatrix").catch(err => {
				console.error(err);
			});
		},
		addNewData() {
			this.$router.push("/distance-matrix/create/").catch(() => {});
		},
		editRecord(id) {
			this.$router
				.push("/distance-matrix/" + id + "/edit")
				.catch(() => {});
		},
		confirmDeleteRecord(id) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este registro "${id}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"distanceMatrix/destroyDistanceMatrix",
					parameters["id"]
				)
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
				title: "Chave de API deletada",
				text: "A chave de API selecionada foi excluída com sucesso"
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
			await this.$vs.loading({scale: 0.5})
			await this.getDistanceMatrix();
			await this.$vs.loading.close();			
		}
	}
};
</script>

<style lang="scss" scoped>
</style>
