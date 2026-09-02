<template>
	<div>
		<!-- Dados da coleta -->
		<div class="vx-row w-full mb-base">
			<div class="vx-col w-full">
				<vx-card>
					<vs-table
						ref="table"
						:noDataText="coleta_atual.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						:data="coleta_atual"
					>
						<template>
							<vs-tr>
								<vs-td class="whitespace-no-wrap">
									<div>
										<span class="font-semibold" style="font-size: 12px">Empresa</span>
									</div>
									<div>
										<span>{{coleta_atual_index.nome_empresa}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Solicitação</span>
									</div>
									<div>
										<span v-if="coleta_atual_index.numero != null">{{coleta_atual[0].numero}}</span>
										<span v-else>ID: {{coleta_atual_index.coleta_id}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Local Coleta</span>
									</div>
									<div>
										<span>{{coleta_atual_index.local_coleta | truncate(25)}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Data Coleta</span>
									</div>
									<div>
										<span>{{coleta_atual_index.dt_efet_coleta | moment("DD MMM")}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Hora Coleta</span>
									</div>
									<div>
										<span>{{coleta_atual_index.hr_sai_coleta | hora_min}}</span>
									</div>
								</vs-td>
								<vs-td class="whitespace-no-wrap" align="center">
									<div>
										<span class="font-semibold" style="font-size: 12px">Placa Coleta</span>
									</div>
									<div>
										<span>{{coleta_atual_index.placa_coleta}}</span>
									</div>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<!-- Notas -->
		<div class="vx-row w-full mb-base">
			<div class="vx-col w-full">
				<vx-card v-show="notas.length > 0">
					<vs-table
						ref="table"
						:noDataText="notas.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						stripe
						:data="notas"
					>
						<div slot="header" class="flex items-center flex-grow justify-between mb-4">
							<div class="flex items-center">
								<span
									v-if="coleta_atual_index.qtde_notas_distrib > 0"
									class="font-semibold"
								>Notas fiscais a serem distribuídas</span>
								<span v-else class="font-semibold">Notas fiscais distribuídas</span>
							</div>
						</div>

						<template slot="thead">
							<vs-th>Número</vs-th>
							<vs-th>Volumes</vs-th>
							<vs-th>Valor</vs-th>
							<vs-th class="whitespace-no-wrap">Local de Entrega</vs-th>
							<vs-th class="whitespace-no-wrap">CNPJ / CPF</vs-th>
							<vs-th class="whitespace-no-wrap">Prev. Entrega</vs-th>
							<vs-th>Urgente</vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">{{data[indextr].numero}}</vs-td>
								<vs-td>{{data[indextr].volumes}}</vs-td>
								<vs-td
									class="whitespace-no-wrap"
								>{{data[indextr].valor | currency('', 2, { thousandsSeparator: '.', decimalSeparator: ',' })}}</vs-td>

								<vs-td class="whitespace-no-wrap">
									<template v-if="data[indextr].solic_destino_id > 0">
										<span>{{data[indextr].local_entrega | truncate(40)}}</span>
									</template>
									<template v-else>
										<span
											v-if="data[indextr].local_entrega != null"
											class="text-inherit hover:underline cursor-pointer"
											@click="editRecord(indextr, data[indextr])"
										>{{data[indextr].local_entrega | truncate(40)}}</span>
										<span
											v-else
											class="text-primary hover:underline cursor-pointer"
											style="font-size: 12px;"
											@click="editRecord(indextr, data[indextr])"
										>Selecione o local de entrega</span>
									</template>
								</vs-td>

								<vs-td class="whitespace-no-wrap">{{data[indextr].cpf_cnpj}}</vs-td>

								<vs-td class="whitespace-no-wrap">{{data[indextr].hr_prev_entrega | hora_min}}</vs-td>

								<vs-td class="whitespace-no-wrap">{{data[indextr].entrega_urgente | sim_nao}}</vs-td>

								<vs-td class="whitespace-no-wrap">
									<feather-icon
										v-if="data[indextr].solic_destino_id > 0"
										icon="LockIcon"
										svgClasses="w-5 h-5'"
									/>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<vs-popup class="holamundo" :title="'Nota: ' + nro_nota" :active.sync="popupEdit">
			<div class="vx-row mb-6">
				<div class="vx-col w-full">
					<label class="vs-input--label">Local de Entrega</label>
					<v-select label="nome_cnpj" v-model="clienteCombo" :options="clienteData" clearable>
						<template v-slot:option="option">
							<span class="mr-2">{{option.nome}}</span>
							<span v-if="option.cpf_cnpj != null">({{option.cpf_cnpj}})</span>
						</template>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div class="vx-row mb-6">
				<div class="vx-col w-full">
					<label class="vs-input--label">Prev. Entrega</label>
					<flat-pickr
						:config="configdateTimePickerTime"
						v-model="prev_entrega"
						class="w-full vs-inputx vs-input--input normal hasValue"
						style="border: 1px solid rgba(0, 0, 0, 0.2);"
					/>
				</div>
			</div>

			<div class="vx-row mb-6">
				<div class="vx-col">
					<vs-checkbox v-model="entrega_urgente" vs-value="S">Entrega urgente</vs-checkbox>
				</div>
			</div>

			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />
			<br />

			<div class="vx-col w-full">
				<vs-button class="mr-3" type="border" @click="salvar()">Salvar</vs-button>
				<vs-button type="border" color="danger" @click="popupEdit=false">Cancelar</vs-button>
			</div>
		</vs-popup>

		<div class="vx-row w-full mb-base" v-if="coleta_atual_index.qtde_notas_distrib > 0">
			<div class="vx-col w-full">
				<vx-card>
					<div class="vx-row">
						<div class="vx-col">
							<vs-alert icon-pack="feather" icon="icon-info" class="my-4 mb-6 w-full" color="warning">
								<div>
									<span
										class="mr-1"
									>As solicitações de entrega serão automaticamente autorizadas para o veículo:</span>
									<span class="font-semibold">{{this.coleta_atual_index.placa_coleta}}</span>
								</div>
							</vs-alert>
							<vs-checkbox
								class="mb-6"
								v-model="confirmar"
								vs-value="S"
							>O local de entrega de cada nota fiscal está correto</vs-checkbox>
							<vs-button
								:disabled="confirmar==null"
								@click="gerarSolicAuxiliarMultiDestinos()"
							>Gerar Solicitações de Entrega</vs-button>
						</div>
					</div>
				</vx-card>
			</div>
		</div>
	</div>
</template>

<script>
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
	components: {
		"v-select": vSelect,
		flatPickr
	},
	data() {
		return {
			configdateTimePickerTime: {
				enableTime: true,
				enableSeconds: false,
				noCalendar: true,
				locale: Portuguese
			},

			coleta_atual: [],
			coleta_atual_index: [],
			notas: [],

			clienteData: [],

			popupEdit: false,
			nro_nota: null,
			cod_loc_entrega: null,
			local_entrega: null,
			cpf_cnpj: null,
			prev_entrega: null,
			entrega_urgente: null,
			nota_index: null,

			confirmar: null
		};
	},
	async created() {
		await this.retornarColetasMultiDestinosRealizadasAtual();
		this.getCliente();
	},
	computed: {
		clienteCombo: {
			get() {
				if (this.local_entrega == null) {
					return "Selecione o local de entrega";
				} else {
					return {
						nome_cnpj: this.local_entrega
					};
				}
			},
			set(obj) {
				this.cod_loc_entrega = obj.codigo;
				this.local_entrega = obj.nome;
				this.cpf_cnpj = obj.cpf_cnpj;
			}
		}
	},
	methods: {
		async retornarColetasMultiDestinosRealizadasAtual() {
			await this.$http
				.get(`api/RetornarColetasMultiDestinosRealizadas`, {
					headers: {
						Authorization:
							"Bearer " + localStorage.getItem("apiToken")
					},
					params: {
						coleta_id: this.$route.params.coleta_id
					}
				})
				.then(response => {
					this.coleta_atual = response.data.dados.coletas;
					this.coleta_atual_index = response.data.dados.coletas[0];
					this.notas = response.data.dados.coletas[0].notas;
				})
				.catch();
		},
		async getCliente() {
			await this.$http
				.post(
					`api/getDadosCliente`,
					{
						empresa: this.coleta_atual[0].empresa
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(response => {
					if (response.data.status) {
						this.clienteData = response.data.cliente;
					}
				})
				.catch();
		},
		async editRecord(index, dados) {
			this.nro_nota = dados.numero;
			this.cod_loc_entrega = dados.cod_loc_entrega;
			this.local_entrega = dados.local_entrega;
			this.cpf_cnpj = dados.cpf_cnpj;
			this.prev_entrega =
				dados.hr_prev_entrega == null
					? new Date()
					: dados.hr_prev_entrega;
			this.entrega_urgente = dados.entrega_urgente != null ? "S" : null;
			this.nota_index = index;
			this.popupEdit = !this.popupEdit;
		},
		salvar() {
			this.notas[this.nota_index].cod_loc_entrega = this.cod_loc_entrega;
			this.notas[this.nota_index].local_entrega = this.local_entrega;
			this.notas[this.nota_index].cpf_cnpj = this.cpf_cnpj;
			this.notas[this.nota_index].hr_prev_entrega = this.prev_entrega;
			this.notas[this.nota_index].entrega_urgente =
				this.entrega_urgente != null ? "S" : "N";
			this.popupEdit = false;
		},
		async gerarSolicAuxiliarMultiDestinos() {
			await this.$vs.loading({ scale: 0.5 });
			await this.$http
				.post(
					`api/GerarSolicAuxiliarMultiDestinos`,
					{
						solic_origem_id: this.coleta_atual_index.coleta_id,
						lista_notas: this.notas
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken")
						}
					}
				)
				.then(async response => {
					await this.$vs.loading.close();

					if (response.data.retorno.cod_retorno != "Z100") {
						await this.$vs.notify({
							time: 10000,
							text: response.data.erros[0].msg_retorno,
							iconPack: "feather",
							icon: "icon-alert-circle",
							color: "danger"
						});
					} else {
						this.$router
							.push("/distribuicao-entregas")
							.catch(() => {});
					}
				})
				.catch(async error => {
					await this.$vs.loading.close();
				});
		}
	}
};
</script>

<style lang="scss" scoped>
</style>

