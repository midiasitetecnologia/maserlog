<template>
	<div class="page-wisdom-edit">
		<vs-alert
			color="danger"
			title="Sabedoria não encontrada"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de sabedoria com id: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todas as</span>
				<router-link :to="{name:'wisdom'}" class="text-inherit underline">Sabedorias</router-link>
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
				<div class="vx-row">
					<div class="vx-col w-full">
						<label class="vs-input--label">Texto</label>
						<vs-textarea
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required'"
							data-vv-as="texto"
							name="texto"
							class="w-full"
							rows="6"
							v-model="wisdomData.texto"
						/>
						<span class="text-danger text-sm" v-show="errors.has('texto')">{{ errors.first('texto') }}</span>
					</div>
				</div>

				<div class="vx-row mb-6">
					<div class="vx-col w-full">
						<vs-input
							autocomplete="off"
							data-vv-validate-on="blur"
							v-validate="'required|max:100'"
							data-vv-as="fonte"
							name="fonte"
							class="w-full"
							label="Fonte"
							v-model="wisdomData.fonte"
						/>
						<span class="text-danger text-sm" v-show="errors.has('fonte')">{{ errors.first('fonte') }}</span>
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
export default {
	data() {
		return {
			wisdomData: [],
			data_not_found: false,

			tem_erros: false,
			erros_form: []
		};
	},
	created() {
		this.$store
			.dispatch("wisdom/editWisdom", this.$route.params.id)
			.then(res => {
				if (res.data.wisdom.length > 0) {
					this.wisdomData = res.data.wisdom[0];
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
				this.wisdomData.texto != "" &&
				this.wisdomData.fonte != ""
			);
		}
	},
	methods: {
		updateRecord() {
			this.$http
				.put(
					`api/wisdom/${this.$route.params.id}`,
					{
						texto: this.wisdomData.texto,
						fonte: this.wisdomData.fonte
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
						this.$router.push("/sabedoria/").catch(() => {});
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
