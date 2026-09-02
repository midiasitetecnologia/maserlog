<template>
	<div id="dashboard-analytics">
		<!-- Olá -->
		<div class="vx-row">
			<div class="vx-col w-full mb-base">
				<vx-card slot="no-body" class="text-center bg-primary-gradient greet-user">
					<img
						src="@assets/images/elements/decore-left.png"
						class="decore-left"
						alt="Decore Left"
						width="200"
					/>
					<img
						src="@assets/images/elements/decore-right.png"
						class="decore-right"
						alt="Decore Right"
						width="175"
					/>
					<feather-icon
						icon="BookOpenIcon"
						class="p-6 mb-8 bg-primary inline-flex rounded-full text-white shadow"
						svgClasses="h-8 w-8"
					></feather-icon>
					<h1 class="mb-6 text-white">Olá {{ $store.state.AppActiveUser.displayName | first_name }},</h1>

					<p class="xl:w-3/4 lg:w-4/5 md:w-2/3 w-4/5 mx-auto text-white">
						{{msgWisdomUserData.texto}}
						<span
							v-if="msgWisdomUserData.fonte != ''"
							class="ml-1 text-white"
							style="font-size: 12px;font-style: italic;"
						>({{msgWisdomUserData.fonte}})</span>
					</p>
				</vx-card>
			</div>
		</div>

		<div class="vx-row" v-if="solicData.length > 0">
			<div class="vx-col w-full">
				<vx-card>
					<div slot="no-body" class="123 mt-4">
						<vs-table
							ref="table"
							:noDataText="solicData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
							search
							:max-items="solicData.length"
							:data="solicData"
							class="table-dark-inverted"
						>
							<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
								<span class="pl-4 font-semibold">Solicitações em andamento</span>
							</div>

							<template slot="thead">
								<vs-th sort-key="numero">Solicitação</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="dt_prev_coleta">Prev. Coleta</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Local de Coleta</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="dt_prev_entrega">Prev. Entrega</vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Local de Entrega</vs-th>
								<!-- Foto carga -->
								<vs-th></vs-th>
								<vs-th class="whitespace-no-wrap" sort-key="descricao_tipo_veiculo">Tipo Veículo</vs-th>
								<vs-th sort-key="status">Status</vs-th>
								<!-- Notas Fiscais -->
								<vs-th></vs-th>
							</template>

							<template slot-scope="{data}">
								<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
									<vs-td class="whitespace-no-wrap">
										<div>
											<div :class="{'flex items-center': true, 'text-center': true}">
												<router-link
													v-if="data[indextr].numero != null"
													class="text-inherit hover:underline"
													@click.stop.prevent
													:to="url(tr.id)"
												>{{data[indextr].numero}}</router-link>
												<router-link
													v-else
													:to="url(tr.id)"
													@click.stop.prevent
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

									<vs-td class="whitespace-no-wrap">
										<div>
											<span
												v-if="data[indextr].dt_prev_coleta == data[indextr].data_cad"
												:style="data[indextr].dt_efet_coleta == null ? emAtraso(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta) : ''"
											>{{data[indextr].hr_prev_coleta | hora_min}}</span>
											<span
												v-else
												:style="data[indextr].dt_efet_coleta == null ? emAtraso(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta) : ''"
											>{{data[indextr].dt_prev_coleta | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min}}</span>
										</div>
										<div>
											<div class="flex items-center">
												<span
													v-if="data[indextr].dt_efet_coleta == data[indextr].data_cad"
													style="font-size: 12px; color:gray"
												>{{data[indextr].hr_sai_coleta | hora_min}}</span>
												<span
													v-else
													style="font-size: 12px; color:gray"
												>{{data[indextr].dt_efet_coleta | moment("DD MMM")}} {{data[indextr].hr_sai_coleta | hora_min}}</span>
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
											<span>{{data[indextr].local_coleta | truncate(25)}}</span>
										</div>
										<div v-if="data[indextr].placa_coleta != null">
											<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_coleta}}</span>
										</div>
									</vs-td>

									<vs-td class="whitespace-no-wrap">
										<div>
											<span
												v-if="data[indextr].dt_prev_entrega == data[indextr].data_cad"
												:style="data[indextr].dt_efet_entrega == null ? emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega) : ''"
											>{{data[indextr].hr_prev_entrega | hora_min}}</span>
											<span
												v-else
												:style="data[indextr].dt_efet_entrega == null ? emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega) : ''"
											>{{data[indextr].dt_prev_entrega | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min}}</span>
										</div>
										<div>
											<div class="flex items-center">
												<span
													v-if="data[indextr].dt_efet_entrega == data[indextr].data_cad"
													style="font-size: 12px; color:gray"
												>{{data[indextr].hr_sai_entrega | hora_min}}</span>
												<span
													v-else
													style="font-size: 12px; color:gray"
												>{{data[indextr].dt_efet_entrega | moment("DD MMM")}} {{data[indextr].hr_sai_entrega | hora_min}}</span>
												<feather-icon
													v-if="data[indextr].hr_sai_entrega != null"
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
										<div v-if="data[indextr].placa_entrega != null">
											<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_entrega}}</span>
										</div>
									</vs-td>

									<vs-td align="center">
										<div v-if="data[indextr].img_carga != null">
											<vx-tooltip text="Clique para visualizar a foto da carga" position="top">
												<feather-icon
													icon="ImageIcon"
													svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
													@click="exibirFotoCarga(data[indextr])"
												></feather-icon>
											</vx-tooltip>
										</div>
									</vs-td>

									<vs-td>{{data[indextr].descricao_tipo_veiculo}}</vs-td>

									<vs-td class="whitespace-no-wrap">
										<div :class="{'flex items-center': true, 'text-center': false}">
											<vs-avatar
												size="small"
												:color="corStatus(data[indextr].status)"
												:text="inicialStatus(data[indextr].status)"
											/>
											<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
										</div>
									</vs-td>

									<vs-td class="whitespace-no-wrap">
										<div class="flex flex-items-center">
											<span v-if="data[indextr].notas_fiscais > 0">
												<vx-tooltip class="mr-2" text="Notas fiscais" position="left">
													<feather-icon
														icon="FileIcon"
														:svgClasses="'w-5 h-5 hover:text-success'"
														@click="notasFiscais(data[indextr].id)"
													/>
												</vx-tooltip>
											</span>
											<span v-else>
												<vx-tooltip
													v-if="data[indextr].img_rom_coleta != null"
													class="mr-2"
													text="Romaneios"
													position="left"
												>
													<feather-icon
														icon="FileIcon"
														:svgClasses="'w-5 h-5 hover:text-success'"
														@click="romaneios(dashboardUrlImgData, data[indextr])"
													/>
												</vx-tooltip>
											</span>

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
										</div>
									</vs-td>

								</vs-tr>
							</template>
						</vs-table>
					</div>
				</vx-card>
			</div>
		</div>

		<vs-popup class="holamundo fit-content" :title="fotoTitulo" :active.sync="popupFoto">
			<div align="center">
				<img :src="foto" height="800px" />
			</div>
		</vs-popup>

		<notas-fiscais-pop-up />
		<romaneios-pop-up />

	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";
