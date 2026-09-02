<template>
	<div>
		<vx-card>

<!-- Linha 1 -->
<div class="vx-row mb-2" v-show="$acl.check('admin')">
  <!-- Empresa (1/5) -->
  <div class="vx-col md:w-1/5 w-full">
    <label class="text-sm opacity-75">Empresa</label>
    <v-select
      class="w-full"
      label="nome"
      clearable
      v-model="empresaCombo"
      :options="empresaData"
    >
      <template v-slot:option="option">{{ option.nome }}</template>
      <div slot="no-options">Opção não disponível</div>
    </v-select>
  </div>

  <!-- Cliente (3/5) -->
  <div class="vx-col md:w-3/5 w-full">
    <label class="text-sm opacity-75">Cliente</label>
    <v-select
      class="w-full"
      label="nome"
      :options="clienteData"
      clearable
      v-model="clienteCombo"
    >
      <template v-slot:option="option">{{ option.nome }}</template>
      <div slot="no-options">
        <span v-if="filtros[0].cod_empresa == null">Selecione a empresa</span>
        <span v-else>Opção não disponível</span>
      </div>
    </v-select>
  </div>

  <!-- Veículo (1/5) -->
  <div class="vx-col md:w-1/5 w-full">
    <label class="text-sm opacity-75">Veículo</label>
    <v-select
      class="w-full"
      label="placa"
      :options="veiculo"
      clearable
      v-model="veiculoCombo"
    >
      <template v-slot:option="option">{{ option.placa }}</template>
      <div slot="no-options">Opção não disponível</div>
    </v-select>
  </div>
</div>

<!-- Linha 2 -->
<div class="vx-row">
  <!-- Período (1/5) -->
  <div class="vx-col md:w-1/5 w-full">
    <label class="whitespace-no-wrap text-sm opacity-75">Período</label>
    <v-select
      :options="periodoOptions"
      :clearable="false"
      v-model="filtros[0].periodoSelected"
    >
      <div slot="no-options">Opção não disponível</div>
    </v-select>
  </div>

  <!-- Container Datas (3/5) -->
  <div class="vx-col md:w-3/5 w-full">
    <div class="vx-row">
      <!-- Data Inicial (1/2 do container) -->
      <div class="vx-col md:w-1/2 w-full">
        <label class="text-sm opacity-75">Data Previsão Inicial</label>
        <flat-pickr
          :config="configdateTimePickerDate"
          v-model="filtros[0].filtro_ini"
          class="w-full vs-inputx vs-input--input normal hasValue"
          style="border: 1px solid rgba(0, 0, 0, 0.2);"
        />
      </div>

      <!-- Data Final (1/2 do container) -->
      <div class="vx-col md:w-1/2 w-full">
        <label class="text-sm opacity-75">Data Previsão Final</label>
        <flat-pickr
          :config="configdateTimePickerDate"
          v-model="filtros[0].filtro_fim"
          class="w-full vs-inputx vs-input--input normal hasValue"
          style="border: 1px solid rgba(0, 0, 0, 0.2);"
        />
      </div>
    </div>
  </div>

  <!-- Nota Fiscal + botão (1/5) -->
  <div class="vx-col md:w-1/5 w-full">
    <label class="text-sm opacity-75 whitespace-no-wrap">Nota Fiscal Cliente</label>
    <div class="flex items-end">
      <vs-input
        class="w-full"
        autocomplete="off"
        name="nro_nf"
        v-mask="['#########']"
        v-model="nro_nf"
      />
      <vs-button
        class="ml-2"        
        color="primary"
        type="border"
        icon-pack="feather"
        icon="icon-search"
        @click="refresh"
        aria-label="Pesquisar"
      />
    </div>
  </div>
