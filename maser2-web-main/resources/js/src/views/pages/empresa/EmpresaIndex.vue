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
						:noDataText="empresaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="empresaData"
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ empresaData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : empresaData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="nome">Nome</vs-th>
							<vs-th sort-key="sigla">Sigla</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td :data="data[indextr].codigo">{{data[indextr].codigo}}</vs-td>
								<vs-td :data="data[indextr].nome">
									<router-link
										:to="url(tr.codigo)"
										@click.stop.prevent
										class="text-inherit hover:underline"
									>{{data[indextr].nome}}</router-link>
								</vs-td>
								<vs-td :data="data[indextr].sigla">
									<chip-emp
										:cor_fundo="data[indextr].cor_fundo"
										:cor_fonte="data[indextr].cor_fonte"
										:sigla="data[indextr].sigla"
									/>
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
										@click="confirmDeleteRecord(tr.codigo, tr.nome)"
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
import ChipEmp from "@/components/rgsoft/ChipEmp.vue";

export default {
	components: {
		ChipEmp
	},
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false
		};
	},
	created() {
		this.getEmpresa();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		empresaData() {
			return this.$store.state.empresa.empresaData;
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
				: this.empresaData.length;
		}
	},
	methods: {
		async getEmpresa() {
			await this.$store.dispatch("empresa/indexEmpresa").catch(err => {
				console.error(err);
			});
		},
		addNewData() {
			this.$router.push("/empresa/create/").catch(() => {});
		},
		editRecord(codigo) {
			this.$router.push("/empresa/" + codigo + "/edit").catch(() => {});
		},
		confirmDeleteRecord(codigo, nome) {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta empresa "${codigo} - ${nome}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { codigo: codigo }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("empresa/destroyEmpresa", parameters["codigo"])
				.then(() => {
					this.showDeleteSuccess();
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Empresa deletada",
				text: "A empresa selecionada foi excluída com sucesso"
			});
		},
		url(codigo) {
			return "/empresa/" + codigo;
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.getEmpresa();
			await this.$vs.loading.close();
		}
	}
};
</script>

<style lang="scss" scoped>
</style>
