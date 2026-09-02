<template>
	<div>
		<div
			class="vx-row"
			v-if="(pnDefinirVeiculos == false) && (pnVeiculoCarga == false) && (pnVeiculosBaldeacao == false)"
		>
			<div class="vx-col w-full md:w-3/4 lg:w-3/4 xl:w-3/4">
				<div class="vx-row">
					<div class="vx-col w-full md:w-1/2 lg:w-1/2 xl:w-1/4">
						<span class="cursor-pointer" @click="exibirPaineis('ColetasPendentes')">
							<statistics-card-line
								hideChart
								class="mb-base"
								icon="GitCommitIcon"
								icon-right
								:statistic="countColetasPendentesData > 0 ? countColetasPendentesData : '0'"
								:destacarPainel="pnColetasPendentes ? true : false"
								statisticTitle="Novas coletas"
								color="warning"
							/>
						</span>
					</div>

					<div class="vx-col w-full md:w-1/2 lg:w-1/2 xl:w-1/4">
						<span class="cursor-pointer" @click="exibirPaineis('ColetasAndamento')">
							<statistics-card-line
								hideChart
								class="mb-base"
								icon="ActivityIcon"
								icon-right
								:statistic="countColetasAndamentoData > 0 ? countColetasAndamentoData : '0'"
								:destacarPainel="pnColetasAndamento ? true : false"
								statisticTitle="Coletas andamento"
								color="primary"
							/>
						</span>
					</div>

					<div class="vx-col w-full md:w-1/2 lg:w-1/2 xl:w-1/4">
						<span class="cursor-pointer" @click="exibirPaineis('EntregasPendentes')">
							<statistics-card-line
								hideChart
								class="mb-base"
								icon="GitMergeIcon"
								icon-right
								:statistic="countEntregasPendentesData > 0 ? countEntregasPendentesData : '0'"
								:destacarPainel="pnEntregasPendentes ? true : false"
								statisticTitle="Entregas pendentes"
								color="warning"
							/>
						</span>
					</div>

					<div class="vx-col w-full md:w-1/2 lg:w-1/2 xl:w-1/4">
						<span class="cursor-pointer" @click="exibirPaineis('EntregasAndamento')">
							<statistics-card-line
								hideChart
								class="mb-base"
								icon="ActivityIcon"
								icon-right
								:statistic="countEntregasAndamentoData > 0 ? countEntregasAndamentoData : '0'"
								:destacarPainel="pnEntregasAndamento ? true : false"
								statisticTitle="Entregas andamento"
								color="primary"
							/>
						</span>
					</div>
				</div>
			</div>

			<div class="vx-col w-full md:w-1/4 lg:w-1/4 xl:w-1/4">
				<div class="vx-row">
					<div class="vx-col w-full xl:w-1/2">
						<span class="cursor-pointer" @click="exibirPaineis('Veiculos')">
							<statistics-card-line
								hideChart
								class="mb-base"
								icon="TruckIcon"
								icon-right
								:statistic="veiculosFrotaData.length > 0 ? veiculosFrotaData.length : '0'"
								:destacarPainel="pnVeiculosFrota ? true : false"
								statisticTitle="Veículos"
								color="danger"
							/>
						</span>
					</div>

					<div class="vx-col w-full xl:w-1/2">
						<span class="cursor-pointer" @click="exibirPaineis('SolicitacoesFinalizadas')">
							<statistics-card-line
								hideChart
								class="mb-base"
								icon="CheckIcon"
								icon-right
								:statistic="countSolicitacoesFinalizadasData > 0 ? countSolicitacoesFinalizadasData : '0'"
								:destacarPainel="pnSolicitacoesFinalizadas ? true : false"
								statisticTitle="Finalizadas"
								color="success"
							/>
						</span>
					</div>
				</div>
			</div>
		</div>

		<!-- COLETAS PENDENTES -->

		<!-- O painel de novas coletas exibe a tabela mesmo que não tenha dados, optamos em exibir nesta situação porque temos filtros 
		para mostrar dados.-->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<coletas-pendentes v-show="(pnColetasPendentes == true) && (pnDefinirVeiculos == false)" />
		<!-- DEFINIR VEICULOS -->
		<definicao-veiculos-coleta v-if="pnDefinirVeiculos == true" />

		<!-- COLETAS ANDAMENTO -->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<coletas-andamento v-show="(coletasAndamentoData.length > 0) && (pnColetasAndamento == true)" />

		<!-- ENTREGAS PENDENTES -->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<entregas-pendentes v-show="(entregasPendentesData.length > 0) && (pnEntregasPendentes == true)" />

		<!-- ENTREGAS ANDAMENTO -->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<entregas-andamento v-show="(entregasAndamentoData.length > 0) && (pnEntregasAndamento == true)" />

		<!-- VEÍCULOS FROTA -->

		<!-- O painel de veículos frota exibe a tabela mesmo que não tenha dados, optamos em exibir nesta situação porque temos filtros 
		para mostrar dados.-->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<veiculos-frota v-show="(pnVeiculosFrota == true) && (pnVeiculoCarga == false)" />

		<!-- VEICULO CARGA -->
		<veiculo-carga v-if="(pnVeiculoCarga == true) && (pnVeiculosBaldeacao == false)" />

		<!-- VEICULO CARGA -->
		<veiculos-baldeacao v-if="pnVeiculosBaldeacao == true" />

		<!-- SOLICITACOES FINALIZADAS -->

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<solicitacoes-finalizadas
			v-show="(solicitacoesFinalizadasData.length > 0) && (pnSolicitacoesFinalizadas == true)"
		/>

		<rota-pop-up />
		<notas-fiscais-pop-up />
		<romaneios-pop-up />
		<auditoria-pop-up />
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";

