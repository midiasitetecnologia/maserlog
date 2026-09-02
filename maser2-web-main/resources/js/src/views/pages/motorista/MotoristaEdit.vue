<template>
	<div class="page-motorista-edit">
		<vs-alert
			color="danger"
			title="Motorista não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de motorista com o ID: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'motorista'}" class="text-inherit underline">Motoristas</router-link>
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
			<vx-card title="Motorista" v-if="!data_not_found">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label for class="vs-input--label">Nome</label>
						<input
							disabled="true"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="nome"
							v-model="motoristaData.nome"
						/>
					</div>
				</div>

				<div class="vx-row">
					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label for class="vs-input--label">CPF</label>
						<input
							disabled="true"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="cpf"
							v-model="motoristaData.cpf"
						/>
					</div>

					<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
						<label for class="vs-input--label">Celular</label>
						<input
							disabled="true"
							class="w-full vs-inputx vs-input--input normal hasValue"
							style="border: 1px solid rgba(0, 0, 0, 0.2);"
							name="celular"
							v-model="motoristaData.celular"
						/>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full mb-6">
						<label class="vs-input--label">Horário de início e fim de expediente</label>
						<div class="vx-row">
							<div class="flex flex-items-center vx-col">
								<flat-pickr									
									:config="configdateTimePickerTime"
									v-model="motoristaData.hr_ini_exped"
									class="mr-2 vs-inputx vs-input--input normal hasValue"
									style="border: 1px solid rgba(0, 0, 0, 0.2);"
								/>
								<flat-pickr									
									:config="configdateTimePickerTime"
									v-model="motoristaData.hr_fim_exped"
									class="vs-inputx vs-input--input normal hasValue"
									style="border: 1px solid rgba(0, 0, 0, 0.2);"
								/>
							</div>
						</div>
					</div>
				</div>				

				<div class="vx-row mb-6">
					<div class="vx-col">
						<vs-checkbox v-model="ativoCheck" vs-value="S">Ativo</vs-checkbox>
					</div>
				</div>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base" v-if="!data_not_found">
			<vx-card title="Aplicativo">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<label class="vs-input--label">ID Login</label>
						<v-select class="w-full" label="name" :options="usersData" clearable v-model="usersCombo">
							<template v-slot:option="option">
								<span :class="option.icon"></span>
								{{ option.name }} ({{ option.email }})
							</template>
							<div slot="no-options">Opção não disponível</div>
						</v-select>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-checkbox v-model="autoLogoff" vs-value="S">Desconectar usuário fora do horário definido</vs-checkbox>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full mb-6">
						<label class="vs-input--label">Manter conectado neste horário</label>
						<div class="vx-row">
							<div class="flex flex-items-center vx-col">
								<flat-pickr
									:disabled="motoristaData.auto_logoff != 'S'"
									:config="configdateTimePickerTime"
									v-model="motoristaData.hr_ini_login"
									class="mr-2 vs-inputx vs-input--input normal hasValue"
									style="border: 1px solid rgba(0, 0, 0, 0.2);"
								/>
								<flat-pickr
									:disabled="motoristaData.auto_logoff != 'S'"
									:config="configdateTimePickerTime"
									v-model="motoristaData.hr_fim_login"
									class="vs-inputx vs-input--input normal hasValue"
									style="border: 1px solid rgba(0, 0, 0, 0.2);"
								/>
							</div>
						</div>
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

			motoristaData: [],
			usersData: [],

			data_not_found: false,

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch("motorista/editMotorista", this.$route.params.id)
			.then(res => {
				if (res.data.motorista.length > 0) {
					this.motoristaData = res.data.motorista[0];
					this.getUsersMotorista();
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
			return true;
		},
		ativoCheck: {
			get() {
				return this.retValorCheckBoxSimNao(this.motoristaData.ativo);
			},
			set(obj) {
				this.motoristaData.ativo = obj;
			}
		},
		usersCombo: {
			get() {
				if (this.motoristaData.name == null) {
					return "Sem acesso";
				} else {
					return {
						name:
							this.motoristaData.name +
							" (" +
							this.motoristaData.email +
							")"
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.motoristaData.name = obj.name;
					this.motoristaData.email = obj.email;
					this.motoristaData.user_id = obj.id;
				} else {
					this.motoristaData.name = null;
					this.motoristaData.email = null;
					this.motoristaData.user_id = null;
				}
			}
		},
		autoLogoff: {
			get() {
				return this.retValorCheckBoxSimNao(
					this.motoristaData.auto_logoff
				);
			},
			set(obj) {
				this.motoristaData.auto_logoff = obj;
			}
		}
	},
	methods: {
		updateRecord() {
			this.$http
				.put(
					`api/motorista/${this.$route.params.id}`,
					{
						id: this.motoristaData.id,
						user_id: this.motoristaData.user_id,
						hr_ini_exped: this.motoristaData.hr_ini_exped,
						hr_fim_exped: this.motoristaData.hr_fim_exped,
						ativo:
							this.motoristaData.ativo == null
								? "N"
								: this.motoristaData.ativo,
						auto_logoff:
							this.motoristaData.auto_logoff == null
								? "N"
								: this.motoristaData.auto_logoff,
						hr_ini_login: this.motoristaData.hr_ini_login,
						hr_fim_login: this.motoristaData.hr_fim_login
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
						this.$router.push("/motorista/").catch(() => {});
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
		getUsersMotorista() {
			this.$http
				.post(
					`api/getUsersMotorista`,
					{
						user_id: this.motoristaData.user_id
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
						this.usersData = response.data.users;
					}
				})
				.catch();
		}
	}
};
</script>

<style lang="scss">
</style>
