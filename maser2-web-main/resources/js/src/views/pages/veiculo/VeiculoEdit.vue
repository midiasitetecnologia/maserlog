<template>
	<div class="page-veiculo-edit">
		<vs-alert
			color="danger"
			title="Veículo não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de veículo com a placa: {{$route.params.placa}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'veiculo'}" class="text-inherit underline">Veículos</router-link>
			</span>
		</vs-alert>

		<vs-alert
			color="danger"
			:active.sync="tem_erros"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<ul>
				<li v-for="(value, i) in erros_form" :key="i">{{ value[0] }}</li>
			</ul>
		</vs-alert>

		<br v-show="tem_erros" />

		<div class="vx-col w-full mb-base">
			<vx-card title="Identificação" v-if="!data_not_found">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label for class="vs-input--label">Placa</label>
						<input
							disabled="true"
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:8'"
							data-vv-as="placa"
							maxlength="8"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="placa"
							v-model="veiculoData.placa"
							v-mask="['AAA-#X##']"
						/>
						<span class="text-danger text-sm" v-show="errors.has('placa')">{{ errors.first('placa') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">Tipo de Veículo</label>
						<v-select
							class="w-full"
							label="descricao"
							:options="tipoVeiculo"
							:clearable="false"
							v-model="tipoVeiculoCombo"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<!-- <div class="vx-row mb-6">
					//Não estamos utilizando este campo.
					<div class="vx-col">
						<vs-checkbox v-model="milkRunCheck" vs-value="S">Milk Run</vs-checkbox>
					</div>
				</div>-->

				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="ativoCheck" vs-value="S">Ativo</vs-checkbox>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card title="Capacidade" v-if="!data_not_found">
				<div class="vx-row mb-6">
					<div class="vx-col w-full mb-2">
						<label class="vs-input--label">Sistema de Carga</label>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="empilhaCheck" vs-value="S">Empilhadeira</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="ponteCheck" vs-value="S">Ponte Rolante</vs-checkbox>
					</div>
					<div class="vx-col">
						<vs-checkbox v-model="manualCheck" vs-value="S">Manual</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col md:w-1/3 sm:w-1/3 w-full">
						<label for class="vs-input--label">Comprimento</label>
						<input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="comprimento"
							maxlength="7"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="comprimento"
							v-model="veiculoData.comprimento"
							v-money="money"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('comprimento')"
						>{{ errors.first('comprimento') }}</span>
					</div>

					<div class="vx-col md:w-1/3 sm:w-1/3 w-full">
						<label for class="vs-input--label">Largura</label>
						<input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="largura"
							maxlength="7"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="largura"
							v-model="veiculoData.largura"
							v-money="money"
						/>
						<span class="text-danger text-sm" v-show="errors.has('largura')">{{ errors.first('largura') }}</span>
					</div>

					<div class="vx-col md:w-1/3 sm:w-1/3 w-full">
						<label for class="vs-input--label">Altura</label>
						<input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="altura"
							maxlength="7"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="altura"
							v-model="veiculoData.altura"
							v-money="money"
						/>
						<span class="text-danger text-sm" v-show="errors.has('altura')">{{ errors.first('altura') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col md:w-1/3 sm:w-1/3 w-full">
						<label for class="vs-input--label">Capacidade Cúbica</label>
						<input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:11'"
							data-vv-as="capacidade cúbica"
							maxlength="11"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="cap_cub"
							v-model="veiculoData.cap_cub"
							v-money="money"
						/>
						<span class="text-danger text-sm" v-show="errors.has('cap_cub')">{{ errors.first('cap_cub') }}</span>
					</div>

					<div class="vx-col md:w-1/3 sm:w-1/3 w-full">
						<label for class="vs-input--label">Capacidade KG</label>
						<input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:11'"
							data-vv-as="capacidade kg"
							maxlength="11"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="cap_kg"
							v-model="veiculoData.cap_kg"
							v-money="money"
						/>
						<span class="text-danger text-sm" v-show="errors.has('cap_kg')">{{ errors.first('cap_kg') }}</span>
					</div>

					<div class="vx-col md:w-1/3 sm:w-1/3 w-full">
						<label class="vs-input--label">Nível de consumo</label>
						<v-select v-model="nivelCons_local" :options="nivelConsOptions" clearable>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
						<label class="vs-input--label">Utilizar GPS</label>
						<v-select v-model="usarGps_local" :options="usarGpsOptions" :clearable="false">
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>

					<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
						<label for class="vs-input--label">Placa Cavalo</label>
						<input
							autocomplete="off"
							maxlength="8"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="placa_cavalo"
							v-model="veiculoData.placa_cavalo"
							v-mask="['AAA-#X##']"
						/>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card v-if="!data_not_found">
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="updateRecord()">Salvar</vs-button>
						<vs-button type="border" color="danger" @click="voltar()">Cancelar</vs-button>
					</div>
				</div>
			</vx-card>
		</div>
	</div>
</template>

<script>
import vSelect from "vue-select";
import { VMoney } from "v-money";

export default {
	components: {
		"v-select": vSelect
	},
	directives: { money: VMoney },
	data() {
		return {
			money: {
				decimal: ",",
				thousands: ".",
				precision: 3
			},

			veiculoData: [],

			usarGpsOptions: [
				{ label: "Não utilizar", value: "N" },
				{ label: "Rastreador do veículo", value: "V" }
			],

			nivelConsOptions: [
				{ label: "Não definido", value: "0" },
				{ label: "Nível 1 (menor consumo)", value: "1" },
				{ label: "Nível 2", value: "2" },
				{ label: "Nível 3", value: "3" },
				{ label: "Nível 4", value: "4" },
				{ label: "Nível 5", value: "5" },
				{ label: "Nível 6 (maior consumo)", value: "6" }
			],

			data_not_found: false,

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch("veiculo/editVeiculo", this.$route.params.placa)
			.then(res => {
				if (res.data.veiculo.length > 0) {
					this.veiculoData = res.data.veiculo[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});

		this.getTipoVeiculo();
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.veiculoData.cod_tipo_veiculo != "" &&
				this.veiculoData.ativo != ""
			);
		},
		tipoVeiculo() {
			return this.$store.state.tipoVeiculo.tipoVeiculoData;
		},
		tipoVeiculoCombo: {
			get() {
				return {
					descricao: this.veiculoData.descricao_tipo
				};
			},
			set(obj) {
				this.veiculoData.cod_tipo_veiculo = obj.codigo;
				this.veiculoData.descricao_tipo = obj.descricao;
			}
		},
		//Não estamos utilizando este campo.
		// milkRunCheck: {
		// 	get() {
		// 		return this.retValorCheckBoxSimNao(this.veiculoData.milk_run);
		// 	},
		// 	set(obj) {
		// 		this.veiculoData.milk_run = obj;
		// 	}
		// },
		empilhaCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.veiculoData.sis_carga_empilha
				);
			},
			set(obj) {
				this.veiculoData.sis_carga_empilha = obj;
			}
		},
		ponteCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.veiculoData.sis_carga_ponte
				);
			},
			set(obj) {
				this.veiculoData.sis_carga_ponte = obj;
			}
		},
		manualCheck: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.veiculoData.sis_carga_manual
				);
			},
			set(obj) {
				this.veiculoData.sis_carga_manual = obj;
			}
		},
		usarGps_local: {
			get() {
				return {
					label: this.usarGpsLabel(this.veiculoData.usar_gps),
					value: this.veiculoData.usar_gps
				};
			},
			set(obj) {
				this.veiculoData.usar_gps = obj.value;
			}
		},
		nivelCons_local: {
			get() {
				return {
					label: this.nivelConsLabel(this.veiculoData.nivel_cons),
					value: this.veiculoData.nivel_cons
				};
			},
			set(obj) {
				if (obj != null) {
					this.veiculoData.nivel_cons = obj.value;
				} else {
					this.veiculoData.nivel_cons = "0";
				}
			}
		},
		ativoCheck: {
			get() {
				return this.retValorCheckBoxSimNao(this.veiculoData.ativo);
			},
			set(obj) {
				this.veiculoData.ativo = obj;
			}
		}
	},
	methods: {
		getTipoVeiculo() {
			this.$store.dispatch("tipoVeiculo/indexTipoVeiculo").catch(err => {
				console.error(err);
			});
		},
		updateRecord() {
			this.$http
				.put(
					`api/veiculo/${this.$route.params.placa}`,
					{
						placa: this.veiculoData.placa,

						cod_tipo_veiculo: this.veiculoData.cod_tipo_veiculo,
						//Não estamos utilizando este campo.
						// milk_run:
						// 	this.veiculoData.milk_run == null
						// 		? "N"
						// 		: this.veiculoData.milk_run,
						sis_carga_empilha:
							this.veiculoData.sis_carga_empilha == null
								? "N"
								: this.veiculoData.sis_carga_empilha,
						sis_carga_ponte:
							this.veiculoData.sis_carga_ponte == null
								? "N"
								: this.veiculoData.sis_carga_ponte,
						sis_carga_manual:
							this.veiculoData.sis_carga_manual == null
								? "N"
								: this.veiculoData.sis_carga_manual,
						largura: this.veiculoData.largura,
						comprimento: this.veiculoData.comprimento,
						altura: this.veiculoData.altura,
						cap_cub: this.veiculoData.cap_cub,
						cap_kg: this.veiculoData.cap_kg,
						nivel_cons: this.veiculoData.nivel_cons,
						usar_gps: this.veiculoData.usar_gps,
						placa_cavalo: this.veiculoData.placa_cavalo,
						ativo:
							this.veiculoData.ativo == null
								? "N"
								: this.veiculoData.ativo
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
						this.$router.push("/veiculo/").catch(() => {});
					}
					if (response.data.status === false) {
						this.erros_form = response.data.erros;
						this.tem_erros = true;
						console.log(response.data.erros);
					}
				})
				.catch(e => {
					this.erros_form = e.response.data.errors;
					this.tem_erros = true;
					console.log(e.response.data.errors);
				});
		},
		voltar() {
			this.$router.back();
		},
		retValorCheckBoxSimNao(value) {
			if (value === "S") {
				return "S";
			} else {
				return null;
			}
		},
		usarGpsLabel(str) {
			if (str === "N") return "Não utilizar";
			else if (str === "V") return "Rastreador do veículo";
			else return str;
		},
		nivelConsLabel(str) {
			if (str === "0") return "Não definido";
			else if (str === "1") return "Nível 1 (menor consumo)";
			else if (str === "2") return "Nível 2";
			else if (str === "3") return "Nível 3";
			else if (str === "4") return "Nível 4";
			else if (str === "5") return "Nível 5";
			else if (str === "6") return "Nível 6 (maior consumo)";
			else return str;
		}
	}
};
</script>

<style lang="scss">
</style>