import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine.vue";
import ColetasPendentes from "./ColetasPendentes.vue";
import DefinicaoVeiculosColeta from "./DefinicaoVeiculosColeta.vue";
import ColetasAndamento from "./ColetasAndamento.vue";
import EntregasPendentes from "./EntregasPendentes.vue";
import EntregasAndamento from "./EntregasAndamento.vue";
import VeiculosFrota from "./VeiculosFrota.vue";
import VeiculoCarga from "./VeiculoCarga.vue";
import VeiculosBaldeacao from "./VeiculosBaldeacao.vue";
import SolicitacoesFinalizadas from "./SolicitacoesFinalizadas.vue";

import RotaPopUp from "@/components/rgsoft/RotaPopUp.vue";
import NotasFiscaisPopUp from "@/components/rgsoft/NotasFiscaisPopUp.vue";
import RomaneiosPopUp from "@/components/rgsoft/RomaneiosPopUp.vue";
import AuditoriaPopUp from "@/components/rgsoft/AuditoriaPopUp.vue";

export default {
	mixins: [controleMixins],
	components: {
		StatisticsCardLine,
		ColetasPendentes,
		DefinicaoVeiculosColeta,
		ColetasAndamento,
		EntregasPendentes,
		EntregasAndamento,
		VeiculosFrota,
		VeiculoCarga,
		VeiculosBaldeacao,
		SolicitacoesFinalizadas,
		RotaPopUp,
		NotasFiscaisPopUp,
		RomaneiosPopUp,
		AuditoriaPopUp
	},
	data() {
		return {
			atualizarContadores: null,

			pnColetasPendentes: true,
			pnColetasAndamento: false,
			pnEntregasPendentes: false,
			pnEntregasAndamento: false,
			pnVeiculosFrota: false,
			pnSolicitacoesFinalizadas: false
		};
	},
	created() {
		this.getDataAtual();

		this.intervalContadores();
	},
	mounted() {
		this.wasSidebarOpen = this.$store.state.reduceButton;
		this.$store.commit("TOGGLE_REDUCE_BUTTON", true);
	},
	beforeDestroy() {
		if (!this.wasSidebarOpen) {
			this.$store.commit("TOGGLE_REDUCE_BUTTON", false);
		}
		if (this.pnDefinirVeiculos == true) {
			this.$store.commit("controle/SET_DEFINIR_VEICULOS");
		}
		if (this.pnVeiculoCarga == true) {
			this.$store.commit("controle/EXIBIR_PAINEL_VEICULO_CARGA");
		}
		if (this.pnVeiculosBaldeacao == true) {
			this.$store.commit("controle/EXIBIR_VEICULOS_BALDEACAO");
		}
		clearInterval(this.atualizarContadores);
	},
	computed: {
		countColetasPendentesData() {
			return this.$store.state.controle.countColetasPendentesData;
		},
		countColetasAndamentoData() {
			return this.$store.state.controle.countColetasAndamentoData;
		},
		countEntregasPendentesData() {
			return this.$store.state.controle.countEntregasPendentesData;
		},
		countEntregasAndamentoData() {
			return this.$store.state.controle.countEntregasAndamentoData;
		},
		countSolicitacoesFinalizadasData() {
			return this.$store.state.controle.countSolicitacoesFinalizadasData;
		},
		coletasPendentesData() {
			return this.$store.state.controle.coletasPendentesData;
		},
		pnDefinirVeiculos() {
			return this.$store.state.controle.definirVeiculos;
		},
		coletasAndamentoData() {
			return this.$store.state.controle.coletasAndamentoData;
		},
		entregasPendentesData() {
			return this.$store.state.controle.entregasPendentesData;
		},
		entregasAndamentoData() {
			return this.$store.state.controle.entregasAndamentoData;
		},
		veiculosFrotaData() {
			return this.$store.state.controle.veiculosFrotaData;
		},
		pnVeiculoCarga() {
			return this.$store.state.controle.veiculoCarga;
		},
		pnVeiculosBaldeacao() {
			return this.$store.state.controle.exibirVeiculosBaldeacao;
		},
		solicitacoesFinalizadasData() {
			return this.$store.state.controle.solicitacoesFinalizadasData;
		}
	},
	methods: {
		intervalContadores() {
			this.atualizarContadores = setInterval(() => {
				//Aqui por algum motivo não podemos chamar a função (Ex: countColetasPendentes), temos que passar
				//a chamada direto do store, caso contrário não reconhece a função e emite um erro.
				this.$store
					.dispatch(
						"controle/countColetasPendentes",
						this.$store.state.controle.coletasPendentesFiltros
					)
					.catch(err => {
						console.error(err);
					});
				this.$store
					.dispatch("controle/countColetasAndamento")
					.catch(err => {
						console.error(err);
					});
				this.$store
					.dispatch("controle/countEntregasPendentes")
					.catch(err => {
						console.error(err);
					});
				this.$store
					.dispatch("controle/countEntregasAndamento")
					.catch(err => {
						console.error(err);
					});
				this.$store
					.dispatch("controle/countSolicitacoesFinalizadas")
					.catch(err => {
						console.error(err);
					});
			}, 30000);
		},
		async exibirPaineis(painel) {
			if (painel == "ColetasPendentes") {
				if (
					this.pnColetasPendentes == false ||
					this.countColetasPendentesData !=
						this.coletasPendentesData.length
				) {
					await this.getDataAtual();
					await this.getColetasPendentes(
						this.$store.state.controle.coletasPendentesFiltros
					);
				}
				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "ColetasAndamento") {
				if (
					this.pnColetasAndamento == false ||
					this.countColetasAndamentoData !=
						this.coletasAndamentoData.length
				) {
					await this.getDataAtual();
					await this.getColetasAndamento();
				}
				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "EntregasPendentes") {
				if (
					this.pnEntregasPendentes == false ||
					this.countEntregasPendentesData !=
						this.entregasPendentesData.length
				) {
					await this.getDataAtual();
					await this.getEntregasPendentes();
				}
				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "EntregasAndamento") {
				if (
					this.pnEntregasAndamento == false ||
					this.countEntregasAndamentoData !=
						this.entregasAndamentoData.length
				) {
					await this.getDataAtual();
					await this.getEntregasAndamento();
				}
				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "Veiculos") {
				if (this.pnVeiculosFrota == false) {
					await this.getVeiculosFrota(
						this.$store.state.controle.veiculosFrotaFiltros
					);
				}
				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "SolicitacoesFinalizadas") {
				if (
					this.pnSolicitacoesFinalizadas == false ||
					this.countSolicitacoesFinalizadasData !=
						this.solicitacoesFinalizadasData.length
				) {
					await this.getDataAtual();
					await this.getSolicitacoesFinalizadas();
				}
				await this.exibirPainelSelecionado(painel);
			}
		},
		exibirPainelSelecionado(painel) {
			this.pnColetasPendentes =
				painel == "ColetasPendentes" ? true : false;
			this.pnColetasAndamento =
				painel == "ColetasAndamento" ? true : false;
			this.pnEntregasPendentes =
				painel == "EntregasPendentes" ? true : false;
			this.pnEntregasAndamento =
				painel == "EntregasAndamento" ? true : false;
			this.pnVeiculosFrota = painel == "Veiculos" ? true : false;
			this.pnSolicitacoesFinalizadas =
				painel == "SolicitacoesFinalizadas" ? true : false;
		}
	}
};
</script>

<style lang="scss">
.con-vs-dialog.max550 .vs-dialog {
	max-width: 550px;
}
.con-vs-popup.minh400 .vs-popup {
	min-height: 400px;
}
.con-vs-popup.minh450 .vs-popup {
	min-height: 450px;
}
.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}
</style>
