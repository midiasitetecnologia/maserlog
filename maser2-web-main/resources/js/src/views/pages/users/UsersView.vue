<template>
	<div id="page-users-view">
		<vs-alert
			color="danger"
			title="Usuário não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de usuário com o id: {{$route.params.id}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'users'}" class="text-inherit underline">Usuários</router-link>
			</span>
		</vs-alert>

		<br v-show="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">

				<!-- CARD IDENTIFICAÇÃO -->
				<div class="vx-col w-full">
					<vx-card title="Identificação" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">ID</td>
								<td>#{{ usersData.id }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Nome</td>
								<td>{{ usersData.name }}</td>
							</tr>
							<tr>
								<td class="font-semibold">{{ this.usersData.user_type =='M' ? 'ID Login' : 'Email' }}</td>
								<td>{{ usersData.email }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Tipo de usuário</td>
								<td>{{ usersData.user_type | userTypeLabel}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Ativo</td>
								<td>{{ usersData.active | active_status}}</td>
							</tr>
						</table>

						<br />

						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'users-edit', params: { id: $route.params.id }}"
							>Editar</vs-button>
							<vs-button
								v-show="$acl.check('admin') && ($store.state.AppActiveUser.uid != usersData.id)"
								type="border"
								color="danger"
								icon-pack="feather"
								icon="icon-trash"
								class="mr-4"
								@click="confirmDeleteRecord"
							>Excluir</vs-button>
							<vs-button
								v-show="!$acl.check('admin')"
								type="border"
								class="mr-4"
								:to="{name: 'users-alterar-senha', params: { id: $route.params.id }}"
							>Alterar Senha</vs-button>
							<vs-button type="border" color="danger" @click="voltar()">Voltar</vs-button>
						</div>
					</vx-card>
				</div>
			</div>

			<!-- CARD CLIENTE -->
			<div class="vx-row" v-if="usersData.cliente_id > 0">
				<div class="vx-col w-full">
					<vx-card title="Cliente" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Código</td>
								<td>{{ usersData.codigo_cliente }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Nome do cliente</td>
								<td>{{ usersData.nome_cliente }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Empresa</td>
								<td>{{ usersData.nome_empresa }}</td>
							</tr>
						</table>
					</vx-card>
				</div>
			</div>

			<!-- CARD ATUALIZAÇÃO -->
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ usersData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ usersData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
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
			usersData: [],
			data_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch("users/showUsers", this.$route.params.id)
			.then(res => {
				if (res.data.users.length > 0) {
					this.usersData = res.data.users[0];
				} else {
					this.data_not_found = true;
				}
			})
			.catch(err => {
				console.error(err);
			});
	},
	filters: {
		userTypeLabel(str) {
			if (str === "C") return "Cliente";
			else if (str === "M") return "Motorista";
			else if (str === "U") return "Usuário";
			else return str;
		}
	},
	methods: {
		confirmDeleteRecord() {
			this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este usuário "${this.usersData.id} - ${this.usersData.name}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: this.usersData.id }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("users/destroyUsers", parameters["id"])
				.then(res => {
					if (res.data.status) {
						this.showDeleteSuccess();						
						this.$router.push("/users/").catch(() => {});
					} else {
						this.showDeleteFail(res.data.erros['message'][0]);
					}
				})				
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Usuário deletado",
				text: "O usuário selecionado foi excluído com sucesso"
			});
		},				
		showDeleteFail(msg) {
			this.$vs.notify({
				color: "danger",
				title: "Ops!",
				text: msg
			});
		},
		voltar() {
			this.$router.back();
		}
	}
};
</script>

<style lang="scss">
#page-users-view {
	table {
		td {
			vertical-align: top;
			min-width: 140px;
			padding-bottom: 0.8rem;
			word-break: break-all;
		}
	}
}
</style>
