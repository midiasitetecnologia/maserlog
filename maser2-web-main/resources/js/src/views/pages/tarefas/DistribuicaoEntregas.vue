<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="coletasMDRealizadasData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
					v-model="selected"
					:max-items="itemsPerPage"
					pagination
					search
					stripe
					:data="coletasMDRealizadasData"
				>
					<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
						<div class="flex mb-1">
							<label class="font-semibold">Coletas Realizadas (Multi-Destinos)</label>
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
									>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ coletasMDRealizadasData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : coletasMDRealizadasData.length }} de {{ queriedItems }}</span>
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
						<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Coleta</vs-th>
						<vs-th sort-key="qtde_notas">Notas</vs-th>
						<vs-th class="whitespace-no-wrap" sort-key="qtde_notas_distrib">Notas Distribuir</vs-th>
						<vs-th></vs-th>
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
								<div>
									<span>{{data[indextr].local_coleta}}</span>
								</div>
								<div>
									<div class="flex items-center">
										<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_coleta}}</span>
										<span
											style="font-size: 12px; color:gray"
										>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}</span>
										<feather-icon
											v-if="data[indextr].hr_sai_coleta != null"
											class="ml-1"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>
									</div>
								</div>
							</vs-td>

							<vs-td>{{data[indextr].qtde_notas}}</vs-td>

							<vs-td>
								<div
									class="flex items-center text-center"
									v-if="vueIgualZeroNull(data[indextr].qtde_notas_distrib)"
								>
									<span style="font-size: 12px;">Distribuída</span>
									<feather-icon
										v-if="data[indextr].hr_sai_coleta != null"
										class="ml-1"
										icon="CheckCircleIcon"
										svgClasses="w-4 h-4 text-success"
									/>
								</div>
								<span v-else>{{data[indextr].qtde_notas_distrib}}</span>
							</vs-td>

							<vs-td align="center">
								<router-link :to="'/distribuir-entregas/' + tr.coleta_id">
									<vs-button type="border" size="small">
										<span v-if="data[indextr].qtde_notas_distrib > 0">DISTRIBUIR</span>
										<span v-else>VER NOTAS</span>
									</vs-button>
								</router-link>
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
import controleMixins from "@/mixins/controleMixins";

export default {
	mixins: [procsMixins, controleMixins],
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false
		};
	},
	async created() {
		await this.retornarColetasMultiDestinosRealizadas();
		await this.getDataAtual();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		coletasMDRealizadasData() {
			return this.$store.state.dashboard.coletasMDRealizadasData;
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
				: this.coletasMDRealizadasData.length;
		}
	},
	methods: {
		async retornarColetasMultiDestinosRealizadas() {
			await this.$store
				.dispatch("dashboard/retornarColetasMultiDestinosRealizadas")
				.catch(err => {
					console.error(err);
				});
		},
		async getDataAtual() {
			await this.$store.dispatch("dataAtual/getDataAtual").catch(err => {
				console.error(err);
			});
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.retornarColetasMultiDestinosRealizadas();
			await this.getDataAtual();
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

