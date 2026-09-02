<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="corrigirCadData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					:max-items="itemsPerPage"
					pagination
					search
					stripe
					:data="corrigirCadData"
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
									>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ corrigirCadData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : corrigirCadData.length }} de {{ queriedItems }}</span>
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
						<vs-th sort-key="numero">Solicitação</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Local Coleta</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Entrega</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Local Entrega</vs-th>
						<vs-th sort-key="status">Status</vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :key="indextr" v-for="(tr, indextr) in data">
							<vs-td class="whitespace-no-wrap">
								<div>
									<div class="flex items-center text-center">
										<router-link
											v-if="data[indextr].numero != null"
											class="text-inherit hover:underline"
											target="_blank"
											:to="url(tr.coleta_id)"
										>{{data[indextr].numero}}</router-link>
										<router-link
											v-else
											:to="url(tr.coleta_id)"
											target="_blank"
											class="text-inherit hover:underline"
										>ID: {{data[indextr].coleta_id}}</router-link>
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

							<vs-td class="whitespace-no-wrap">
								<span
									v-if="data[indextr].dt_prev_coleta == data[indextr].data_cad"
								>{{data[indextr].hr_prev_coleta | hora_min}}</span>
								<span
									v-else
								>{{data[indextr].dt_prev_coleta | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min}}</span>
							</vs-td>

							<vs-td>
								<div>
									<router-link
										:to="'/cliente/' + data[indextr].id_local_coleta"
										target="_blank"
										class="text-inherit hover:underline"
									>{{data[indextr].local_coleta | truncate(25)}}</router-link>
								</div>
								<div v-if="data[indextr].erros_coleta != null">
									<span class="text-danger" style="font-size: 12px">{{data[indextr].erros_coleta}}</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<span
									v-if="data[indextr].dt_prev_entrega == data[indextr].data_cad"
								>{{data[indextr].hr_prev_entrega | hora_min}}</span>
								<span
									v-else
								>{{data[indextr].dt_prev_entrega | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min}}</span>
							</vs-td>

							<vs-td>
								<div>
									<router-link
										:to="'/cliente/' + data[indextr].id_local_entrega"
										target="_blank"
										class="text-inherit hover:underline"
									>{{data[indextr].local_entrega | truncate(25)}}</router-link>
								</div>
								<div v-if="data[indextr].erros_entrega != null">
									<span class="text-danger" style="font-size: 12px">{{data[indextr].erros_entrega}}</span>
								</div>
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
						</vs-tr>
					</template>
				</vs-table>
			</vx-card>
		</div>
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	mixins: [procsMixins],
	components: {
		ColFixa
	},
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false
		};
	},
	created() {
		this.retornarClientesColetasCadIncomp();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		corrigirCadData() {
			return this.$store.state.dashboard.corrigirCadData;
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
				: this.corrigirCadData.length;
		}
	},
	methods: {
		async retornarClientesColetasCadIncomp() {
			await this.$store
				.dispatch("dashboard/retornarClientesColetasCadIncomp")
				.catch(err => {
					console.error(err);
				});
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.retornarClientesColetasCadIncomp();
			await this.$vs.loading.close();
		},
		url(id) {
			return "/coleta/" + id;
		},
		exibirDia(data) {
			//Se for igual a Hoje (Mesmo dia) não vamos exibir.
			if (data === this.dataAtual) return "";
			else return data;
		},
	}
};
</script>

<style lang="scss" scoped>
</style>