</div>

			
		</vx-card>

		<br />

		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="coletaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						:max-items="itemsPerPage"
						pagination
						search
						stripe
						:data="coletaData"
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
										>{{ currentPage * itemsPerPage - (itemsPerPage - 1) }} - {{ coletaData.length - currentPage * itemsPerPage > 0 ? currentPage * itemsPerPage : coletaData.length }} de {{ queriedItems }}</span>
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
							<vs-th sort-key="nome">Cliente</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="local_coleta">Coleta</vs-th>
							<vs-th class="whitespace-no-wrap" sort-key="local_entrega">Entrega</vs-th>
							<!-- Foto carga -->
							<vs-th></vs-th>
							<vs-th sort-key="status">Status</vs-th>
							<!-- Ações -->
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center text-center">
										<router-link
											v-if="data[indextr].numero != null"
											:to="url(tr.id)"
											target="_blank"
											class="text-inherit hover:underline"
										>{{data[indextr].numero}}</router-link>
										<router-link v-else :to="url(tr.id)" target="_blank" class="text-inherit hover:underline">
											<span
												v-if="data[indextr].coleta_fixa == 'C' && data[indextr].solic_origem_id != null"
												style="font-size: 12px"
											>Comanda {{data[indextr].id}}</span>
											<span v-else>ID: {{data[indextr].id}}</span>
										</router-link>
										<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
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

								<vs-td>
									<div>{{data[indextr].nome | truncate(25)}}</div>
									<div v-if="data[indextr].solic_origem_id != null">
										<span
											v-if="data[indextr].coleta_origem_numero != null"
											style="font-size: 12px; color:gray"
										>Origem: {{data[indextr].coleta_origem_numero}}</span>
										<span
											v-else
											style="font-size: 12px; color:gray"
										>Origem: ID {{data[indextr].coleta_origem_id}}</span>
									</div>
								</vs-td>								

								<vs-td>
									<div>
										<span v-if="data[indextr].coleta_fixa == 'C' && data[indextr].solic_origem_id != null">
											{{data[indextr].local_coleta_cmd}}
										</span>
										<span v-else>{{data[indextr].local_coleta | truncate(25)}}</span>
									</div>
									<div>
										<div class="flex items-center whitespace-no-wrap">
											<span v-if="data[indextr].placa_coleta != null" class="mr-2" style="font-size: 12px; color:gray">
												{{data[indextr].placa_coleta}}
											</span>
											<span style="font-size: 12px; color:gray">
												{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}
											</span>
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
										<span v-if="data[indextr].coleta_fixa == 'C' && data[indextr].solic_origem_id != null">
											{{data[indextr].local_entrega_cmd}}
										</span>
										<span v-else>{{data[indextr].local_entrega | truncate(25)}}</span>
									</div>
									<div>
										<div class="flex items-center whitespace-no-wrap">										
											<span v-if="data[indextr].placa_entrega != null" class="mr-2" style="font-size: 12px; color:gray">
												{{data[indextr].placa_entrega}}
											</span>
											<span style="font-size: 12px; color:gray">
												{{exibirDia(data[indextr].dt_efet_entrega) | moment("DD MMM") }} {{data[indextr].hr_sai_entrega | hora_min }}
											</span>
											<feather-icon
												v-if="data[indextr].hr_sai_entrega != null"
												class="ml-1 mr-2"
												icon="CheckCircleIcon"
												svgClasses="w-4 h-4 text-success"
											/>
											<span v-if="['R', 'D'].includes(data[indextr].reentrega)" class="badge_cinza">reentrega</span>
										</div>
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
													@click="romaneios(coletaUrlImgData, data[indextr])"
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
		<auditoria-pop-up />
	</div>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
