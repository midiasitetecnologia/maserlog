<template>
	<div id="dashboard-analytics">
		<!-- Olá -->
		<div class="vx-row">
			<div class="vx-col w-full mb-base">
				<vx-card
					slot="no-body"
					class="text-center bg-primary-gradient greet-user"
				>
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
					<h1 class="mb-6 text-white">
						Olá
						{{
							$store.state.AppActiveUser.displayName | first_name
						}},
					</h1>

					<p
						class="xl:w-3/4 lg:w-4/5 md:w-2/3 w-4/5 mx-auto text-white"
					>
						{{ msgWisdomUserData.texto }}
						<span
							v-if="msgWisdomUserData.fonte != ''"
							class="ml-1 text-white"
							style="font-size: 12px; font-style: italic"
							>({{ msgWisdomUserData.fonte }})</span
						>
					</p>
				</vx-card>
			</div>
		</div>

		<div class="vx-row">
			<!-- Tarefas -->
			<div class="vx-col w-full md:w-1/3 lg:w-1/3 xl:w-1/3 mb-base">
				<vx-card title="Tarefas" style="min-height: 462px">
					<ul class="vx-timeline">
						<li
							v-for="item in tarefasHomeData"
							:key="item.descricao"
						>
							<div
								class="timeline-icon"
								:class="
									item.regs > 0
										? `bg-${item.fundo}`
										: 'bg-inactive'
								"
							>
								<feather-icon
									:icon="item.icon"
									svgClasses="text-white stroke-current w-5 h-5"
								/>
							</div>
							<div class="flex items-center justify-between">
								<div class="mt-1">
									<p class="font-semibold">
										<router-link
											v-if="item.regs > 0"
											class="text-inherit hover:underline"
											:to="item.url"
											>{{ item.descricao }}</router-link
										>
										<span v-else>{{ item.descricao }}</span>
									</p>
								</div>
								<div class="mt-1">
									<h2>
										<span
											:style="
												item.regs <= 0
													? 'color: rgb(157, 157, 157)'
													: ''
											"
											>{{ item.regs }}</span
										>
									</h2>
								</div>
							</div>
							<br />
							<br />
						</li>
					</ul>
				</vx-card>
			</div>

			<!-- Solicitações -->
			<div
				class="vx-col w-full sm:w-1/2 md:w-1/3 lg:w-1/3 xl:w-1/3 mb-base"
			>
				<vx-card
					title="Solicitações"
					style="min-height: 462px"
					v-if="resumoColetasHomeData"
				>
					<div slot="no-body">
						<div class="vx-row text-center">
							<div
								class="vx-col w-full lg:w-1/5 md:w-full sm:w-1/5 flex flex-col justify-between mb-4 lg:order-first md:order-last sm:order-first order-last"
							>
								<div
									class="lg:ml-6 lg:mt-6 md:mt-0 md:ml-0 sm:ml-6 sm:mt-6"
								>
									<h1 class="font-bold text-5xl">
										{{ resumoColetasHomeData.total }}
									</h1>
									<small>Hoje</small>
								</div>
							</div>

							<div
								class="vx-col w-full lg:w-4/5 md:w-full sm:w-4/5 justify-center mx-auto lg:mt-8 md:mt-8 sm:mt-8 mt-8"
							>
								<vue-apex-charts
									type="radialBar"
									height="340"
									:options="SolicitacoesOptions"
									:series="resumoColetasHomeData.percentuais"
								/>
							</div>
						</div>
					</div>
					<div class="flex flex-row justify-between px-8 mt-8">
						<p
							class="text-center"
							v-for="(val, key) in resumoColetasHomeMeta"
							:key="key"
						>
							<span class="block">{{ key }}</span>
							<span class="text-2xl font-semibold">{{
								val
							}}</span>
						</p>
					</div>
				</vx-card>
			</div>

			<!-- Frota -->
			<div
				class="vx-col w-full sm:w-1/2 md:w-1/3 lg:w-1/3 xl:w-1/3 mb-base"
			>
				<vx-card title="Frota" style="min-height: 462px">
					<div class="no-body">
						<vue-apex-charts
							type="radialBar"
							:options="FrotaOptions.chartOptions"
							:series="resumoFrotaHomeData.percentuais"
						/>
					</div>

					<ul>
						<li
							v-for="item in resumoFrotaHomeData.grafico"
							:key="item.label"
							class="flex justify-between"
						>
							<span class="flex items-center">
								<span
									class="inline-block h-4 w-4 rounded-full mr-2 bg-white border-3 border-solid"
									:class="`border-${item.color}`"
								></span>
								<span class="font-semibold">{{
									item.label
								}}</span>
							</span>
							<span>{{ item.counts }}</span>
						</li>
					</ul>
				</vx-card>
			</div>
		</div>

		<div class="vx-row">
			<!-- Resumos -->
			<div
				class="vx-col w-full sm:w-1/2 md:w-1/2 lg:w-1/2 xl:w-1/2 mb-base"
			>
				<vx-card
					:title="resumoKmTempoHomeData.titulo"
					style="min-height: 330px"
				>
					<div
						v-for="(val, index) in resumoKmTempoHomeData.progresso"
						:key="val.id"
						:class="{ 'mt-4': index }"
					>
						<div class="flex justify-between">
							<div class="flex flex-col">
								<span class="mb-1">{{ val.nome }}</span>
								<h4>{{ val.percentual }}%</h4>
							</div>
							<div class="flex flex-col text-right">
								<span class="flex -mr-1">
									<span class="mr-1">{{
										val.resultadoAtual
									}}</span>
									<feather-icon
										v-if="val.percentual != 0"
										:icon="
											val.percentual < 0
												? 'ArrowDownIcon'
												: 'ArrowUpIcon'
										"
										:svgClasses="[
											val.percentual < 0
												? 'text-danger'
												: 'text-success',
											'stroke-current h-4 w-4 mb-1 mr-1',
										]"
									></feather-icon>
								</span>
								<span class="text-grey">{{
									val.resultadoAnt
								}}</span>
							</div>
						</div>
						<vs-progress
							v-if="val.percentual <= 0"
							:percent="0"
						></vs-progress>
						<vs-progress
							v-else
							:percent="val.percentual"
						></vs-progress>
					</div>
				</vx-card>
			</div>

			<!-- MOTORISTAS DISPONÍVEIS -->
			<div
				class="vx-col w-full sm:w-1/2 md:w-1/2 lg:w-1/2 xl:w-1/2 mb-base"
				v-if="motoristasDisponiveisData.length > 0"
			>
				<vx-card
					:title="
						'Motoristas disponíveis (' +
						this.motoristasDisponiveisData.length +
						')'
					"
				>
					<div slot="no-body" class="mt-4">
						<vs-table
							max-items="5"
							pagination
							stripe
							:data="motoristasDisponiveisData"
							class="table-dark-inverted"
						>
							<template slot-scope="{ data }">
								<vs-tr
									:key="indextr"
									v-for="(tr, indextr) in data"
								>
									<vs-td class="whitespace-no-wrap">{{
										data[indextr].nome | truncate(18)
									}}</vs-td>

									<vs-td class="whitespace-no-wrap">{{
										data[indextr].dt_logado | hora_min
									}}</vs-td>
								</vs-tr>
							</template>
						</vs-table>
					</div>
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import VueApexCharts from "vue-apexcharts";
import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine.vue";

