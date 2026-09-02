<template>
	<div id="page-veiculo-view">
		<vs-alert
			color="danger"
			title="Veículo não encontrado"
			:active.sync="data_not_found"
			closable
			icon-pack="feather"
			close-icon="icon-x"
		>
			<span>Registro de veículo com a placa: {{$route.params.placa}} não encontrado.</span>
			<span>
				<span>Verifique todos os</span>
				<router-link :to="{name:'veiculo'}" class="text-inherit underline">Veículos</router-link>
			</span>
		</vs-alert>

		<br v-show="data_not_found" />

		<div v-if="!data_not_found">
			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Identificação" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Placa</td>
								<td>{{ veiculoData.placa }}</td>
							</tr>
							<tr>
								<td class="font-semibold">Tipo de Veículo</td>
								<td>{{ veiculoData.descricao_tipo }}</td>
							</tr>							
							<!-- <tr>
								//Não estamos utilizando este campo.
								<td class="font-semibold">Milk Run</td>
								<td>{{ veiculoData.milk_run | sim_nao }}</td>
							</tr> -->
							<tr>
								<td class="font-semibold">Ativo</td>
								<td>{{ veiculoData.ativo | sim_nao}}</td>
							</tr>
						</table>

						<br />

						<div class="vx-col w-full flex">
							<vs-button
								icon-pack="feather"
								icon="icon-edit"
								class="mr-4"
								:to="{name: 'veiculo-edit', params: { placa: $route.params.placa }}"
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
			</div>

			<div class="vx-col w-full">
				<vx-card title="Capacidade" class="mb-base">
					<table>
						<tr>
							<td class="font-semibold">Sistema de Carga</td>
							<td>
								<span class="mr-2" v-if="veiculoData.sis_carga_empilha == 'S'">Empilhadeira</span>
								<span class="mr-2" v-if="veiculoData.sis_carga_ponte == 'S'">Ponte Rolante</span>
								<span v-if="veiculoData.sis_carga_manual == 'S'">Manual</span>
							</td>
						</tr>
						<tr>
							<td class="font-semibold">Comprimento</td>
							<td>{{ veiculoData.comprimento | currency('', 3, { thousandsSeparator: '.', decimalSeparator: ',' })}}</td>
						</tr>
						<tr>
							<td class="font-semibold">Largura</td>
							<td>{{ veiculoData.largura | currency('', 3, { thousandsSeparator: '.', decimalSeparator: ',' })}}</td>
						</tr>
						<tr>
							<td class="font-semibold">Altura</td>
							<td>{{ veiculoData.altura | currency('', 3, { thousandsSeparator: '.', decimalSeparator: ',' })}}</td>
						</tr>
						<tr>
							<td class="font-semibold">Capacidade Cúbica</td>
							<td>{{ veiculoData.cap_cub | currency('', 3, { thousandsSeparator: '.', decimalSeparator: ',' })}}</td>
						</tr>
						<tr>
							<td class="font-semibold">Capacidade KG</td>
							<td>{{ veiculoData.cap_kg | currency('', 3, { thousandsSeparator: '.', decimalSeparator: ',' })}}</td>
						</tr>
						<tr v-if="veiculoData.nivel_cons != null">
							<td class="font-semibold">Nível de consumo</td>
							<td>{{ veiculoData.nivel_cons | nivel_cons_veiculo }}</td>
						</tr>						
						<tr v-if="veiculoData.usar_gps != null">
							<td class="font-semibold">GPS</td>
							<td>
								<span class="mr-2" v-if="veiculoData.usar_gps == 'N'">Não utilizar</span>
								<span class="mr-2" v-if="veiculoData.usar_gps == 'V'">Rastreador do veículo</span>
							</td>
						</tr>
						<tr v-if="veiculoData.placa_cavalo != null">
							<td class="font-semibold">Placa Cavalo</td>
							<td>{{ veiculoData.placa_cavalo }}</td>
						</tr>
					</table>
				</vx-card>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full">
					<vx-card title="Atualização" class="mb-base">
						<table>
							<tr>
								<td class="font-semibold">Criado</td>
								<td>{{ veiculoData.created_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
							</tr>
							<tr>
								<td class="font-semibold">Alterado</td>
								<td>{{ veiculoData.updated_at | moment("DD/MM/YYYY HH:mm:ss")}}</td>
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
			veiculoData: [],
			data_not_found: false
		};
	},
	created() {
		this.$store
			.dispatch("veiculo/showVeiculo", this.$route.params.placa)
			.then(res => {
				if (res.data.veiculo.length > 0) {
					this.veiculoData = res.data.veiculo[0];
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
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir este veículo "${this.veiculoData.placa}"?`,
				accept: this.deleteRecord,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { placa: this.veiculoData.placa }
			});
		},
		deleteRecord: function(parameters) {
			this.$store
				.dispatch("veiculo/destroyVeiculo", parameters["placa"])
				.then(res => {
					if (res.data.status) {
						this.showDeleteSuccess();
						this.$router.push("/veiculo/").catch(() => {});
					} else {
						this.showDeleteFail(res.data.erros["message"][0]);
					}
				})
				.catch(err => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Veículo deletado",
				text: "O veículo selecionado foi excluído com sucesso"
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
#page-veiculo-view {
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
