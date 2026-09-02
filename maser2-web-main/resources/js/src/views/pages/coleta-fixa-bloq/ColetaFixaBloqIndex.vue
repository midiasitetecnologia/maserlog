<template>
	<div>
		<div
			class="flex flex-wrap-reverse items-center data-list-btn-container"
			v-if="this.$route.query.coleta_fixa_id != null"
		>
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
						:noDataText="coletaFixaBloqData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="coletaFixaBloqData"
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ coletaFixaBloqData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : coletaFixaBloqData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="dt_ini">Data Inicial</vs-th>
							<vs-th sort-key="dt_fim">Data Final</vs-th>
							<vs-th sort-key="observ">Observação</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].dt_ini">
									<router-link
										:to="url(tr.id)"
										@click.stop.prevent
										class="text-inherit hover:underline"
									>{{data[indextr].dt_ini | moment("DD/MM/YYYY")}}</router-link>
								</vs-td>

								<vs-td :data="data[indextr].dt_fim">{{data[indextr].dt_fim | moment("DD/MM/YYYY")}}</vs-td>
								<vs-td :data="data[indextr].observ">{{data[indextr].observ}}</vs-td>

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
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false
		};
	},
	created() {
		this.getColetaFixaBloq();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		coletaFixaBloqData() {
			return this.$store.state.coletaFixaBloq.coletaFixaBloqData;
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
				: this.coletaFixaBloqData.length;
		}
	},
	methods: {
		async getColetaFixaBloq() {
			await this.$store
				.dispatch(
					"coletaFixaBloq/indexColetaFixaBloq",
					this.$route.query.coleta_fixa_id
				)
				.catch(err => {
					console.error(err);
				});
		},
		url(id) {
			return "/coleta-fixa-bloq/" + id;
		},
		editRecord(id) {
			this.$router
				.push("/coleta-fixa-bloq/" + id + "/edit")
				.catch(() => {});
		},
		addNewData() {
			this.$router
				.push(
					"/coleta-fixa-bloq/create/" +
						this.$route.query.coleta_fixa_id
				)
				.catch(() => {});
		},
		confirmDeleteRecord(id) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este bloqueio?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"coletaFixaBloq/destroyColetaFixaBloq",
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
				title: "Bloqueio de Coleta Fixa deletado",
				text:
					"O bloqueio da coleta fixa selecionado foi excluído com sucesso"
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
			await this.getColetaFixaBloq()
			await this.$vs.loading.close();
        }
	}
};
</script>

<style lang="scss" scoped>
</style>