import NotasFiscaisPopUp from "@/components/rgsoft/NotasFiscaisPopUp.vue";
import RomaneiosPopUp from "@/components/rgsoft/RomaneiosPopUp.vue";
import AuditoriaPopUp from "@/components/rgsoft/AuditoriaPopUp.vue";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	mixins: [procsMixins, coletaMixins],
	components: {
		vSelect,
		flatPickr,
		NotasFiscaisPopUp,
		RomaneiosPopUp,
		AuditoriaPopUp,
		ColFixa,
	},
	data() {
		return {
			selected: [],
			itemsPerPage: 10,
			isMounted: false,

			configdateTimePickerDate: {
				altInput: true,
				altFormat: "d/m/Y",
				dateFormat: "Y-m-d",
				locale: Portuguese,
			},

			nro_nf: null,
			filtros: [
				{
					cod_empresa: null,
					empresa: null,
					cliente_id: this.$route.query.cliente_id,
					nome_cliente: decodeURIComponent(this.$route.query.nome),
					placa: null,
					periodoSelected: { label: "Ambos", value: "A" },
					filtro_ini: this.inicioMes(),
					filtro_fim: new Date(),
				},
			],

			clienteData: [],

			periodoOptions: [
				{ label: "Ambos", value: "A" },
				{ label: "Coleta", value: "C" },
				{ label: "Entrega", value: "E" },
			],
		};
	},
	async created() {
		await this.getEmpresa();
		await this.lerVeiculo();		
		await this.getDataAtual();
		//Não queremos que entre pesquisando.
		//await this.getColeta();
	},
	mounted() {
		this.isMounted = true;
	},
	watch: {
		'filtros[0].empresa': function (newVal, oldVal) {
			this.refresh();
		}
	},
	computed: {
		coletaData() {
			return this.$store.state.coleta.coletaData;
		},
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		},
		empresaData() {
			return this.$store.state.empresa.empresaData;
		},
		veiculo() {
			return this.$store.state.veiculo.veiculoPesqData;
		},
		coletaUrlImgData() {
			return this.$store.state.coleta.coletaUrlImgData;
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
				: this.coletaData.length;
		},
		empresaCombo: {
			get() {
				if (this.filtros[0].empresa == null) {
					return "Todas";
				} else {
					return {
						nome: this.filtros[0].empresa,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.filtros[0].cod_empresa = obj.codigo;
					this.filtros[0].empresa = obj.nome;
				} else {
					this.filtros[0].cod_empresa = null;
					this.filtros[0].empresa = null;
				}
				this.getCliente();
			},
		},
		clienteCombo: {
			get() {
				if (this.filtros[0].cliente_id == null) {
					return "Todos";
				} else {
					return {
						nome: this.filtros[0].nome_cliente,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.filtros[0].cliente_id = obj.id;
					this.filtros[0].nome_cliente = obj.nome;
				} else {
					this.filtros[0].cliente_id = null;
					this.filtros[0].nome_cliente = null;
				}
			},
		},
		veiculoCombo: {
			get() {
				if (this.filtros[0].placa == null) {
					return "Todos";
				} else {
					return {
						placa: this.filtros[0].placa,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.filtros[0].placa = obj.placa;
				} else {
					this.filtros[0].placa = null;
				}
			},
		},
	},
	methods: {
		async getColeta() {
			const payload = {
				cod_empresa: this.filtros[0].cod_empresa,
				cliente_id: this.filtros[0].cliente_id,
				placa: this.filtros[0].placa,
				periodo: this.filtros[0].periodoSelected.value,
				filtro_ini: this.filtros[0].filtro_ini,
				filtro_fim: this.filtros[0].filtro_fim,
				nro_nf: this.nro_nf,
			};

			await this.$store
				.dispatch("coleta/indexColeta", payload)
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
		async getEmpresa() {
			await this.$store.dispatch("empresa/indexEmpresa").catch((err) => {
				console.error(err);
			});
		},
		async getCliente() {
			let cod_empresa;

			if (this.filtros[0].cod_empresa == null) {
				cod_empresa = -1;
			} else {
				cod_empresa = this.filtros[0].cod_empresa;
			}

			this.filtros[0].cliente_id = null;
			this.filtros[0].nome_cliente = null;

			await this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: cod_empresa,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then((response) => {
					if (response.data.status) {
						this.clienteData = response.data.cliente;
					}
				})
				.catch();
		},
		async lerVeiculo() {
			await this.$store.dispatch("veiculo/lerVeiculo").catch((err) => {
				console.error(err);
			});
		},
		async refresh() {
			const { filtro_ini, filtro_fim, cliente_id, placa } = this.filtros[0];

			// Verifica se ambas as datas são válidas
			if (!cliente_id && !placa && !this.nro_nf) {
				if (filtro_ini && filtro_fim) {
					const diffMs = new Date(filtro_fim) - new Date(filtro_ini);
					const diffDias = diffMs / (1000 * 60 * 60 * 24);

					if (diffDias > 30) {
						this.$vs.notify({
							title: "Período inválido",
							text: "O intervalo de datas não pode ultrapassar 30 dias.<br><br>Para pesquisas mais amplas, informe o Cliente, Veiculo ou Nota Fiscal.",
							color: "danger",
							time: 8000,
						});
						return; // interrompe a execução
					}
				}
			}

			await this.$vs.loading({ scale: 0.5 });
			await this.getColeta();
			await this.getDataAtual();
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
		async auditoria(coleta_id) {
			await this.$vs.loading({ scale: 0.5 });
			await this.getColetaLog(coleta_id);
			await this.getColetaPos(coleta_id);
			await this.retornarInstrucoesColeta(coleta_id);
			await this.$store.commit("EXIBIR_AUDITORIA");
			await this.$vs.loading.close();
		},
		exibirFotoCarga(dados) {
			let titulo;
			if (dados.numero != null) {
				titulo = "Carga da coleta: " + dados.numero;
			} else {
				titulo = "Carga da coleta: ID " + dados.id;
			}
			this.exibirFoto(this.coletaUrlImgData + dados.img_carga, titulo);
		},
	},
};
</script>

<style lang="scss" scoped>
.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}
</style>