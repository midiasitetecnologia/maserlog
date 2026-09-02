<template>
	<div>
		<vx-card class="mb-base">
			<div class="vx-row">
				<div class="vx-col md:w-5/12 sm:w-full w-full">
					<label class="text-sm opacity-75">Início (Data Prevista Coleta)</label>
					<flat-pickr
						:config="configdateTimePickerDate"
						v-model="filtros[0].data_ini"
						class="w-full vs-inputx vs-input--input normal hasValue"
						style="border: 1px solid rgba(0, 0, 0, 0.2);"
					/>
				</div>
				<div class="vx-col md:w-5/12 sm:w-full w-full">
					<label class="text-sm opacity-75">Fim (Data Prevista Coleta)</label>
					<flat-pickr
						:config="configdateTimePickerDate"
						v-model="filtros[0].data_fim"
						class="w-full vs-inputx vs-input--input normal hasValue"
						style="border: 1px solid rgba(0, 0, 0, 0.2);"
					/>
				</div>
				<div class="vx-col md:w-2/12 sm:w-full w-full">
					<label class="text-sm opacity-0">Pesquisar</label>
					<vs-button
						color="primary"
						type="border"
						icon-pack="feather"
						icon="icon-search"
						@click="refresh"
					></vs-button>
				</div>
			</div>
		</vx-card>

		<div class="vx-row">
			<div class="vx-col w-full md:w-1/2 lg:w-1/4 xl:w-1/4">
				<span class="cursor-pointer" @click="exibirPaineis('Clientes')">
					<statistics-card-line
						hideChart
						class="mb-base"
						icon="UsersIcon"
						icon-right
						:statistic="resumoKmTempoClienteData.length > 0 ? resumoKmTempoClienteData.length -1 : '0'"												
						:destacarPainel="pnCliente ? true : false"
						statisticTitle="Cliente"
						color="primary"
					/>
				</span>
			</div>

			<div class="vx-col w-full md:w-1/2 lg:w-1/4 xl:w-1/4">
				<span class="cursor-pointer" @click="exibirPaineis('Veiculos')">
					<statistics-card-line
						hideChart
						class="mb-base"
						icon="TruckIcon"
						icon-right
						:statistic="resumoKmTempoVeiculoData.length > 0 ? resumoKmTempoVeiculoData.length -1 : '0'"												
						:destacarPainel="pnVeiculo ? true : false"
						statisticTitle="Veículo"
						color="danger"
					/>
				</span>
			</div>

			<div class="vx-col w-full md:w-1/2 lg:w-1/4 xl:w-1/4">
				<span class="cursor-pointer" @click="exibirPaineis('TipoVeiculos')">
					<statistics-card-line
						hideChart
						class="mb-base"
						icon="TagIcon"
						icon-right
						:statistic="resumoKmTempoTipoVeiculoData.length > 0 ? resumoKmTempoTipoVeiculoData.length -1 : '0'"
						:destacarPainel="pnTipoVeiculo ? true : false"
						statisticTitle="Tipo de Veículo"
						color="warning"
					/>
				</span>
			</div>

			<div class="vx-col w-full md:w-1/2 lg:w-1/4 xl:w-1/4">
				<span class="cursor-pointer" @click="exibirPaineis('Motoristas')">
					<statistics-card-line
						hideChart
						class="mb-base"
						icon="CrosshairIcon"
						icon-right
						:statistic="resumoKmTempoMotoristaData.length > 0 ? resumoKmTempoMotoristaData.length -1 : '0'"						
						:destacarPainel="pnMotorista ? true : false"
						statisticTitle="Motorista"
						color="success"
					/>
				</span>
			</div>
		</div>

		<!-- Usar "v-show" ao invés de "v-if" nesta situação, caso contrário ocorrerá erro de "TypeError: Cannot read property 'currentx' of undefined.
		o v-show executa o código mas não mostra, enquanto o v-if não executa. E o valor de currentX está atrelado a tabela existir de alguma forma.-->
		<div class="vx-row" v-show="(resumoKmTempoData.length > 0)">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="resumoKmTempoData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						search
						stripe
						:data="resumoKmTempoData"
					>
						<div slot="header" class="flex flex-wrap-reverse items-center flex-grow justify-between">
							<div class="flex mb-1">
								<span class="mt-1 ml font-semibold">{{painelSelecionado}}</span>
							</div>

							<div class="flex flex-wrap-reverse items-center">
								<vs-button class="mr-4" type="border" size="small" @click="exportar">Exportar</vs-button>
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ resumoKmTempoData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : resumoKmTempoData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="descricao">{{colunaDescricao}}</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_coletas">
								<span v-if="pnCliente">Solicitações</span>
								<span v-else>Coletas</span>
							</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_km_coleta">Km Coletas</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_tempo_coleta">Tempo Coletas</vs-th>
							<vs-th
								v-if="pnCliente == false"
								class="whitespace-no-wrap"
								sort-key="total_entregas"
							>Entregas</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_km_entrega">Km Entregas</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_tempo_entrega">Tempo Entregas</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_km">Total Km</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="total_tempo">Total Tempo</vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{data[indextr].descricao}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{data[indextr].total_coletas}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{formatKmFromMeters(data[indextr].total_km_coleta)}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{data[indextr].total_tempo_coleta | hora_min}}</span>
								</vs-td>
								<vs-td v-if="pnCliente == false">
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{data[indextr].total_entregas}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{formatKmFromMeters(data[indextr].total_km_entrega)}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{data[indextr].total_tempo_entrega | hora_min}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{formatKmFromMeters(data[indextr].total_km)}}</span>
								</vs-td>
								<vs-td>
									<span
										:class="data[indextr].descricao == 'TOTAL' ? 'font-semibold' : ''"
									>{{data[indextr].total_tempo | hora_min}}</span>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>
		<vs-prompt
			:title="'Exportar ' + painelSelecionado"
			class="export-options"
			:is-valid="fileName != ''"
			@cancel="clearFields"
			@accept="exportToExcel"
			accept-text="Exportar"
			cancel-text="Fechar"
			@close="clearFields"
			:active.sync="activePrompt"
		>
			<vs-input v-model="fileName" placeholder="Digite o nome do arquivo..." class="w-full" />
			<v-select v-model="selectedFormat" :options="formats" :clearable="false" class="my-4" />
		</vs-prompt>
	</div>
