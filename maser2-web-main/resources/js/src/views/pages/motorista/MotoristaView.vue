<template>
	<div id="page-motorista-view">
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

		<br v-show="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Motorista" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">ID</td>
								<td>#{{ motoristaData.id }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Nome</td>
								<td>{{ motoristaData.nome }}</td>
							</tr>
							<tr>
								<td class="font-semibold">CPF</td>
								<td>{{ motoristaData.cpf }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Celular</td>
								<td>{{ motoristaData.celular }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Expediente</td>
								<td>
									<span v-if="(motoristaData.hr_ini_exped != null && motoristaData.hr_fim_exped != null)">{{motoristaData.hr_ini_exped | hora_min }} - {{motoristaData.hr_fim_exped | hora_min }}</span>
									<span v-else>Não definido</span>
								</td>								
							</tr>
							<tr>
								<td class="font-semibold">Ativo</td>
								<td>{{ motoristaData.ativo | sim_nao }}</td>
							</tr>
						</table>

						<br />
						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'motorista-edit', params: { id: $route.params.id }}"
							>Editar</vs-button>
							<vs-button
								type="border"
								color="danger"
								icon-pack="feather"
								icon="icon-trash"
								class="mr-4"
								@click="confirmDeleteRecord"
							>Excluir</vs-button>
							<vs-button type="border" color="danger" @click="voltar()">Voltar</vs-button>
						</div>
					</vx-card>
				</div>

				<div class="vx-col w-full">
					<vx-card title="Veículo" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Placa</td>
								<td v-if="motoristaData.placa != null">{{ motoristaData.placa }}</td>
								<td v-else>Sem veículo</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>

			<div class="vx-col w-full">
				<vx-card title="Aplicativo" class="mb-base">
					<table>
						<tr>
							<td class="font-semibold">ID Login</td>
							<td v-if="motoristaData.user_id != null">
								<span class="mr-2">{{ motoristaData.email }}</span>
								<span
									v-if="motoristaData.auto_logoff == 'S'"
								>({{motoristaData.hr_ini_login | hora_min }} - {{motoristaData.hr_fim_login | hora_min }})</span>
							</td>
							<td v-else>
								<vs-chip transparent>
									<span>Sem acesso</span>
								</vs-chip>
							</td>
						</tr>
						<tr>
							<td class="font-semibold">Login</td>
							<td>{{ motoristaData.logado | sim_nao }}</td>
						</tr>
						<tr>
							<td class="font-semibold">Último Login</td>
							<td>{{motoristaData.dt_logado | moment("DD/MM/YYYY HH:mm:ss") }}</td>
						</tr>
						<tr>
							<td class="font-semibold">Dispositivo</td>
							<td v-if="motoristaData.id_disp != null">{{motoristaData.id_disp}}</td>
							<td v-else>Não identificado</td>
						</tr>
					</table>
				</vx-card>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Cadastro alterado</td>
								<td>{{ motoristaData.dt_alt_cad | moment("DD/MM/YYYY")}} {{ motoristaData.hr_alt_cad }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ motoristaData.created_at | moment("DD/MM/YYYY HH:mm:ss") }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ motoristaData.updated_at | moment("DD/MM/YYYY HH:mm:ss") }}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			motoristaData: [],
			data_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch("motorista/showMotorista", this.$route.params.id)
			.then(res => {
				if (res.data.motorista.length > 0) {
					this.motoristaData = res.data.motorista[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
	},
	methods: {
		confirmDeleteRecord() {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este motorista "${this.motoristaData.id} - ${this.motoristaData.nome}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: this.motoristaData.id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("motorista/destroyMotorista", parameters["id"])
				.then(() => {
					this.showDeleteSuccess();
					this.$router.push("/motorista/").catch(() => {});
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Motorista deletado",
				text: "O motorista selecionado foi excluído com sucesso"
			});
		},
		voltar() {
			this.$router.back();
		}
	}
};
</script>

<style lang="scss">
#page-motorista-view {
	table {
		td {
			vertical-align: top;
			min-width: 180px;
			padding-bottom: 0.8rem;
			word-break: break-all;
		}
	}
}
</style>
