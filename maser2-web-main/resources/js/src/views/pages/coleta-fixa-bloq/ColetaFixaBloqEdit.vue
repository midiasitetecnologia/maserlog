<template>
	<div class="page-coleta-fixa-bloq-edit">
		<vs-alert
			color="danger"
			title="Bloqueio de Coleta Fixa não encontrado"
			:active.sync="data_not_found"
		>
			<span>Registro de bloqueio de coleta fixa com id: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todas as</span>
				<router-link :to="{name:'coleta-fixa'}" class="text-inherit underline">Coletas Fixas</router-link>
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
			<vx-card title="Bloquear as solicitações de coleta no período abaixo:" v-if="!data_not_found">
				<div class="vx-row">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Data Inicial</label>
						<flat-pickr
							:disabled="coletaFixaBloqData.dt_ini < dataAtual"
							:config="configdateTimePickerDate"
							v-model="coletaFixaBloqData.dt_ini"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
						/>
					</div>

					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label class="vs-input--label">Data Final</label>
						<flat-pickr
							:disabled="coletaFixaBloqData.dt_fim < dataAtual"
							:config="configdateTimePickerDate"
							v-model="coletaFixaBloqData.dt_fim"
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
							v-model="coletaFixaBloqData.observ"
						/>
						<span class="text-danger text-sm" v-show="errors.has('observ')">{{ errors.first('observ') }}</span>
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

			coletaFixaBloqData: [],

			data_not_found: false,

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch(
				"coletaFixaBloq/editColetaFixaBloq",
				this.$route.params.id
			)
			.then(res => {
				if (res.data.coletaFixaBloq.length > 0) {
					this.coletaFixaBloqData = res.data.coletaFixaBloq[0];
					this.getDataAtual();
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
				this.coletaFixaBloqData.dt_ini != null &&
				this.coletaFixaBloqData.dt_fim != null
			);
		},
		dataAtual() {
			return this.$store.state.dataAtual.dataAtual;
		}
	},
	methods: {
		updateRecord() {
			this.$http
				.put(
					`api/coleta-fixa-bloq/${this.$route.params.id}`,
					{
						coleta_fixa_id: this.coletaFixaBloqData.coleta_fixa_id,
						dt_ini: this.coletaFixaBloqData.dt_ini,
						dt_fim: this.coletaFixaBloqData.dt_fim,
						observ: this.coletaFixaBloqData.observ
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
						this.voltar();
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
		getDataAtual() {
			this.$store.dispatch("dataAtual/getDataAtual").catch(err => {
				console.error(err);
			});
		}
	}
};
</script>

<style lang="scss">
</style>
