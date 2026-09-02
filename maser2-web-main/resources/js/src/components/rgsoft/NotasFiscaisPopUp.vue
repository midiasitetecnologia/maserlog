<template>
	<vs-popup
		class="holamundo width1024px minh400"
		:title="titulo"
		:active.sync="notasVisivel"
	>
		<div class="vx-row">
			<div class="vx-col w-full">
				<vs-table
					ref="table"
					:noDataText="notasFiscaisData.length > 0
							? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.'
							: 'Não há registros para mostrar.'
					"
					v-model="selected"
					max-items="100"
					stripe
					:data="notasFiscaisData"
				>
					<template slot="thead">
						<vs-th v-if="$acl.check('admin') && permissaoNotaFiscalData"></vs-th>												
						<vs-th sort-key="numero">Número</vs-th>
						<vs-th sort-key="serie">Série</vs-th>
						<vs-th sort-key="valor">Valor</vs-th>
						<vs-th sort-key="volumes">Volumes</vs-th>						
						<vs-th></vs-th> 
						<vs-th sort-key="img_recibo">Recibo</vs-th>
						<vs-th sort-key="observ" v-if="$acl.check('admin') && permissaoNotaFiscalData">Observações</vs-th>
						<vs-th v-if="$acl.check('admin') && permissaoNotaFiscalData">
							<vs-button
								color="primary"
								size="small"
								type="flat"
								icon-pack="feather"
								icon="icon-plus"
								@click="adicionarNotaFiscalColeta(dadosColetaNotaFiscalData, 'Incluir Nota Fiscal', 'incluir')">ADICIONAR
							</vs-button>
						</vs-th>
					</template>

					<template slot-scope="{data}">
						<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
							<vs-td
								:data="data[indextr].origem_reg"
								align="center"
								v-if="$acl.check('admin') && permissaoNotaFiscalData">
								<feather-icon
									v-if="data[indextr].origem_reg == 'A4'"
									icon="UserIcon"
									svgClasses="h-5 w-5"
									class="mt-1"
								/>
							</vs-td>							

							<vs-td class="whitespace-no-wrap" :data="data[indextr].numero">
								{{ data[indextr].numero }}
							</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].serie">
								{{ data[indextr].serie }}
							</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].valor">
								{{ data[indextr].valor | currency("", 2, {thousandsSeparator: ".", decimalSeparator: ",",}) }}
							</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].volumes">
								{{ data[indextr].volumes }}
							</vs-td>

							<vs-td>
								<feather-icon
									v-if="!vueIgualTrimNull(data[indextr].img_recibo)"
									icon="CheckCircleIcon"
									svgClasses="w-4 h-4 text-success"
								/>
								<feather-icon
									v-if="!vueIgualTrimNull(data[indextr].mot_nao_entrega)"									
									icon="XCircleIcon"
									svgClasses="w-4 h-4 text-danger"
								/>
							</vs-td>

							<vs-td class="whitespace-no-wrap" :data="data[indextr].img_recibo">
								<div v-if="data[indextr].img_recibo != null">
									<feather-icon
										icon="ImageIcon"
										svgClasses="w-6 h-6 text-inherit hover:text-success stroke-current cursor-pointer"
										@click="
											exibirRecibo(
												notasUrlImgReciboData +
													data[indextr].img_recibo,
												'Recibo NF: ' +
													data[indextr].numero
											)
										"
									></feather-icon>
								</div>
								<div v-else>
									<span v-if="!vueIgualTrimNull(data[indextr].mot_nao_entrega)">
										{{data[indextr].mot_nao_entrega | mot_nao_entrega_nf}}
									</span>									
								</div>
								<div class="flex items-center" v-if="data[indextr].solic_destino_id != null">
									<template v-if="data[indextr].coleta_reentrega_numero != null">
										<span style="font-size: 12px; color:gray">Reentrega</span>
										<feather-icon icon="ArrowRightIcon" svgClasses="w-4 h-4 text-inherit ml-1 mr-1">									
										</feather-icon>
										<span style="font-size: 12px; color:gray">{{data[indextr].coleta_reentrega_numero}}</span>
									</template>
									<template v-else>
										<span style="font-size: 12px; color:gray">Reentrega</span>
										<feather-icon icon="ArrowRightIcon" svgClasses="w-4 h-4 text-inherit ml-1 mr-1">									
										</feather-icon>
										<span style="font-size: 12px; color:gray">ID {{data[indextr].coleta_reentrega_id}}</span>
									</template>
								</div>
							</vs-td>

							<vs-td class="whitespace-no-wrap" v-if="$acl.check('admin') && permissaoNotaFiscalData" :data="data[indextr].observ">								
								{{ data[indextr].observ | truncate(15) }}
							</vs-td>

							<vs-td
								class="whitespace-no-wrap"
								v-if="$acl.check('admin') && permissaoNotaFiscalData"
							>
								<feather-icon
									icon="EditIcon"
									class="ml-2"
									svgClasses="w-5 h-5 hover:text-primary stroke-current cursor-pointer"
									@click="editarNotaFiscalColeta(data[indextr], 'Alterar Nota Fiscal', 'editar')"
								/>
								<feather-icon
									v-if="data[indextr].origem_reg == 'A4'"
									icon="TrashIcon"
									svgClasses="w-5 h-5 hover:text-danger stroke-current cursor-pointer"
									class="ml-2"
									@click="confirmDeleteRecord(tr.id, tr.coleta_id, tr.numero)"
								/>
							</vs-td>
						</vs-tr>
					</template>
				</vs-table>				
			</div>
		</div>

		<div v-if="((dadosColetaNotaFiscalData.status == 'EN') && !vueIgualTrimNull(dadosColetaNotaFiscalData.mot_nao_entrega_coleta))">
			<vs-alert icon-pack="feather" icon="icon-info" class="h-full my-4 mt-8 mb-8" color="warning">				
				<div class="font-semibold">{{dadosColetaNotaFiscalData.mot_nao_entrega_coleta | mot_nao_entrega }}</div>
				<div>{{dadosColetaNotaFiscalData.obs_nao_entrega}}</div>
			</vs-alert>			
		</div>

		<vs-popup
			class="holamundo fit-content"
			:title="reciboTitulo"
			:active.sync="popupRecibo"
		>
			<div align="center">
				<img :src="recibo" height="800px" />
			</div>
		</vs-popup>

		<vs-popup
			class="holamundo"
			:title="incluirEditarNFTitulo"
			:active.sync="popupIncluirEditarNF"
		>
			<vs-alert color="danger" :active.sync="tem_erros" closable icon-pack="feather" close-icon="icon-x">
				{{ msg_erro }}
			</vs-alert>
			
			<br v-if="tem_erros" />

			<div class="vx-row">
				<div class="vx-col w-full mb-6">
					<vs-input
						autocomplete="off"
						data-vv-validate-on="blur"
						v-validate="'max:54'"
						data-vv-as="chave de acesso"
						ref="cod_barras"
						name="cod_barras"
						class="w-full"
						label="Chave de acesso"
						:disabled="funcao == 'editar'"
						v-model="cod_barras"
						v-mask="[
							'#### #### #### #### #### #### #### #### #### #### ####',
						]"
						@blur="carregarDadosNota"
					/>
					<span
						class="text-danger text-sm"
						v-show="errors.has('cod_barras')"
						>{{ errors.first("cod_barras") }}</span
					>
				</div>
			</div>

			<div class="vx-row">
				<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
					<vs-input
						autocomplete="off"
						name="numero"
						class="w-full"
						label="Número"
						disabled="true"
						v-model="numero"
					/>
				</div>

				<div class="vx-col md:w-1/2 sm:w-1/2 w-full mb-6">
					<vs-input
						autocomplete="off"
						name="serie"
						class="w-full"
						label="Série"
						disabled="true"
						v-model="serie"
					/>
				</div>
			</div>

			<div class="vx-row mb-6">
				<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
					<label for class="vs-input--label">Valor</label>
					<input
						autocomplete="off"
						data-vv-validate-on="blur"
						v-validate="'max:15'"
						data-vv-as="valor"
						maxlength="15"
						class="w-full vs-inputx vs-input--input normal hasValue"
						style="border: 1px solid rgba(0, 0, 0, 0.2)"
						name="valor"
						:disabled="funcao == 'editar'"
						v-model="valor"
						v-money="money"
					/>
					<span
						class="text-danger text-sm"
						v-show="errors.has('valor')"
						>{{ errors.first("valor") }}</span
					>
				</div>

				<div class="vx-col md:w-1/2 sm:w-1/2 w-full">
					<vs-input
						class="w-full"
						type="number"
						min="1"
						autocomplete="off"
						data-vv-validate-on="blur"
						v-validate="'numeric|max:10|max_value:2147483647'"
						data-vv-as="volumes"
						name="volumes"
						label="Volumes"
						:disabled="funcao == 'editar'"
						v-model.number="volumes"
					/>
					<span
						class="text-danger text-sm"
						v-show="errors.has('volumes')"
						>{{ errors.first("volumes") }}</span
					>
				</div>
			</div>

			<div class="vx-row" v-if="exibir_dig_cnpj">
				<div class="vx-col w-full mb-6">
					<vs-input
						autocomplete="off"
						data-vv-validate-on="blur"
						v-validate="'max:2'"
						data-vv-as="últimos dois dígitos do CNPJ"
						name="dig_cnpj"
						class="w-full"
						label="Últimos dois dígitos do CNPJ"
						:disabled="funcao == 'editar'"
						v-model="dig_cnpj"
						v-mask="['##']"
					/>
					<span
						class="text-danger text-sm"
						v-show="errors.has('dig_cnpj')"
						>{{ errors.first("dig_cnpj") }}</span
					>
				</div>
			</div>

			<div class="vx-row mb-6" v-if="vueIgualZeroNull(mot_nao_entrega)">
				<div class="vx-col w-full">
					<label for class="vs-input--label"
						>Recibo
						<small v-if="!vueIgualZeroNull(img_recibo)" class="ml-2"
							>(Clique para carregar uma nova imagem)</small
						></label
					>
					<vue-base64-file-upload
						id="upload"
						v-if="novo_recibo"
						input-class="vs-input--input normal"
						style="opacity: 0.9;!important;"
						accept="image/png,image/jpeg"
						:disable-preview="true"
						placeholder="Clique aqui para carregar a imagem"
						:file-name="img_recibo"
						:max-size="customImageMaxSize"
						@size-exceeded="onSizeExceeded"
						@file="onFile"
						@load="onLoad"
					/>
				</div>
			</div>

			<div class="vx-row mb-6" v-if="!vueIgualZeroNull(mot_nao_entrega) && funcao == 'editar'">
				<div class="vx-col w-full">
					<label class="vs-input--label">Motivo de não entrega</label>
					<v-select
						class="w-full"
						label="label"
						:options="motivoOptions"
						:clearable="false"
						v-model="motivoCombo"
					>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div class="vx-row">
				<div class="vx-col w-full mb-6">
					<vs-input
						autocomplete="off"
						data-vv-validate-on="blur"
						v-validate="'max:255'"
						data-vv-as="observações"
						name="observ"
						class="w-full"
						label="Observações"
						v-model="observ"
					/>
					<span
						class="text-danger text-sm"
						v-show="errors.has('observ')"
						>{{ errors.first("observ") }}</span
					>
				</div>
			</div>

			<vs-divider />

			<div class="vx-row">
				<div class="vx-col w-full">
					<vs-button
						v-if="funcao == 'incluir'"
						class="mr-3 mb-2"
						:disabled="!validateForm"
						@click="incluirNotaFiscalColeta"
						>Salvar</vs-button
					>
					<vs-button
						v-if="funcao == 'editar'"
						class="mr-3 mb-2"
						@click="atualizarReciboNotaFiscalColeta"
						>Salvar</vs-button
					>
					<vs-button
						type="border"
						color="danger"
						@click="fecharPopIncluirEditarNF"
						>Cancelar</vs-button
					>
				</div>
			</div>
		</vs-popup>
	</vs-popup>
