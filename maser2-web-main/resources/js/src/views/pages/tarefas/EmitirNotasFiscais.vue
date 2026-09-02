<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="coletasEmissaoNotasData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					:max-items="itemsPerPage"
					pagination
					search
					stripe
					:data="coletasEmissaoNotasData"
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
									>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ coletasEmissaoNotasData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : coletasEmissaoNotasData.length }} de {{ queriedItems }}</span>
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
						<vs-th sort-key="nome_cliente">Cliente</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Coleta / Entrega</vs-th>
						<vs-th sort-key="placa_coleta">Veículo</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="dt_efet_coleta">Data coleta</vs-th>						
						<vs-th sort-key="status">Status</vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :key="indextr" v-for="(tr, indextr) in data">

							<vs-td class="whitespace-no-wrap">								
								<vx-tooltip
									:color="data[indextr].cor_fonte"
									:text="data[indextr].nome_empresa"
									position="left"
								>
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
									</div>
								</vx-tooltip>								
							</vs-td>

							<vs-td>
								<span>{{data[indextr].nome_cliente | truncate(25)}} ({{data[indextr].cod_cliente}})</span>
							</vs-td>

							<vs-td>
								<div>
									<span
										style="font-size: 12px"
									>{{data[indextr].local_coleta | truncate(25)}} ({{data[indextr].cod_loc_coleta}})</span>
								</div>
								<div>
									<span
										style="font-size: 12px"
									>{{data[indextr].local_entrega | truncate(25)}} ({{data[indextr].cod_loc_entrega}})</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div>{{data[indextr].placa_coleta}}</div>
								<div v-if="data[indextr].descricao_tipo_veiculo != null">
									<span style="font-size: 12px; color:gray">{{data[indextr].descricao_tipo_veiculo}}</span>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">{{data[indextr].dt_efet_coleta | moment("DD MMM")}} {{data[indextr].hr_sai_coleta | hora_min }}</vs-td>							

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

export default {
	mixins: [procsMixins],
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false
		};
	},
	created() {
		this.retornarColetasEmissaoNotas();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		coletasEmissaoNotasData() {
			return this.$store.state.dashboard.coletasEmissaoNotasData;
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
				: this.coletasEmissaoNotasData.length;
		}
	},
	methods: {
		async retornarColetasEmissaoNotas() {
			await this.$store
				.dispatch("dashboard/retornarColetasEmissaoNotas")
				.catch(err => {
					console.error(err);
				});
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.retornarColetasEmissaoNotas();
			await this.$vs.loading.close();
		},
		url(id) {
			return "/coleta/" + id;
		}
	}
};
</script>

<style lang="scss" scoped>
</style>

