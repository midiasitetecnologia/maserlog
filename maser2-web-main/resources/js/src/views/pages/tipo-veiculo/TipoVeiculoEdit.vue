<template>
	<div class="page-tipo-veiculo-edit">
		<vs-alert
			color="danger"
			title="Tipo de veículo não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de tipo de veículo com o código: {{$route.params.codigo}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'tipo-veiculo'}" class="text-inherit underline">Tipos de Veículo</router-link>
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

		<!-- VERTICAL LAYOUT -->
		<div class="vx-col w-full mb-base">
			<vx-card v-if="!data_not_found">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							disabled="true"
							name="codigo"
							class="w-full"
							label="Código"
							v-model="tipoVeiculoData.codigo"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:60'"
							data-vv-as="descrição"
							name="descricao"
							class="w-full"
							label="Descrição"
							v-model="tipoVeiculoData.descricao"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('descricao')"
						>{{ errors.first('descricao') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">Classe</label>
						<v-select
							v-model="classe_local"
							:options="classeOptions"
							:clearable="false"
							v-validate="'required'"
							name="classe"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
						<span class="text-danger text-sm" v-show="errors.has('classe')">{{ errors.first('classe') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="tipoVeiculoData.classe != 'C'">
					<div class="vx-col w-full">
						<label class="vs-input--label">Duração do atendimento</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="tipoVeiculoData.dur_prev_atend"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="tipoVeiculoData.classe != 'C'">
					<div class="vx-col w-full">
						<label class="vs-input--label">Tempo de deslocamento até o pavilhão</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="tipoVeiculoData.tempo_desloc_pavilhao"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
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
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
	components: {
		vSelect,
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

			tipoVeiculoData: [],
			data_not_found: false,

			classeOptions: [
				{ label: "Carreta", value: "R" },
				{ label: "Cavalo", value: "C" },
				{ label: "Monobloco", value: "M" }
			],

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch("tipoVeiculo/editTipoVeiculo", this.$route.params.codigo)
			.then(res => {
				if (res.data.tipoVeiculo.length > 0) {
					this.tipoVeiculoData = res.data.tipoVeiculo[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.tipoVeiculoData.descricao != "" &&
				this.tipoVeiculoData.classe != ""
			);
		},
		classe_local: {
			get() {
				return {
					label: this.classeLabel(this.tipoVeiculoData.classe),
					value: this.tipoVeiculoData.classe
				};
			},
			set(obj) {
				this.tipoVeiculoData.classe = obj.value;
			}
		}
	},
	methods: {
		classeLabel(str) {
			if (str === "R") return "Carreta";
			else if (str === "C") return "Cavalo";
			else if (str === "M") return "Monobloco";
			else return str;
		},
		updateRecord() {
			this.$http
				.put(
					`api/tipo-veiculo/${this.$route.params.codigo}`,
					{
						codigo: this.tipoVeiculoData.codigo,
						descricao: this.tipoVeiculoData.descricao,
						classe: this.tipoVeiculoData.classe,
						dur_prev_atend: this.tipoVeiculoData.dur_prev_atend,
						tempo_desloc_pavilhao: this.tipoVeiculoData.tempo_desloc_pavilhao
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
						this.$router.push("/tipo-veiculo/").catch(() => {});
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
		}
	}
};
</script>

<style lang="scss">
</style>