</template>

<script>
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";
import labelsMixins from "@/mixins/labelsMixins";
import vSelect from "vue-select";
import { VMoney } from "v-money";
import VueBase64FileUpload from "vue-base64-file-upload";

export default {
	mixins: [procsMixins, coletaMixins, labelsMixins],
	directives: { money: VMoney },
	components: { "v-select": vSelect, VueBase64FileUpload },
	name: "notas-fiscais-pop-up",
	props: {
		titulo: {
			type: String,
			default: "Notas fiscais",
		},
	},
	data() {
		return {
			selected: [],

			popupRecibo: false,
			recibo: null,
			reciboTitulo: " ", //Aqui tem um espaço, se for null/trim, vai exibir o título "popup" default do component.

			money: {
				decimal: ",",
				thousands: ".",
				precision: 2,
			},

			customImageMaxSize: 3, // megabytes

			popupIncluirEditarNF: false,
			incluirEditarNFTitulo: " ", //Aqui tem um espaço, se for null/trim, vai exibir o título "popup" default do component.
			funcao: "",
			tem_erros: false,
			msg_erro: "",
			coleta_nf_id: "",
			coleta_id: "",
			cod_barras: "",
			numero: "",
			serie: "",
			valor: "",
			volumes: "",
			dig_cnpj: "",
			exibir_dig_cnpj: true,
			observ: "",
			mot_nao_entrega: null,
			img_recibo: "",
			recibo_base64: "",
			novo_recibo: false,

			motivoOptions: [
				{ label: "Mercadoria não conforme", value: "51" },
				{ label: "Recusa de nota fiscal", value: "52" }
			],

		};
	},
	computed: {
		notasVisivel: {
			get: function () {
				return this.$store.state.exibirNotasFiscais;
			},
			set: function () {
				this.$store.commit("EXIBIR_NOTAS_FISCAIS");
			},
		},
		notasFiscaisData() {
			return this.$store.state.coleta.notasFiscaisData;
		},
		notasUrlImgReciboData() {
			return this.$store.state.coleta.notasUrlImgReciboData;
		},
		permissaoNotaFiscalData() {
			return this.$store.state.coleta.permissaoNotaFiscalData;
		},
		dadosColetaNotaFiscalData() {
			return this.$store.state.coleta.dadosColetaNotaFiscalData;
		},
		motivoCombo: {
			get() {
				return {
					label: this.motNaoEntregaNfLabel(this.mot_nao_entrega),
					value: this.mot_nao_entrega,
				};
			},
			set(obj) {
				this.mot_nao_entrega = obj.value;
			},
		},
		validateForm() {
			return (
				!this.errors.any() &&
				this.cod_barras != "" &&
				this.valor != "" &&
				this.volumes != ""
			);
		},
	},
	methods: {
		exibirRecibo(src, titulo) {
			this.recibo = src;
			this.reciboTitulo = titulo;
			this.popupRecibo = !this.popupRecibo;
		},
		carregarDadosNota() {
			//Removemos os espaços do campo com máscara.
			let chave = this.cod_barras.replace(/\s/g, "");

			if (chave.length == 44) {
				// Série (posição 23 da chave + 3 dígitos)
				// Pegamos a partir da posição 22, pois o substr
				// considera a primeira posição da string como 0.
				this.serie = parseInt(chave.substr(22, 3));
				// Número (posição 26 da chave + 9 dígitos)
				this.numero = parseInt(chave.substr(25, 9));
			} else {
				this.serie = "";
				this.numero = "";
			}
		},
		adicionarNotaFiscalColeta(dados, titulo, funcao) {
			this.incluirEditarNFTitulo = titulo;
			this.funcao = funcao;
			this.tem_erros = false;
			this.msg_erro = "";
			this.coleta_id = dados.coleta_id;
			this.cod_barras = "";
			this.numero = "";
			this.serie = "";
			this.valor = "";
			this.volumes = "";
			this.dig_cnpj = "";			
			this.recibo_base64 = "";
			this.img_recibo = "";
			this.mot_nao_entrega = null;

			//Precisamos fazer essas atribuições para resetar o componente de upload.
			this.novo_recibo = false;
			setTimeout(() => {
				this.novo_recibo = true;
			}, 100);

			if (
				(dados.coleta_fixa == "C" &&
					!this.vueIgualZeroNull(dados.solic_origem_id)) ||
				dados.local_distrib == "S"
			) {
				this.exibir_dig_cnpj = false;
			} else {
				this.exibir_dig_cnpj = true;
			}

			this.observ = "";
			this.popupIncluirEditarNF = !this.popupIncluirEditarNF;

			// Setamos o focus no campo "Chave de acesso", o setTimeout é necessário porque precisa que o componente esteja renderizado para focar.
			setTimeout(() => {
				this.$refs["cod_barras"].$el.querySelector("input").focus();
			}, 100);
		},
		editarNotaFiscalColeta(dados, titulo, funcao) {
			this.incluirEditarNFTitulo = titulo;
			this.funcao = funcao;
			this.tem_erros = false;
			this.msg_erro = "";
			this.coleta_nf_id = dados.id;
			this.coleta_id = dados.coleta_id;
			this.cod_barras = dados.cod_barras;
			this.numero = dados.numero;
			this.serie = dados.serie;
			this.valor = dados.valor;
			this.volumes = dados.volumes;
			this.dig_cnpj = dados.dig_cnpj;

			if (
				(dados.coleta_fixa == "C" &&
					!this.vueIgualZeroNull(dados.solic_origem_id)) ||
				dados.local_distrib == "S"
			) {
				this.exibir_dig_cnpj = false;
			} else {
				this.exibir_dig_cnpj = true;
			}

			this.observ = dados.observ;
			this.img_recibo = dados.img_recibo;
			this.recibo_base64 = "";
			this.mot_nao_entrega = dados.mot_nao_entrega;

			//Precisamos fazer essas atribuições para resetar o componente de upload.
			this.novo_recibo = false;
			setTimeout(() => {
				this.novo_recibo = true;
			}, 100);
			this.popupIncluirEditarNF = !this.popupIncluirEditarNF;
		},
		async incluirNotaFiscalColeta() {
			await this.$http
				.post(
					`api/IncluirNotaFiscalColeta`,
					{
						coleta_id: this.coleta_id,
						//Removemos os espaços do campo com máscara.
						cod_barras: this.cod_barras.replace(/\s/g, ""),
						serie: this.serie,
						numero: this.numero,
						valor: this.valor,
						volumes: this.volumes,
						dig_cnpj: this.dig_cnpj,
						observ: this.observ,
						origem_reg: "A4",
						img_base64: this.recibo_base64,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then(async (response) => {
					if (response.data.retorno.cod_retorno === "Z100") {
						await this.$vs.loading({ scale: 0.5 });
						await this.getNotasFiscais(this.coleta_id);
						await this.$vs.loading.close();
						await this.fecharPopIncluirEditarNF();
					} else {
						this.tem_erros = true;
						this.msg_erro = response.data.retorno.msg_retorno;
					}
				})
				.catch((error) => {
					console.log("error", error);
				});
		},
		async atualizarReciboNotaFiscalColeta() {
			await this.$http
				.post(
					`api/AtualizarReciboNotaFiscalColeta`,
					{
						coleta_nf_id: this.coleta_nf_id,
						coleta_id: this.coleta_id,
						img_base64: this.recibo_base64,
						observ: this.observ,

						// Para respeitar a condição de alteração da imagem do recibo na API, vamos fazer este ajuste:
						// SE "recibo_base64" tiver conteúdo... indica que a imagem do recibo FOI alterada.
						// SE for vazio... o usuário está somente alterando a observação da nota e deve manter o nome da imagem original.
						img_recibo: this.vueIgualZeroNull(this.recibo_base64)
							? this.img_recibo
							: "",
						mot_nao_entrega: this.mot_nao_entrega
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then(async (response) => {
					if (response.data.retorno.cod_retorno === "Z100") {
						await this.$vs.loading({ scale: 0.5 });
						await this.getNotasFiscais(this.coleta_id);
						await this.$vs.loading.close();
						await this.fecharPopIncluirEditarNF();
					} else {
						this.tem_erros = true;
						this.msg_erro = response.data.retorno.msg_retorno;
					}
				})
				.catch((error) => {
					console.log("error", error);
				});
		},
		async fecharPopIncluirEditarNF() {
			this.popupIncluirEditarNF = !this.popupIncluirEditarNF;
		},
		async confirmDeleteRecord(id, coleta_id, numero) {
			await this.$store.commit("EXIBIR_NOTAS_FISCAIS");
			await this.$vs.dialog({
				type: "confirm",
				color: "danger",
				title: "Confirmar exclusão",
				text: `Atenção! Esta operação é definitiva. Deseja realmente excluir esta nota fiscal "${numero}"?`,
				accept: this.deleteRecord,
				cancel: this.cancelDelete,
				acceptText: "Excluir",
				cancelText: "Cancelar",
				parameters: { id: id, coleta_id: coleta_id },
			});
		},
		deleteRecord: function (parameters) {
			this.$store
				.dispatch("coletaNf/destroyColetaNf", parameters["id"])
				.then(async (res) => {
					if (res.data.status) {
						await this.$vs.loading({ scale: 0.5 });
						await this.getNotasFiscais(parameters["coleta_id"]);
						await this.showDeleteSuccess();
						await this.$vs.loading.close();
						await this.$store.commit("EXIBIR_NOTAS_FISCAIS");
					} else {
						await this.$vs.loading({ scale: 0.5 });
						await this.getNotasFiscais(parameters["coleta_id"]);
						await this.showDeleteFail(res.data.erros["message"][0]);
						await this.$vs.loading.close();
						await this.$store.commit("EXIBIR_NOTAS_FISCAIS");
					}
				})
				.catch((err) => {
					console.error(err);
				});
		},
		showDeleteSuccess() {
			this.$vs.notify({
				color: "success",
				title: "Nota fiscal deletada",
				text: "A nota fiscal selecionada foi excluída com sucesso",
			});
		},
		showDeleteFail(msg) {
			this.$vs.notify({ color: "danger", title: "Ops!", text: msg });
		},
		cancelDelete: async function (parameters) {
			await this.$vs.loading({ scale: 0.5 });
			await this.getNotasFiscais(
				this.dadosColetaNotaFiscalData.coleta_id
			);
			await this.$vs.loading.close();
			await this.$store.commit("EXIBIR_NOTAS_FISCAIS");
		},
		onFile(file) {
			this.img_recibo = file.name;
		},
		onLoad(dataUri) {
			let img_base64;
			img_base64 = dataUri.replace("data:image/jpeg;base64,", "");
			img_base64 = img_base64.replace("data:image/png;base64,", "");
			this.recibo_base64 = img_base64;
		},
		onSizeExceeded(size) {
			this.tem_erros = true;
			this.msg_erro = `A imagem possui ${size}Mb o tamanho excede os limites de ${this.customImageMaxSize}Mb!`;
			this.recibo_base64 = "";
		},
	},
};
</script>

<style lang="scss">
.con-vs-popup.minh400 .vs-popup {
	min-height: 400px;
}
.con-vs-popup.width1024px .vs-popup {
	width: 1024px;
	/* height: 100%; */
}
.con-vs-popup.fit-content .vs-popup {
	width: fit-content;
	/* height: 100%; */
}
#upload > div > input.vs-input--input.normal {
	opacity: 0.9 !important;
}
</style>