<template>
	<div class="page-sys-cfg-create-edit">
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
				<vs-tabs>
					<vs-tab label="Rastreamento">
						<div class="vx-row mt-6 mb-6">
							<div class="vx-col w-full">
								<vs-input
									data-vv-validate-on="blur"
									v-validate="'max:255'"
									data-vv-as="url do servidor (Plataforma de Rastreamento)"
									name="url_sis_track"
									class="w-full"
									label="URL do servidor (Plataforma de Rastreamento)"
									v-model="sysCfgData.url_sis_track"
								/>
								<span
									class="text-danger text-sm"
									v-show="errors.has('url_sis_track')"
								>{{ errors.first('url_sis_track') }}</span>
							</div>
						</div>

						<div class="vx-row mb-6">
							<div class="vx-col w-full">
								<vs-input
									data-vv-validate-on="blur"
									v-validate="'max:50'"
									data-vv-as="usuário"
									name="user_sis_track"
									class="w-full"
									label="Usuário"
									v-model="sysCfgData.user_sis_track"
								/>
								<span
									class="text-danger text-sm"
									v-show="errors.has('user_sis_track')"
								>{{ errors.first('user_sis_track') }}</span>
							</div>
						</div>

						<div class="vx-row mb-6">
							<div class="vx-col w-full">
								<label class="vs-input--label">Senha</label>
								<vx-input-group>
									<vs-input
										autocomplete="off"
										data-vv-validate-on="blur"
										v-validate="'max:50'"
										data-vv-as="senha"
										name="pwd_sis_track"
										:type="showPassword == false ? 'password' : 'text'"
										class="w-full"
										v-model="sysCfgData.pwd_sis_track"
									/>
									<template slot="prepend">
										<div class="prepend-text btn-addon">
											<vs-button
												@click="setaShowPwd()"
												color="primary"
												type="border"
												icon-pack="feather"
												:icon="showPassword == false ? 'icon-eye-off' : 'icon-eye'"
											></vs-button>
										</div>
									</template>
								</vx-input-group>
								<span
									class="text-danger text-sm"
									v-show="errors.has('pwd_sis_track')"
								>{{ errors.first('pwd_sis_track') }}</span>
							</div>
						</div>
					</vs-tab>
					<vs-tab label="Áreas">
						<div class="vx-row mt-6">
							<div class="vx-col w-full">
								<label class="vs-input--label">Escritório</label>
								<vs-textarea class="w-full" rows="6" v-model="sysCfgData.office_area" />
							</div>
						</div>

						<div class="vx-row">
							<div class="vx-col w-full">
								<label class="vs-input--label">Garagem</label>
								<vs-textarea class="w-full" rows="6" v-model="sysCfgData.garage_area" />
							</div>
						</div>

						<div class="vx-row">
							<div class="vx-col w-full">
								<label class="vs-input--label">Pavilhão</label>
								<vs-textarea class="w-full" rows="6" v-model="sysCfgData.pavilion_area" />
							</div>
						</div>

						<div class="vx-row mb-6">
							<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
								<vs-input
									class="w-full"
									label="Latitude Pavilhão"
									v-model="sysCfgData.geo_lat_pavilion"
									v-mask="['-##.########']"
								/>
							</div>

							<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
								<vs-input
									class="w-full"
									label="Longitude Pavilhão"
									v-model="sysCfgData.geo_lng_pavilion"
									v-mask="['-##.########']"
								/>
							</div>
						</div>
					</vs-tab>

					<vs-tab label="Manutenção">
						<div class="vx-row mt-6 mb-6">
							<div class="vx-col w-full">
								<label class="vs-input--label">Status</label>
								<v-select
									class="w-full"
									label="label"
									:options="statusOptions"
									:clearable="false"
									v-model="statusCombo"
								>
									<div slot="no-options">Opção não disponível</div>
								</v-select>
							</div>
						</div>

						<div class="vx-row mb-6">
							<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
								<label class="vs-input--label">Data Inicial</label>
								<flat-pickr
									:config="configdateTimePickerDate"
									v-model="sysCfgData.dt_ini_status"
									class="w-full vs-inputx vs-input--input normal hasValue"
									style="border: 1px solid rgba(0, 0, 0, 0.2);"
								/>
							</div>

							<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
								<label class="vs-input--label">Data Final</label>
								<flat-pickr
									:config="configdateTimePickerDate"
									v-model="sysCfgData.dt_fim_status"
									class="w-full vs-inputx vs-input--input normal hasValue"
									style="border: 1px solid rgba(0, 0, 0, 0.2);"
								/>
							</div>
						</div>

						<div class="vx-row mb-2">
							<div class="vx-col w-full">
								<label class="vs-input--label">Mensagem</label>
								<vs-textarea class="w-full" v-model="sysCfgData.msg_status" />
							</div>
						</div>

						<div class="vx-row mb-6">
							<div class="vx-col w-full">
								<vs-input
									data-vv-validate-on="blur"
									v-validate="'max:255'"
									data-vv-as="url redirecionamento"
									name="url_redirect"
									class="w-full"
									label="URL redirecionamento"
									v-model="sysCfgData.url_redirect"
								/>
								<span
									class="text-danger text-sm"
									v-show="errors.has('url_redirect')"
								>{{ errors.first('url_redirect') }}</span>
							</div>
						</div>

						<div class="mt-20">
							<span class="font-semibold">Coletas Fixas</span>
						</div>

						<div class="vx-row mt-6 mb-6">
							<div class="vx-col w-full">
								<div class="flex flex-items-center text-center">
									<vs-switch class="mr-2" color="primary" v-model="sysCfgData.gerar_coletas_fixas" />
									<label>Gerar automaticamente Solicitações de Coleta a partir das Coletas Fixas</label>
								</div>
							</div>
						</div>

						<div class="vx-row mb-3">
							<div class="vx-col w-full">
								<label class="vs-input--label">Defina qual dia deve ser gravado na Data Prevista para Coleta das solicitações geradas</label>
								<v-select
									class="w-full"
									label="label"
									:options="diaColetasFixasOptions"
									:clearable="false"
									v-model="diaColetasFixasCombo"
								>
									<div slot="no-options">Opção não disponível</div>
								</v-select>
							</div>
						</div>

						<div class="vx-row mb-6">
							<div class="vx-col w-full">																
								<span>A rotina de geração de solicitações é executada somente uma vez por dia.</span>
							</div>							
						</div>			

						<div class="vx-row mt-20 mb-3">
							<div class="vx-col w-full">								
								<vs-button :disabled="buscandoCoords" @click="autorizar()">Buscar Geocoordenadas Clientes</vs-button>																
							</div>							
						</div>						

						<div class="vx-row mb-6">
							<div class="vx-col w-full">																
								<span>As coordenadas de latitude e longitude serão atualizadas no perfil do cliente, refletindo sua localização geográfica com base no endereço cadastrado.</span>
							</div>							
						</div>

						<vs-prompt
							class="max550"
							@accept="autorizado"
							title="Buscar Geocoordenadas"
							accept-text="Autorizar"
							cancel-text="Cancelar"
							:active.sync="activePrompt"
						>
							<div class="con-exemple-prompt">
								<p class="mb-4">{{promptText}}</p>
							</div>
						</vs-prompt>
					</vs-tab>
				</vs-tabs>
			</vx-card>
		</div>

		<div class="vx-col w-full mb-base">
			<vx-card>
				<div class="vx-row">
					<div class="vx-col w-full">
						<vs-button class="mr-3 mb-2" :disabled="!validateForm" @click="insertOrUpdateRecord()">Salvar</vs-button>
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
			showPassword: false,
			funcao: null,

			activePrompt: false,
			promptText: null,
			buscandoCoords: false,

			configdateTimePickerDate: {
				altInput: true,
				enableTime: true,
				altFormat: "d/m/Y H:i",
				dateFormat: "Y-m-d H:i:s",
				locale: Portuguese
			},

			sysCfgData: [],

			statusOptions: [
				{ label: "Modo de Produção", value: "MP" },
				{ label: "Modo de Segurança", value: "MS" },
				{ label: "Modo Administração", value: "MA" },
				{ label: "Atualização Programada", value: "AP" },
				{ label: "Desativação Definitiva", value: "DD" }
			],

			diaColetasFixasOptions: [
				{ label: "Dia atual: a data prevista para coleta será igual a data de geração das solicitações.", value: "A" },
				{ label: "Dia seguinte: a data prevista para coleta será a data de geração das solicitações +1 dia.", value: "S" }
			],

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch("sysCfg/editSysCfg")
			.then(res => {
				if (res.data.sysCfg.length > 0) {
					this.funcao = "update";
					this.sysCfgData = res.data.sysCfg[0];
					this.sysCfgData.gerar_coletas_fixas =
						this.sysCfgData.gerar_coletas_fixas == "N"
							? false
							: true;
				} else {
					this.funcao = "insert";
					this.sysCfgData = [
						{
							url_sis_track: null,
							user_sis_track: null,
							pwd_sis_track: null,
							status: null,
							dt_ini_status: null,
							dt_fim_status: null,
							msg_satus: null,
							url_redirect: null,
							office_area: null,
							garage_area: null,
							pavilion_area: null,
							geo_lat_pavilion: null,
							geo_lng_pavilion: null,
							gerar_coletas_fixas: false,
							dia_coletas_fixas: "S"
						}
					];
				}
			})
			.catch(err => {
				console.error(err);
			});
	},
	computed: {
		validateForm() {
			return !this.errors.any();
		},
		statusCombo: {
			get() {
				if (this.sysCfgData.status == null) {
					return "Selecione o status";
				} else {
					return {
						label: this.statusLabel(this.sysCfgData.status),
						value: this.sysCfgData.status
					};
				}
			},
			set(obj) {
				this.sysCfgData.status = obj.value;
			}
		},
		diaColetasFixasCombo: {
			get() {
				if (this.sysCfgData.dia_coletas_fixas == null) {
					return "Selecione...";
				} else {
					return {
						label: this.diaColetasFixasLabel(this.sysCfgData.dia_coletas_fixas),
						value: this.sysCfgData.dia_coletas_fixas
					};
				}
			},
			set(obj) {
				this.sysCfgData.dia_coletas_fixas = obj.value;
			}
		}
	},
	methods: {
		setaShowPwd() {
			if (this.showPassword == false) {
				this.showPassword = true;
			} else {
				this.showPassword = false;
			}
		},
		statusLabel(str) {
			if (str === "MP") return "Modo de Produção";
			else if (str === "MS") return "Modo de Segurança";
			else if (str === "MA") return "Modo Administração";
			else if (str === "AP") return "Atualização Programada";
			else if (str === "DD") return "Desativação Definitiva";
			else return str;
		},
		diaColetasFixasLabel(str) {
			if (str === "A") return "Dia atual: a data prevista para coleta será igual a data de geração das solicitações.";
			else if (str === "S") return "Dia seguinte: a data prevista para coleta será a data de geração das solicitações +1 dia.";
			else return str;
		},
		insertOrUpdateRecord() {
			if (this.funcao == "insert") {
				this.addNewRecord();
			} else {
				this.updateRecord();
			}
		},
		addNewRecord() {
			this.$http
				.post(
					`api/sys-cfg`,
					{
						url_sis_track: this.sysCfgData.url_sis_track,
						user_sis_track: this.sysCfgData.user_sis_track,
						pwd_sis_track: this.sysCfgData.pwd_sis_track,
						status: this.sysCfgData.status,
						dt_ini_status: this.sysCfgData.dt_ini_status,
						dt_fim_status: this.sysCfgData.dt_fim_status,
						msg_status: this.sysCfgData.msg_status,
						url_redirect: this.sysCfgData.url_redirect,
						office_area: this.sysCfgData.office_area,
						garage_area: this.sysCfgData.garage_area,
						pavilion_area: this.sysCfgData.pavilion_area,
						geo_lat_pavilion: this.sysCfgData.geo_lat_pavilion,
						geo_lng_pavilion: this.sysCfgData.geo_lng_pavilion,
						gerar_coletas_fixas:
							this.sysCfgData.gerar_coletas_fixas == false
								? "N"
								: "S",
						dia_coletas_fixas: this.sysCfgData.dia_coletas_fixas
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
						this.$router.back();
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
		updateRecord() {
			this.$http
				.put(
					`api/sys-cfg/${this.sysCfgData.id}`,
					{
						url_sis_track: this.sysCfgData.url_sis_track,
						user_sis_track: this.sysCfgData.user_sis_track,
						pwd_sis_track: this.sysCfgData.pwd_sis_track,
						status: this.sysCfgData.status,
						dt_ini_status: this.sysCfgData.dt_ini_status,
						dt_fim_status: this.sysCfgData.dt_fim_status,
						msg_status: this.sysCfgData.msg_status,
						url_redirect: this.sysCfgData.url_redirect,
						office_area: this.sysCfgData.office_area,
						garage_area: this.sysCfgData.garage_area,
						pavilion_area: this.sysCfgData.pavilion_area,
						geo_lat_pavilion: this.sysCfgData.geo_lat_pavilion,
						geo_lng_pavilion: this.sysCfgData.geo_lng_pavilion,
						gerar_coletas_fixas:
							this.sysCfgData.gerar_coletas_fixas == false
								? "N"
								: "S",
						dia_coletas_fixas: this.sysCfgData.dia_coletas_fixas
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
						this.$router.back();
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

		autorizar() {
			this.promptText = `Você confirma a carga das geocoordenadas para os clientes?`;
			this.activePrompt = true;
		},

		autorizado() {
			this.buscandoCoords = true;

			return new Promise((resolve, reject) => {
				this.$http
					.post(
						`api/CarregarGeoCoordenadasCliente`,
						{},
						{
							headers: {
								Authorization:
									"Bearer " + localStorage.getItem("apiToken")
							}
						}
					)
					.then(response => {
						if (response.data.status === true) {
							this.showImportGeoCoordsSuccess();
						} else {
							this.showImportGeoCoordsErrors();
						}

						this.buscandoCoords = false;
					})
					.catch(error => {
						reject(error);
						this.buscandoCoords = false;
					});
			});
		},

		showImportGeoCoordsSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Coordenadas Importadas",
				text:
					"A importação das geocoordenadas foi concluída com sucesso."
			});
		},

		showImportGeoCoordsErrors() {
			this.$vs.notify({
				color: "danger",
				title: "Erros na Importação",
				text:
					"Ocorreram erros na importação das geocoordenadas. Verifique."
			});
		}
	}
};
</script>

<style lang="scss">
</style>