</template>

<script>
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
import StatisticsCardLine from "@/components/statistics-cards/StatisticsCardLine.vue";

export default {
	components: {
		vSelect,
		flatPickr,
		StatisticsCardLine
	},
	data() {
		return {
			resumoKmTempoData: [],
			selected: [],

			itemsPerPage: 10,
			isMounted: false,

			pnCliente: false,
			pnVeiculo: false,
			pnTipoVeiculo: false,
			pnMotorista: false,
			painelSelecionado: null,
			ultimoPainel: null,
			colunaDescricao: "Descrição",

			filtros: [
				{
					data_ini: this.inicioMes(),
					data_fim: new Date()
				}
			],

			configdateTimePickerDate: {
				altInput: true,
				altFormat: "d/m/Y",
				dateFormat: "Y-m-d",
				locale: Portuguese
			},

			fileName: "",
			headerTitle: [],
			headerVal: [],
			formats: ["xlsx", "csv", "txt"],
			selectedFormat: "xlsx",
			activePrompt: false
		};
	},
	created() {
		//Não queremos que entre pesquisando.
		//this.getResumoKmTempo();
	},
	mounted() {
		this.isMounted = true;
	},
	computed: {
		resumoKmTempoClienteData() {
			return this.$store.state.coleta.resumoKmTempoClienteData;
		},
		resumoKmTempoVeiculoData() {
			return this.$store.state.coleta.resumoKmTempoVeiculoData;
		},
		resumoKmTempoTipoVeiculoData() {
			return this.$store.state.coleta.resumoKmTempoTipoVeiculoData;
		},
		resumoKmTempoMotoristaData() {
			return this.$store.state.coleta.resumoKmTempoMotoristaData;
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
				: this.resumoKmTempoData.length;
		}
	},
	methods: {
		async getResumoKmTempo() {
			await this.getResumoKmTempoCliente();
			await this.getResumoKmTempoVeiculo();
			await this.getResumoKmTempoTipoVeiculo();
			await this.getResumoKmTempoMotorista();
		},
		async getResumoKmTempoCliente() {
			await this.$store
				.dispatch(
					"coleta/retornarTotaisKmTempoCliente",
					this.filtros[0]
				)
				.catch(err => {
					console.error(err);
				});
		},
		async getResumoKmTempoVeiculo() {
			await this.$store
				.dispatch(
					"coleta/retornarTotaisKmTempoVeiculo",
					this.filtros[0]
				)
				.catch(err => {
					console.error(err);
				});
		},
		async getResumoKmTempoTipoVeiculo() {
			await this.$store
				.dispatch(
					"coleta/retornarTotaisKmTempoTipoVeiculo",
					this.filtros[0]
				)
				.catch(err => {
					console.error(err);
				});
		},
		async getResumoKmTempoMotorista() {
			await this.$store
				.dispatch(
					"coleta/retornarTotaisKmTempoMotorista",
					this.filtros[0]
				)
				.catch(err => {
					console.error(err);
				});
		},
		inicioMes() {
			var date = new Date();
			var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
			return firstDay;
		},
		async refresh() {

			const { data_ini, data_fim } = this.filtros[0];

			// Verifica se ambas as datas são válidas
			if (data_ini && data_fim) {
				const diffMs = new Date(data_fim) - new Date(data_ini); // diferença em ms
				const diffDias = diffMs / (1000 * 60 * 60 * 24);

				if (diffDias > 30) {
				this.$vs.notify({
					title: "Período inválido",
					text: "O intervalo de datas não pode ultrapassar 30 dias.",
					color: "danger",
				});
				return; // interrompe a execução
				}
			}

			await this.$vs.loading({ scale: 0.5 });
			await this.getResumoKmTempo();
			await this.exibirPaineis(this.ultimoPainel);
			await this.$vs.loading.close();
		},
		async exibirPaineis(painel) {
			if (painel == "Clientes") {
				this.resumoKmTempoData = JSON.parse(
					JSON.stringify(this.resumoKmTempoClienteData)
				);
				this.painelSelecionado = "Clientes";
				this.colunaDescricao = "Nome";

				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "Veiculos") {
				this.resumoKmTempoData = JSON.parse(
					JSON.stringify(this.resumoKmTempoVeiculoData)
				);
				this.painelSelecionado = "Veículos";
				this.colunaDescricao = "Placa";

				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "TipoVeiculos") {
				this.resumoKmTempoData = JSON.parse(
					JSON.stringify(this.resumoKmTempoTipoVeiculoData)
				);
				this.painelSelecionado = "Tipos de Veículo";
				this.colunaDescricao = "Descrição";

				await this.exibirPainelSelecionado(painel);
			}
			if (painel == "Motoristas") {
				this.resumoKmTempoData = JSON.parse(
					JSON.stringify(this.resumoKmTempoMotoristaData)
				);
				this.painelSelecionado = "Motoristas";
				this.colunaDescricao = "Nome";

				await this.exibirPainelSelecionado(painel);
			}
			this.ultimoPainel = painel;
		},
		exibirPainelSelecionado(painel) {
			this.pnCliente = painel == "Clientes" ? true : false;
			this.pnVeiculo = painel == "Veiculos" ? true : false;
			this.pnTipoVeiculo = painel == "TipoVeiculos" ? true : false;
			this.pnMotorista = painel == "Motoristas" ? true : false;
		},
		exportar() {
			this.fileName = "Resumos Km + Hrs " + this.painelSelecionado;
			this.activePrompt = true;
		},
		exportToExcel() {
			import("@/vendor/Export2Excel").then(excel => {
				if (this.pnCliente) {
					this.headerTitle = [
						this.colunaDescricao,
						"Solicitações",
						"Km Coletas",
						"Tempo Coletas",
						"Km Entregas",
						"Tempo Entregas",
						"Total Km",
						"Total Tempo"
					];
					this.headerVal = [
						"descricao",
						"total_coletas",
						"total_km_coleta",
						"total_tempo_coleta",
						"total_km_entrega",
						"total_tempo_entrega",
						"total_km",
						"total_tempo"
					];
				} else {
					this.headerTitle = [
						this.colunaDescricao,
						"Coletas",
						"Km Coletas",
						"Tempo Coletas",
						"Entregas",
						"Km Entregas",
						"Tempo Entregas",
						"Total Km",
						"Total Tempo"
					];
					this.headerVal = [
						"descricao",
						"total_coletas",
						"total_km_coleta",
						"total_tempo_coleta",
						"total_entregas",
						"total_km_entrega",
						"total_tempo_entrega",
						"total_km",
						"total_tempo"
					];
				}

				const list = this.resumoKmTempoData;
				const data = this.formatJson(this.headerVal, list);

				excel.export_json_to_excel({
					header: this.headerTitle,
					data,
					filename: this.fileName,
					autoWidth: true,
					bookType: this.selectedFormat
				});
				this.clearFields();
			});
		},
		formatJson(filterVal, jsonData) {
			const kmFields = ["total_km_coleta", "total_km_entrega", "total_km"];
			return jsonData.map(v =>
				filterVal.map(j => {
				if (kmFields.includes(j)) {
					return this.formatKmFromMeters(v[j]);
				}
				return v[j];
				})
			);
		},
		clearFields() {
			(this.filename = ""), (this.selectedFormat = "xlsx");
		},
		formatKmFromMeters(value) {
			if (!value || isNaN(value)) return '0,0';
			const km = parseFloat(value) / 1000;
			return `${km.toLocaleString('pt-BR', {
				minimumFractionDigits: 1,
				maximumFractionDigits: 1
			})}`;
		}
	}
};
</script>

<style lang="scss" scoped>
</style>