import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine.vue";
import NotasFiscaisPopUp from "@/components/rgsoft/NotasFiscaisPopUp.vue";
import RomaneiosPopUp from "@/components/rgsoft/RomaneiosPopUp.vue";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	mixins: [procsMixins, coletaMixins],
	components: {
		StatisticsCardLine,
		NotasFiscaisPopUp,
		RomaneiosPopUp,
		ColFixa,
	},
	data() {
		return {
			ordersRecevied: {},
		};
	},
	created() {
		this.retornarMsgWisdomUser();
		this.getSolicitacoes();
		this.getDataAtual();
	},
	computed: {
		msgWisdomUserData() {
			return this.$store.state.dashboard.msgWisdomUserData;
		},
		solicData() {
			return this.$store.state.dashboard.solicData;
		},
		dashboardUrlImgData() {
			return this.$store.state.dashboard.dashboardUrlImgData;
		},
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		},
		dataHoraAtual() {
			return this.$store.state.dataAtual.dataHoraAtual;
		},
	},
	methods: {
		async retornarMsgWisdomUser() {
			await this.$store
				.dispatch("dashboard/retornarMsgWisdomUser")
				.catch((err) => {
					console.error(err);
				});
		},
		getSolicitacoes() {
			this.$store.dispatch("dashboard/getSolicitacoes").catch((err) => {
				console.error(err);
			});
		},
		async getDataAtual() {
			this.$store.dispatch("dataAtual/getDataAtual").catch((err) => {
				console.error(err);
			});
		},
		url(id) {
			return "/coleta/" + id;
		},
		exibirDia(data) {
			//Se for igual a Hoje (Mesmo dia) não vamos exibir.
			if (data === this.dataAtual) return "";
			else return data;
		},
		emAtraso(data, hora) {
			if (this.dataHoraAtual > data + " " + hora) {
				return "color:red;font-weight: 300;!important;";
			}
		},
		exibirFotoCarga(dados) {
			let titulo;
			if (dados.numero != null) {
				titulo = "Carga da coleta: " + dados.numero;
			} else {
				titulo = "Carga da coleta: ID " + dados.id;
			}
			this.exibirFoto(this.dashboardUrlImgData + dados.img_carga, titulo);
		},
		async notasFiscais(coleta_id) {
			await this.$vs.loading({ scale: 0.5 });
			await this.getNotasFiscais(coleta_id);
			await this.$store.commit("EXIBIR_NOTAS_FISCAIS");
			await this.$vs.loading.close();
		},
		async romaneios(url, dados) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				img_rom_coleta:
					dados.img_rom_coleta != null
						? url + dados.img_rom_coleta
						: null,
				img_rom_entrega:
					dados.img_rom_entrega != null
						? url + dados.img_rom_entrega
						: null,
			};

			await this.$store.commit("SET_FOTOS_ROMANEIOS", payload);
			await this.$store.commit("EXIBIR_ROMANEIOS");
			await this.$vs.loading.close();
		},
	},
};
</script>

<style lang="scss">
/*! rtl:begin:ignore */
#dashboard-analytics {
	.greet-user {
		position: relative;

		.decore-left {
			position: absolute;
			left: 0;
			top: 0;
		}
		.decore-right {
			position: absolute;
			right: 0;
			top: 0;
		}
	}

	@media (max-width: 576px) {
		.decore-left,
		.decore-right {
			width: 140px;
		}
	}

	.vs-con-table .vs-table--header .vs-table--search {
		padding-right: 1rem !important;
	}

	.vs-con-table .vs-table--header .vs-table--search i {
		left: 5px !important;
	}
}

.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}

/*! rtl:end:ignore */
</style>
