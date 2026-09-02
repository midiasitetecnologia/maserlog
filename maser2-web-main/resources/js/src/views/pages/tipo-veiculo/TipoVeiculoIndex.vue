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
						:noDataText="tipoVeiculoData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="tipoVeiculoData"
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ tipoVeiculoData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : tipoVeiculoData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="codigo">Código</vs-th>
							<vs-th sort-key="descricao">Descrição</vs-th>
							<vs-th sort-key="classe">Classe</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="classe">Duração Atend.</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="classe">Tempo Pavilhão</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].codigo">{{data[indextr].codigo}}</vs-td>
								<vs-td :data="data[indextr].descricao">
									<router-link
										:to="url(tr.codigo)"
										@click.stop.prevent
										class="text-inherit hover:underline"
									>{{data[indextr].descricao}}</router-link>
								</vs-td>

								<vs-td :data="data[indextr].classe">
									{{data[indextr].classe | classeLabel}}
								</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].dur_prev_atend">
									<span v-if="data[indextr].classe != 'C'">{{data[indextr].dur_prev_atend | hora_min}}</span>
								</vs-td>

								<vs-td class="whitespace-no-wrap" :data="data[indextr].tempo_desloc_pavilhao">
									<span v-if="data[indextr].classe != 'C'">{{data[indextr].tempo_desloc_pavilhao | hora_min}}</span>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<feather-icon
										icon="EditIcon"
										svgClasses="w-5 h-5 hover:text-primary stroke-current cursor-pointer"
										@click="editRecord(tr.codigo)"
									/>
									<feather-icon
										icon="TrashIcon"
										svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
										class="ml-2"
										@click="confirmDeleteRecord(tr.codigo, tr.descricao)"
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
		this.getTipoVeiculo();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		tipoVeiculoData() {
			return this.$store.state.tipoVeiculo.tipoVeiculoData;
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
				: this.tipoVeiculoData.length;
		}
	},
	filters: {
		classeLabel(str) {
			if (str === "R") return "Carreta";
			else if (str === "C") return "Cavalo";
			else if (str === "M") return "Monobloco";
			else return str;
		}
	},
	methods: {
		async getTipoVeiculo() {
			await this.$store.dispatch("tipoVeiculo/indexTipoVeiculo").catch(err => {
				console.error(err);
			});
		},
		addNewData() {
			this.$router.push("/tipo-veiculo/create/").catch(() => {});
		},
		editRecord(codigo) {
			this.$router
				.push("/tipo-veiculo/" + codigo + "/edit")
				.catch(() => {});
		},
		confirmDeleteRecord(codigo, descricao) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este tipo de veículo "${codigo} - ${descricao}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { codigo: codigo }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch(
					"tipoVeiculo/destroyTipoVeiculo",
					parameters["codigo"]
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
				title: "Tipo de veículo deletado",
				text: "O tipo de veículo selecionado foi excluído com sucesso"
			});
		},
		showDeleteFail(msg) {
			this.$vs.notify({
				color: "danger",
				title: "Ops!",
				text: msg
			});
		},
		url(codigo) {
			return "/tipo-veiculo/" + codigo;
		},
		async refresh() {
			await this.$vs.loading({scale: 0.5})
			await this.getTipoVeiculo();
			await this.$vs.loading.close();			
		}
	}
};
</script>

<style lang="scss" scoped>
</style>