export default {
	mixins: [procsMixins],
	components: {
		VueApexCharts,
		StatisticsCardLine,
	},
	data() {
		return {
			atualizarInformacoes: null,

			ordersRecevied: {},
			supportTracker: {},

			meta: {
				Novas: 0,
				Abertas: 0,
				Finalizadas: 0,
			},
		};
	},
	created() {
		this.retornarMsgWisdomUser();
		this.retornarTarefasHome();
		this.retornarResumoColetasHome();
		this.retornarResumoFrotaHome();
		this.retornarResumoKmTempoHome();
		this.retornarMotoristasDisponiveis();

		this.intervalInformacoes();
	},
	beforeDestroy() {
		clearInterval(this.atualizarInformacoes);
	},
	computed: {
		msgWisdomUserData() {
			return this.$store.state.dashboard.msgWisdomUserData;
		},
		tarefasHomeData() {
			return this.$store.state.dashboard.tarefasHomeData;
		},
		resumoColetasHomeData() {
			return this.$store.state.dashboard.resumoColetasHomeData;
		},
		resumoColetasHomeMeta() {
			if (this.resumoColetasHomeData.meta !== undefined) {
				return this.resumoColetasHomeData.meta[0];
			}
			return this.meta;
		},
		resumoFrotaHomeData() {
			return this.$store.state.dashboard.resumoFrotaHomeData;
		},
		resumoKmTempoHomeData() {
			return this.$store.state.dashboard.resumoKmTempoHomeData;
		},
		motoristasDisponiveisData() {
			return this.$store.state.dashboard.motoristasDisponiveisData;
		},
		SolicitacoesOptions() {
			return {
				plotOptions: {
					radialBar: {
						size: 158,
						offsetY: -30,
						startAngle: -150,
						endAngle: 150,
						hollow: {
							size: "65%",
						},
						track: {
							background: "rgba(238, 238, 238, 0.9)",
							strokeWidth: "100%",
						},
						dataLabels: {
							value: {
								offsetY: 30,
								color: "#99a2ac",
								fontSize: "2rem",
							},
						},
					},
				},
				colors: ["#EA5455"],
				fill: {
					type: "gradient",
					gradient: {
						// enabled: true,
						shade: "dark",
						type: "horizontal",
						shadeIntensity: 0.5,
						gradientToColors: ["#7367F0"],
						inverseColors: true,
						opacityFrom: 1,
						opacityTo: 1,
						stops: [0, 100],
					},
				},
				stroke: {
					dashArray: 8,
				},
				chart: {
					height: 280,
					type: "radialBar",
				},
				labels: ["Finalizadas"],
			};
		},
		FrotaOptions() {
			var totalFrota = this.resumoFrotaHomeData.total;
			var options = {
				chartOptions: {
					labels: ["Ocupados", "Ociosos"],
					plotOptions: {
						radialBar: {
							size: 280,
							offsetY: -5,
							hollow: {
								size: "35%",
							},
							track: {
								background: "#ebebeb",
								strokeWidth: "100%",
								margin: 10,
							},
							dataLabels: {
								show: true,
								name: {
									fontSize: "18px",
								},
								value: {
									fontSize: "16px",
									color: "#636a71",
									offsetY: 10,
								},
								total: {
									show: true,
									label: "Total",
									formatter: function () {
										return totalFrota;
									},
								},
							},
						},
					},
					colors: ["#EA5455", "#FF9F43"],
					fill: {
						type: "gradient",
						gradient: {
							// enabled: true,
							shade: "dark",
							type: "vertical",
							shadeIntensity: 0.5,
							gradientToColors: ["#f29292", "#FFC085"],
							inverseColors: false,
							opacityFrom: 1,
							opacityTo: 1,
							stops: [0, 100],
						},
					},
					stroke: {
						lineCap: "round",
					},
					chart: {
						height: 320,
						dropShadow: {
							enabled: true,
							blur: 3,
							left: 1,
							top: 1,
							opacity: 0.1,
						},
					},
				},
			};

			return options;
		},
	},
	methods: {
		intervalInformacoes() {
			this.atualizarInformacoes = setInterval(() => {
				//Aqui por algum motivo não podemos chamar a função (Ex: retornarTarefasHome), temos que passar
				//a chamada direto do store, caso contrário não reconhece a função e emite um erro.
				this.$store
					.dispatch("dashboard/retornarTarefasHome")
					.catch((err) => {
						console.error(err);
					});
				this.$store
					.dispatch("dashboard/retornarResumoColetasHome")
					.catch((err) => {
						console.error(err);
					});
				this.$store
					.dispatch("dashboard/retornarResumoFrotaHome")
					.catch((err) => {
						console.error(err);
					});
				this.$store
					.dispatch("dashboard/retornarResumoKmTempoHome")
					.catch((err) => {
						console.error(err);
					});
				this.$store
					.dispatch("dashboard/retornarMotoristasDisponiveis")
					.catch((err) => {
						console.error(err);
					});
			}, 30000);
		},
		async retornarMsgWisdomUser() {
			await this.$store
				.dispatch("dashboard/retornarMsgWisdomUser")
				.catch((err) => {
					console.error(err);
				});
		},
		async retornarTarefasHome() {
			await this.$store
				.dispatch("dashboard/retornarTarefasHome")
				.catch((err) => {
					console.error(err);
				});
		},
		async retornarResumoColetasHome() {
			await this.$store
				.dispatch("dashboard/retornarResumoColetasHome")
				.catch((err) => {
					console.error(err);
				});
			this.meta = JSON.parse(
				JSON.stringify(this.resumoColetasHomeData.meta[0])
			);
		},
		async retornarResumoFrotaHome() {
			await this.$store
				.dispatch("dashboard/retornarResumoFrotaHome")
				.catch((err) => {
					console.error(err);
				});
		},
		async retornarResumoKmTempoHome() {
			await this.$store
				.dispatch("dashboard/retornarResumoKmTempoHome")
				.catch((err) => {
					console.error(err);
				});
		},
		async retornarMotoristasDisponiveis() {
			await this.$store
				.dispatch("dashboard/retornarMotoristasDisponiveis")
				.catch((err) => {
					console.error(err);
				});
		},
	},
};
</script>

<style lang="scss">
@import "@sass/vuexy/components/vxTimeline.scss";

.bg-inactive {
	background-color: rgb(157, 157, 157);
}

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
}
/*! rtl:end:ignore */
</style>
