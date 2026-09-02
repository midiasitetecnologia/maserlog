<template>
	<div class="page-empresa-edit">
		<vs-alert
			color="danger"
			title="Empresa não encontrada"
			:active.sync="empresa_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de empresa com o código: {{$route.params.codigo}} não encontrado.</span>
			<span>
				<span>Verifique todas as</span>
				<router-link :to="{name:'empresa'}" class="text-inherit underline">Empresas</router-link>
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
			<vx-card v-if="!empresa_not_found">
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required'"
							data-vv-as="código"
							name="codigo"
							class="w-full"
							label="Código"
							disabled="true"
							v-model="empresaData.codigo"
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
							data-vv-as="nome"
							name="nome"
							class="w-full"
							label="Nome"
							maxlength="60"
							v-model="empresaData.nome"
						/>
						<span class="text-danger text-sm" v-show="errors.has('nome')">{{ errors.first('nome') }}</span>
					</div>
				</div>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:3'"
							data-vv-as="sigla"
							name="sigla"
							class="w-full"
							label="Sigla"
							maxlength="3"
							v-model="empresaData.sigla"
						/>
						<span class="text-danger text-sm" v-show="errors.has('sigla')">{{ errors.first('sigla') }}</span>
					</div>
				</div>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="cor fonte"
							name="cor_fonte"
							class="w-full"
							label="Cor Fonte"
							maxlength="7"
							v-model="empresaData.cor_fonte"
						/>
						<input type="color" v-model="empresaData.cor_fonte" name value="#rrggbb" />
						<span
							class="text-danger text-sm"
							v-show="errors.has('cor_fonte')"
						>{{ errors.first('cor_fonte') }}</span>
					</div>
				</div>
				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'max:7'"
							data-vv-as="cor fundo"
							name="cor_fundo"
							class="w-full"
							label="Cor Fundo"
							maxlength="7"
							v-model="empresaData.cor_fundo"
						/>
						<input type="color" v-model="empresaData.cor_fundo" name value="#rrggbb" />
						<span
							class="text-danger text-sm"
							v-show="errors.has('cor_fundo')"
						>{{ errors.first('cor_fundo') }}</span>
					</div>
				</div>

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
export default {
	data() {
		return {
			empresaData: [],
			empresa_not_found: false,
			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch("empresa/editEmpresa", this.$route.params.codigo)
			.then(res => {
				if (res.data.empresa.length > 0) {
					this.empresaData = res.data.empresa[0];
				} else {
					this.empresa_not_found = true;
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
				this.empresaData.nome != "" &&
				this.empresaData.sigla != ""
			);
		}
	},
	methods: {
		updateRecord() {
			this.$http
				.put(
					`api/empresa/${this.$route.params.codigo}`,
					{
						codigo: this.empresaData.codigo,
						nome: this.empresaData.nome,
						sigla: this.empresaData.sigla,
						cor_fonte: this.empresaData.cor_fonte,
						cor_fundo: this.empresaData.cor_fundo,
						icone: this.empresaData.icone
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
						this.$router.push("/empresa/").catch(() => {});
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
