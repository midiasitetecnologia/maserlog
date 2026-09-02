<template>
	<div class="page-coleta-fixa-bloq-create">
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
			<vx-card title="Bloquear as solicitações de coleta no período abaixo:">				

				<div class="vx-row">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Data Inicial</label>
						<flat-pickr
							:config="configdateTimePickerDate"
							v-model="coletaFixaBloqData[0].dt_ini"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Data Final</label>
						<flat-pickr
							:config="configdateTimePickerDate"
							v-model="coletaFixaBloqData[0].dt_fim"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							class="w-full"
							data-vv-validate-on="blur"
							v-validate="'max:255'"
							data-vv-as="observação"
							maxlength="255"
							name="observ"							
							label="Observação"							
							placeholder="Informe o motivo pelo qual as solicitações foram bloqueadas durante este período."
							v-model="coletaFixaBloqData[0].observ"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('observ')"
						>{{ errors.first('observ') }}</span>
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
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";

export default {
	components: {
		flatPickr
	},
	data() {
		return {
			configdateTimePickerDate: {
				altInput: true,
				altFormat: "d/m/Y",
				dateFormat: "Y-m-d",
				locale: Portuguese
			},

			coletaFixaBloqData: [
				{					
					dt_ini: null,
					dt_fim: null,
					observ: ""
				}
			],

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
	},
	computed: {
		validateForm() {
			return !this.errors.any() &&
			this.coletaFixaBloqData[0].dt_ini != null &&
			this.coletaFixaBloqData[0].dt_fim != null				
		},
	},
	methods: {
		addNewRecord() {

			this.$http
				.post(
					`api/coleta-fixa-bloq`,
					{	
						coleta_fixa_id: this.$route.params.coleta_fixa_id,
						dt_ini: this.coletaFixaBloqData[0].dt_ini,
						dt_fim: this.coletaFixaBloqData[0].dt_fim,
						observ: this.coletaFixaBloqData[0].observ
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
						this.voltar()
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
		},
	}
};
</script>

<style lang="scss">
</style>