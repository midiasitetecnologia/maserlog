<template>
	<div class="vx-row">
		<div class="vx-col w-full">
			<vx-card>
				<vs-table
					ref="table"
					:noDataText="
						entregasNaoRealizadasReentregaData.length > 0
							? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.'
							: 'Não há registros para mostrar.'
					"
					v-model="selected"
					:max-items="itemsPerPage"
					pagination
					search
					stripe
					:data="entregasNaoRealizadasReentregaData"
				>
					<div
						slot="header"
						class="flex flex-wrap-reverse items-center flex-grow justify-between"
					>
						<div class="flex mb-1">
							<label class="font-semibold"
								>Entregas não realizadas</label
							>
						</div>

						<div class="flex flex-wrap-reverse items-center">
							<feather-icon
								@click="refresh"
								icon="RotateCwIcon"
								svgClasses="h-4 w-4"
								class="cursor-pointer mr-4"
							/>
							<vs-dropdown
								vs-trigger-click
								class="cursor-pointer mr-4 items-per-page-handler"
							>
								<div
									class="p-2 border border-solid d-theme-border-grey-light rounded-full d-theme-dark-bg cursor-pointer flex items-center justify-between font-medium whitespace-no-wrap"
								>
									<span class
										>{{
											currentPage * itemsPerPage -
											(itemsPerPage - 1)
										}}
										-
										{{
											entregasNaoRealizadasReentregaData.length -
												currentPage * itemsPerPage >
											0
												? currentPage * itemsPerPage
												: entregasNaoRealizadasReentregaData.length
										}}
										de {{ queriedItems }}</span
									>
									<feather-icon
										icon="ChevronDownIcon"
										svgClasses="h-4 w-4"
									/>
								</div>

								<vs-dropdown-menu>
									<vs-dropdown-item
										@click="itemsPerPage = 10"
									>
										<span>10</span>
									</vs-dropdown-item>
									<vs-dropdown-item
										@click="itemsPerPage = 15"
									>
										<span>15</span>
									</vs-dropdown-item>
									<vs-dropdown-item
										@click="itemsPerPage = 20"
									>
										<span>20</span>
									</vs-dropdown-item>
								</vs-dropdown-menu>
							</vs-dropdown>
						</div>
					</div>

					<template slot="thead">						
						<vs-th sort-key="numero">Solicitação</vs-th>
						<vs-th sort-key="local_coleta" class="whitespace-no-wrap">Coleta</vs-th>
						<vs-th sort-key="local_entrega" class="whitespace-no-wrap">Entrega</vs-th>
						<vs-th sort-key="qtde_notas" class="whitespace-no-wrap">Notas</vs-th>
						<vs-th sort-key="mot_nao_entrega" class="whitespace-no-wrap">Motivo</vs-th>
						<vs-th></vs-th>
					</template>

					<template slot-scope="{ data }">
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
									<span>{{data[indextr].local_coleta | truncate(25)}}</span>
								</div>
								<div>
									<div class="flex items-center">
										<span
											class="mr-2"
											style="font-size: 12px; color: gray"
											>{{
												data[indextr].placa_coleta
											}}</span
										>
										<span
											style="font-size: 12px; color: gray"
											>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM")}} {{data[indextr].hr_sai_coleta | hora_min}}</span
										>
										<feather-icon
											v-if="data[indextr].hr_sai_coleta != null"
											class="ml-1"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>										
									</div>
								</div>
							</vs-td>

							<vs-td>
								<div>
									<span>{{data[indextr].local_entrega | truncate(25)}}</span>
								</div>
								<div>
									<div class="flex items-center">
										<span class="mr-2" style="font-size: 12px; color: gray">{{data[indextr].placa_entrega}}</span>
										<span style="font-size: 12px; color: gray">
											{{exibirDia(data[indextr].dt_efet_entrega) | moment("DD MMM")}} {{data[indextr].hr_sai_entrega | hora_min}}
										</span>
										<feather-icon
											v-if="data[indextr].hr_sai_entrega != null"
											class="ml-1"
											icon="CheckCircleIcon"
											svgClasses="w-4 h-4 text-success"
										/>		
										<span v-if="data[indextr].carga_pavilhao == 'S'" class="badge_cinza ml-2">pavilhão</span>
									</div>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap">
								<div v-if="data[indextr].aceitar_foto_rom == 'S'">
									Romaneio
								</div>
								<div v-else class="flex items-center">
									<span>{{data[indextr].qtde_notas}}</span>
									<vx-tooltip											
										v-if="data[indextr].qtde_notas_reentrega > 0"
										text="Notas não entregues"
										position="top"
									>										
										<span class="badge_vermelho ml-2" style="font-size: 14px">{{data[indextr].qtde_notas_reentrega}}</span>
									</vx-tooltip>
								</div>
							</vs-td>

							<vs-td>
								<div>
									{{data[indextr].mot_nao_entrega}}	
								</div>
								<div>	
									<span style="font-size: 12px; color:gray">{{data[indextr].obs_nao_entrega | truncate(25)}}</span>
								</div>
							</vs-td>

							<vs-td align="center">
								<div v-if="data[indextr].carga_pavilhao != 'S'">
									<span class="text-warning" style="font-size: 12px">Falta descarregar</span>
								</div>
								<div v-else>
									<router-link :to="'/definir-reentrega/' + tr.coleta_id">
										<vs-button type="border" size="small">										
											CRIAR SOLICITAÇÃO
										</vs-button>
									</router-link>
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
import controleMixins from "@/mixins/controleMixins";

export default {
	mixins: [procsMixins, controleMixins],
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false,
		};
	},
	async created() {
		await this.retornarEntregasNaoRealizadasReentrega();
		await this.getDataAtual();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		entregasNaoRealizadasReentregaData() {
			return this.$store.state.dashboard
				.entregasNaoRealizadasReentregaData;
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
				: this.entregasNaoRealizadasReentregaData.length;
		},
	},
	methods: {
		async retornarEntregasNaoRealizadasReentrega() {
			await this.$store
				.dispatch("dashboard/retornarEntregasNaoRealizadasReentrega")
				.catch((err) => {
					console.error(err);
				});
		},
		async getDataAtual() {
			await this.$store
				.dispatch("dataAtual/getDataAtual")
				.catch((err) => {
					console.error(err);
				});
		},
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });
			await this.retornarEntregasNaoRealizadasReentrega();
			await this.getDataAtual();
			await this.$vs.loading.close();
		},
		url(id) {
			return "/coleta/" + id;
		},
	},
};
</script>

<style lang="scss" scoped>	
</style>

