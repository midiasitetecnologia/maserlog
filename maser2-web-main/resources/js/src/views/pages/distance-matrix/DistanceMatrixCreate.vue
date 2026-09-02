<template>
	<div class="page-distance-matrix-create">
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
						<label class="vs-input--label">Serviço de Distância e Rota</label>
						<v-select
							v-model="service_local"
							:options="serviceOptions"
							:clearable="false"
							v-validate="'required'"
							name="api_service"
						>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
						<span
							class="text-danger text-sm"
							v-show="errors.has('api_service')"
						>{{ errors.first('api_service') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:100'"
							data-vv-as="conta de e-mail"
							name="api_account"
							class="w-full"
							label="Conta de e-mail cadastrada no serviço"
							v-model="distanceMatrixData[0].api_account"
						/>
						<span
							class="text-danger text-sm"
							v-show="errors.has('api_account')"
						>{{ errors.first('api_account') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							name="api_key"	
							class="w-full"
							label="Chave de API / Access Token"							
							data-vv-validate-on="blur"
							v-validate="'required|max:255'"
							data-vv-as="Chave de API / Access Token"
							maxlength="255"							
							v-model="distanceMatrixData[0].api_key"
						/>
						<span class="text-danger text-sm" v-show="errors.has('api_key')">{{ errors.first('api_key') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							type="number"
							min="1"
							max="2147483647"
							name="api_usage"	
							class="w-full"
							data-vv-validate-on="blur"
							v-validate="'numeric|max:10|max_value:2147483647'"
							data-vv-as="solicitações"
							label="Solicitações"							
							v-model.number="distanceMatrixData[0].api_usage"
						/>
						<span class="text-danger text-sm" v-show="errors.has('api_usage')">{{ errors.first('api_usage') }}</span>						
					</div>					
				</div>			

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							type="number"
							min="1"
							max="2147483647"
							name="api_limit"	
							class="w-full"
							data-vv-validate-on="blur"
							v-validate="'numeric|max:10|max_value:2147483647'"
							data-vv-as="limite de requisições"
							label="Limite de Requisições"							
							v-model.number="distanceMatrixData[0].api_limit"
						/>
						<span class="text-danger text-sm" v-show="errors.has('api_limit')">{{ errors.first('api_limit') }}</span>
						<span class="text-primary text-sm" v-show="distanceMatrixData[0].api_service === 'google_cloud'">Limite mensal gratuito de 10.000 requisições.</span>
						<span class="text-primary text-sm" v-show="distanceMatrixData[0].api_service === 'mapbox'">Limite mensal gratuito de 100.000 requisições.</span>
					</div>					
				</div>

				<div class="vx-row mb-10">
					<div class="vx-col w-full">
						<label class="vs-input--label">Prioridade de uso</label>
						<v-select v-model="priority_local" :options="priorityOptions" :clearable="false">
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row mb-10">
					<div class="vx-col">
						<vs-checkbox v-model="activeCheck" vs-value="S">Ativo</vs-checkbox>
					</div>
				</div>

				<vs-button :disabled="distanceMatrixData[0].api_key == ''" type="border" @click="testApiKey">Testar Chave</vs-button>
				
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
import labelsMixins from "@/mixins/labelsMixins";
import vSelect from "vue-select";

export default {
	mixins: [labelsMixins],
	components: {
		vSelect
	},
	data() {
		return {
			distanceMatrixData: [
				{
					api_service: "google_cloud",
					api_account: "",
					api_key: "",
					api_usage: 0,
					api_limit: 10000,
					api_priority: "1",
					active: "S"
				}
			],

			serviceOptions: [
				{ label: "Google Cloud", value: "google_cloud" },
				{ label: "Mapbox", value: "mapbox" }
			],

			priorityOptions: [				
				{ label: "Nível 1 (Alta prioridade)", value: "1" },
				{ label: "Nível 2", value: "2" },
				{ label: "Nível 3", value: "3" },
				{ label: "Nível 4", value: "4" },
				{ label: "Nível 5", value: "5" },
				{ label: "Nível 6 (Baixa prioridade)", value: "6" }
			],

			tem_erros: false,
			erros_form: []
		};
	},
	computed: {
		validateForm() {
			return (
				!this.errors.any() &&
				this.distanceMatrixData[0].api_service != "" &&
				this.distanceMatrixData[0].api_account != "" &&
				this.distanceMatrixData[0].api_key != "" &&				
				this.distanceMatrixData[0].api_limit != "" &&
				this.distanceMatrixData[0].api_priority != ""
			);
		},		
		service_local: {
			get() {
				return {
					label: this.serviceLabel(this.distanceMatrixData[0].api_service),
					value: this.distanceMatrixData[0].api_service
				};
			},
			set(obj) {
				if(obj != null) {
					this.distanceMatrixData[0].api_service = obj.value;
                } else {
					this.distanceMatrixData[0].api_service = "google_cloud";
                }				
			}			
		},
		priority_local: {
			get() {
				return {
					label: this.priorityLabel(this.distanceMatrixData[0].api_priority),
					value: this.distanceMatrixData[0].api_priority
				};
			},
			set(obj) {
				if(obj != null) {
					this.distanceMatrixData[0].api_priority = obj.value;
                } else {
					this.distanceMatrixData[0].api_priority = "1";
                }				
			}
		},
		activeCheck: {
			get() {
				return this.retValorCheckBoxSimNao(this.distanceMatrixData[0].active);
			},
			set(obj) {
				this.distanceMatrixData[0].active = obj;
			}
		}
	},
	methods: {		
		addNewRecord() {
			this.$http
				.post(
					`api/distance-matrix`,
					{
						api_service: this.distanceMatrixData[0].api_service,
						api_account: this.distanceMatrixData[0].api_account,
						api_key: this.distanceMatrixData[0].api_key,
						api_usage: this.distanceMatrixData[0].api_usage,
						api_limit: this.distanceMatrixData[0].api_limit,
						api_priority: this.distanceMatrixData[0].api_priority,
						active: this.distanceMatrixData[0].active == null ? "N" : this.distanceMatrixData[0].active
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
						this.$router.push("/distance-matrix/").catch(() => {});
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
		retValorCheckBoxSimNao(value) {
			if (value === "S") {
				return "S";
			} else {
				return null;
			}
		},
		voltar() {
			this.$router.back();
		},
		testApiKey() {
			const latOrig = '-29.194914';
			const lngOrig = '-51.189505';
			const latDest = '-29.167409';
			const lngDest = '-51.160728';

			const apiService = this.distanceMatrixData[0].api_service;
			const apiKey = this.distanceMatrixData[0].api_key;

			this.$http.get(`/api/test-driving-distance`, {
				params: {
					latOrig, lngOrig, latDest, lngDest, apiService, apiKey
				},
				headers: {
					Authorization: "Bearer " + localStorage.getItem("apiToken")
				}
			}).
			then(response => {
				const { distance, duration } = response.data;
				if ((distance > 0) && (duration > 0)) {
					this.$vs.notify({
						title: 'Chave Válida',
						text: `Distância: ${distance} km, Tempo: ${duration} segundos`,
						color: 'success'
					});
				} else {
					this.$vs.notify({
						title: 'Erro',
						text: 'Chave inválida ou erro na requisição',
						color: 'danger'
					});
				}
			}).catch(() => {
				this.$vs.notify({
				title: 'Erro',
				text: 'Erro ao testar a chave',
				color: 'danger'
				});
			});
		}
	}
};
</script>

<style lang="scss">
</style>
