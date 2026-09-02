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
						:noDataText="wisdomData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="wisdomData"
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ wisdomData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : wisdomData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="texto">Texto</vs-th>
							<vs-th sort-key="texto">Fonte</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].id">
									{{data[indextr].id}}
								</vs-td>

								<vs-td :data="data[indextr].texto">
									{{data[indextr].texto | truncate(100)}}
								</vs-td>

								<vs-td :data="data[indextr].fonte">
									{{data[indextr].fonte}}
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
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	components: {},
	data() {
		return {
			selected: [],

			itemsPerPage: 10,
			isMounted: false
		};
	},
	created() {
		this.getWisdom();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		wisdomData() {
			return this.$store.state.wisdom.wisdomData;
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
				: this.wisdomData.length;
		}
	},
	methods: {
		async getWisdom() {
			await this.$store.dispatch("wisdom/indexWisdom").catch(err => {
				console.error(err);
			});
		},
		addNewData() {
			this.$router.push("/sabedoria/create/").catch(() => {});
		},
		editRecord(id) {
			this.$router
				.push("/sabedoria/" + id + "/edit")
				.catch(() => {});
		},
		confirmDeleteRecord(id) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta mensagem de sabedoria "${id}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"wisdom/destroyWisdom",
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
				title: "Sabedoria perdida",
				text: "A mensagem de sabedoria selecionada acaba de ser perdida."
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
			await this.getWisdom();
			await this.$vs.loading.close();			
		}
	}
};
</script>

<style lang="scss" scoped>
</style>
