<template>
	<div class="page-tipo-veiculo-create">
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
			<vx-card>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							class="w-full"
							type="number"
							min="1"												
							autocomplete="off"
							data-vv-validate-on="blur"							
							v-validate="'required|numeric|max:10|max_value:2147483647'"
							data-vv-as="código"
							name="codigo"							
							label="Código"							
							v-model.number="tipoVeiculoData[0].codigo"
						/>
						<span class="text-danger text-sm" v-show="errors.has('codigo')">{{ errors.first('codigo') }}</span>
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
							v-model="tipoVeiculoData[0].descricao"
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
						<span
							class="text-danger text-sm"
							v-show="errors.has('classe')"
						>{{ errors.first('classe') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="tipoVeiculoData[0].classe != 'C'">
					<div class="vx-col w-full">
						<label class="vs-input--label">Duração do atendimento</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="tipoVeiculoData[0].dur_prev_atend"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>

				<div class="vx-row mb-6" v-if="tipoVeiculoData[0].classe != 'C'">
					<div class="vx-col w-full">
						<label class="vs-input--label">Tempo de deslocamento até o pavilhão</label>
						<flat-pickr
							:config="configdateTimePickerTime"
							v-model="tipoVeiculoData[0].tempo_desloc_pavilhao"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card>
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="addNewRecord()">Salvar</vs-button>
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

			tipoVeiculoData: [
				{
					codigo: "",
					descricao: "",
					classe: "M",
					dur_prev_atend: "00:00",
					tempo_desloc_pavilhao: "00:00"
				}
			],

			classeOptions: [
				{ label: "Carreta", value: "R" },
				{ label: "Cavalo", value: "C" },
				{ label: "Monobloco", value: "M" }
			],

			tem_erros: false,
			erros_form: []
		};
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.tipoVeiculoData[0].codigo != "" &&
				this.tipoVeiculoData[0].descricao != "" &&
				this.tipoVeiculoData[0].classe != ""
			);
		},
		classe_local: {
			get() {
				return {
					label: this.classeLabel(this.tipoVeiculoData[0].classe),
					value: this.tipoVeiculoData[0].classe
				};
			},
			set(obj) {
				this.tipoVeiculoData[0].classe = obj.value;
			}
		},
	},
	methods: {
		classeLabel(str) {
			if (str === "R") return "Carreta";
			else if (str === "C") return "Cavalo";
			else if (str === "M") return "Monobloco";
			else return str;
		},
		addNewRecord() {
			this.$http
				.post(
					`api/tipo-veiculo`,
					{
						codigo: this.tipoVeiculoData[0].codigo,
						descricao: this.tipoVeiculoData[0].descricao,
						classe: this.tipoVeiculoData[0].classe,
						dur_prev_atend: this.tipoVeiculoData[0].dur_prev_atend,
						tempo_desloc_pavilhao: this.tipoVeiculoData[0].tempo_desloc_pavilhao
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
					console.log(e);
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
