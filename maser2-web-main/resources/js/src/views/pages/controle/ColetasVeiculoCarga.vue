<template>
	<div>
		<div class="vx-row mb-6">
			<div class="vx-col w-full">
				<vx-card>
					<!-- Rota do veículo -->
					<vs-table
						class="mb-4"
						ref="table"
						:noDataText="coletasVeiculoCargaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						max-items="100"
						stripe
						:data="coletasVeiculoCargaData.coletas"
					>
						<div slot="header" class="flex items-center flex-grow justify-between mb-4">
							<div class="flex items-center">
								<span class="font-semibold mr-4">Rota do veículo</span>
								<span class="mr-2">Local de saída</span>
								<v-select
									style="min-width: 122px"
									class="mr-4"
									:options="localSaidaOptions"
									:clearable="false"
									v-model="filtros[0].localSaidaSelected"
								>
									<div slot="no-options">Opção não disponível</div>
								</v-select>
								<span class="mr-2">Hora da saída</span>
								<flat-pickr
									class="mr-2"
									style="max-width: 60px"
									:config="configdateTimePickerTime"
									v-model="filtros[0].hora_saida"
								/>
								<vs-button
									v-if="coletasVeiculoCargaData.coletas.length > 1"
									color="primary"
									type="flat"
									@click="definirRota(coletasVeiculoCargaData.coletas)"
								>Definir Rota</vs-button>
							</div>
							<div class="flex items-center">
								<feather-icon
									@click="refresh"
									icon="RotateCwIcon"
									svgClasses="h-4 w-4"
									class="cursor-pointer mr-4"
								/>
							</div>
						</div>

						<template slot="thead">
							<vs-th>Solicitação</vs-th>
							<vs-th class="whitespace-no-wrap">Prev. Coleta</vs-th>
							<vs-th>Coleta</vs-th>
							<vs-th class="whitespace-no-wrap">Prev. Entrega</vs-th>
							<vs-th>Entrega</vs-th>
							<vs-th>Carga</vs-th>
							<vs-th>Distância</vs-th>
							<vs-th class="whitespace-no-wrap">Prev. Chegada</vs-th>
							<vs-th>Status</vs-th>
							<!-- Ações -->
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr
								:data="tr"
								:state="['C2', 'C3', 'C4', 'E2', 'E3', 'E4'].includes(data[indextr].status) ? 'warning' : null"
								:key="indextr"
								v-for="(tr, indextr) in data"
							>
								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center text-center">
										<span v-if="data[indextr].numero != null">{{data[indextr].numero}}</span>
										<span v-else>ID: {{data[indextr].coleta_id}}</span>
										<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div
										v-if="data[indextr].dt_prev_coleta != null"
										:style="data[indextr].dt_efet_coleta == null ? emAtraso(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta): ''"
									>{{exibirDia(data[indextr].dt_prev_coleta) | moment("DD MMM")}} {{data[indextr].hr_prev_coleta | hora_min }}</div>
									<div
										style="font-size: 12px; font-weight: 500;"
									>{{calcTempoRestante(data[indextr].dt_prev_coleta, data[indextr].hr_prev_coleta)}}</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>
										<span>{{data[indextr].local_coleta | truncate(25)}}</span>
									</div>
									<div>
										<div class="flex items-center">
											<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_coleta}}</span>
											<span
												style="font-size: 12px; color:gray"
											>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}</span>
											<feather-icon
												v-if="data[indextr].hr_sai_coleta != null"
												class="ml-1"
												icon="CheckCircleIcon"
												svgClasses="w-4 h-4 text-success"
											/>
										</div>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div
										v-if="data[indextr].dt_prev_entrega != null"
										:style="emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)"
									>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
									<div v-if="data[indextr].entrega_urgente == 'S'">
										<span class="badge_vermelho">urgente</span>
									</div>
									<div v-else style="font-size: 12px; font-weight: 500;">
										{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<span>{{data[indextr].local_entrega | truncate(25)}}</span>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center">
										<span>{{data[indextr].vol_carga}}</span>
									</div>
									<div class="flex items-center" v-if="data[indextr].dim_carga != '-'">
										<span
											:style="['C2', 'C3', 'C4', 'E2', 'E3', 'E4'].includes(data[indextr].status) ? 'font-size: 12px; color:gray' : 'font-size: 12px;'"
										>{{data[indextr].dim_carga}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">{{data[indextr].distancia_destino}}</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div
										class="flex items-center"
										:class="data[indextr].hr_prev_chegada > data[indextr].hr_prev_col_ent ? 'text-danger': ''"
									>
										<span class="mr-1" v-if="data[indextr].tempo_rota != ''">{{data[indextr].tempo_rota}}</span>
									</div>
									<div class="flex items-center">
										<span style="font-size: 12px; color:gray">({{data[indextr].hr_prev_chegada | hora_min}})</span>
										<vx-tooltip
											v-if="data[indextr].msg_tempo != ''"
											:text="data[indextr].msg_tempo"
											position="top"
										>
											<feather-icon
												class="ml-1 mt-2"
												:icon="data[indextr].tempo_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
												:svgClasses="data[indextr].tempo_ok == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-warning'"
											></feather-icon>
										</vx-tooltip>
										<feather-icon
											class="ml-1"
											v-else
											:icon="data[indextr].tempo_ok == 'S' ? 'CheckCircleIcon' : 'AlertCircleIcon'"
											:svgClasses="data[indextr].tempo_ok == 'S' ? 'w-4 h-4 text-success' : 'w-4 h-4 text-warning'"
										></feather-icon>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center text-center">
										<vs-avatar
											size="small"
											:color="corStatus(data[indextr].status)"
											:text="inicialStatus(data[indextr].status)"
										/>
										<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap" align="center">
									<vx-tooltip
										v-if="['C0', 'CR', 'E0'].includes(data[indextr].status)"
										class="mr-2"
										:text="data[indextr].status == 'C0' ? 'Autorizar coleta' : 'Autorizar entrega'"
										position="left"
									>
										<vs-button
											color="primary"
											type="flat"
											size="small"
											@click="autorizar(data[indextr])"
										>AUTORIZAR</vs-button>
									</vx-tooltip>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<vx-tooltip
										v-if="['C0', 'C1', 'E0', 'E1'].includes(data[indextr].status)"
										text="Remover a solicitação desta carga"
										position="left"
									>
										<feather-icon
											class="ml-2 mt-1"
											icon="FileMinusIcon"
											:svgClasses="'w-5 h-5 hover:text-danger'"
											@click="remover(data[indextr])"
										/>
									</vx-tooltip>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex flex-items-center text-center">
										<vx-tooltip
											:text="veiculoCargaData.motorista != null ? 'Enviar instrução para ' + veiculoCargaData.motorista : 'Enviar instrução'"
											class="mr-2"
											position="left"
										>
											<feather-icon
												icon="MessageSquareIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="informarInstrucao(data[indextr])"
											/>
										</vx-tooltip>
										<!-- E3 - Liberamos a baldeação para o status "E3" => quando o veículo já chegou no local de ENTREGA e o atendimento vai demorar. 
										A carga pode ser baldeada para outro veículo para aguardar o atendimento e assim liberar o veículo anterior para outro serviço.-->
										<vx-tooltip
											v-if="['E1', 'E3'].includes(data[indextr].status)"
											text="Autorizar baldeação"
											position="left"
										>	
											<!-- O "style" está sendo alterado de cor com os hexadecimais, porque a linha toda pode ficar colorida e sobrepor a regra, 
											desta forma a regra do style com !important vai comandar.-->
											<feather-icon
												icon="RepeatIcon"
												svgClasses="w-4 h-4 hover:text-success"												
												:style="data[indextr].baldeada == 'S' ? 'color:#28C76F; !important' : 
												        data[indextr].baldeada == 'N' && !vueIgualTrimNull(data[indextr].placa_baldeacao) ? 
															 'color:#FF9F43; !important' : 'color:#626262; !important'"
												@click="enviarInstrBaldeacao(data[indextr].coleta_id)"
											/>
										</vx-tooltip>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex flex-items-center">
										<span v-if="data[indextr].notas_fiscais > 0">
											<vx-tooltip class="mr-2" text="Notas fiscais" position="left">
												<feather-icon
													icon="FileIcon"
													:svgClasses="'w-5 h-5 hover:text-success'"
													@click="notasFiscais(data[indextr].coleta_id)"
												/>
											</vx-tooltip>
										</span>
										<span v-else>
											<vx-tooltip
												v-if="data[indextr].img_rom_coleta != null"
												class="mr-2"
												text="Romaneios"
												position="left"
											>
												<feather-icon
													icon="FileIcon"
													:svgClasses="'w-5 h-5 hover:text-success'"
													@click="romaneios(data[indextr].url_imagem, data[indextr])"
												/>
											</vx-tooltip>
										</span>

										<vx-tooltip
											v-if="data[indextr].coleta_pos > 0 || data[indextr].coleta_log > 0"
											text="Auditoria"
											position="left"
										>
											<feather-icon
												icon="FileTextIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="auditoria(data[indextr].coleta_id)"
											/>
										</vx-tooltip>
									</div>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<!-- Carga do veículo -->
		<div class="vx-row">
			<div class="vx-col w-full">
				<vx-card v-show="coletasVeiculoCargaData.carga.length > 0">
					<vs-table
						ref="table"
						:noDataText="coletasVeiculoCargaData.length > 0 ? 'Não há registros para mostrar. Verifique o texto pesquisado e a página atual.' : 'Não há registros para mostrar.'"
						v-model="selected"
						max-items="100"
						stripe
						:data="coletasVeiculoCargaData.carga"
					>
						<div slot="header" class="flex items-center flex-grow justify-between mb-4">
							<div class="flex items-center">
								<span class="font-semibold mr-4">Carga do veículo</span>
							</div>
							<div class="flex items-center">
								<feather-icon
									@click="refresh"
									icon="RotateCwIcon"
									svgClasses="h-4 w-4"
									class="cursor-pointer mr-4"
								/>
							</div>
						</div>

						<template slot="thead">
							<vs-th>Solicitação</vs-th>
							<vs-th>Coleta</vs-th>
							<vs-th class="whitespace-no-wrap">Prev. Entrega</vs-th>
							<vs-th>Entrega</vs-th>
							<vs-th>Carga</vs-th>
							<vs-th>Status</vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
							<vs-th></vs-th>
						</template>

						<template slot-scope="{data}">
							<vs-tr :data="tr" :key="indextr" v-for="(tr, indextr) in data">
								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center text-center">
										<span v-if="data[indextr].numero != null">{{data[indextr].numero}}</span>
										<span v-else>ID: {{data[indextr].coleta_id}}</span>
										<col-fixa :coleta_fixa="data[indextr].coleta_fixa" />
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div>
										<span>{{data[indextr].local_coleta | truncate(25)}}</span>
									</div>
									<div>
										<div class="flex items-center">
											<span class="mr-2" style="font-size: 12px; color:gray">{{data[indextr].placa_coleta}}</span>
											<span
												style="font-size: 12px; color:gray"
											>{{exibirDia(data[indextr].dt_efet_coleta) | moment("DD MMM") }} {{data[indextr].hr_sai_coleta | hora_min }}</span>
											<feather-icon
												v-if="data[indextr].hr_sai_coleta != null"
												class="ml-1"
												icon="CheckCircleIcon"
												svgClasses="w-4 h-4 text-success"
											/>
										</div>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div
										v-if="data[indextr].dt_prev_entrega != null"
										:style="emAtraso(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)"
									>{{exibirDia(data[indextr].dt_prev_entrega) | moment("DD MMM")}} {{data[indextr].hr_prev_entrega | hora_min }}</div>
									<div v-if="data[indextr].entrega_urgente == 'S'">
										<span class="badge_vermelho">urgente</span>
									</div>
									<div v-else style="font-size: 12px; font-weight: 500;">
										{{calcTempoRestante(data[indextr].dt_prev_entrega, data[indextr].hr_prev_entrega)}}
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<span>{{data[indextr].local_entrega | truncate(25)}}</span>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center">
										<span>{{data[indextr].vol_carga}}</span>
									</div>
									<div class="flex items-center" v-if="data[indextr].dim_carga != '-'">
										<span style="font-size: 12px; color:gray">{{data[indextr].dim_carga}}</span>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex items-center">
										<vs-avatar
											size="small"
											:color="corStatus(data[indextr].status)"
											:text="inicialStatus(data[indextr].status)"
										/>
										<div>
											<div>
												<span class="ml-1">{{data[indextr].status | coleta_status_res}}</span>
											</div>
											<div
												v-if="data[indextr].coleta_fixa == 'M' && vueIgualZeroNull(data[indextr].solic_origem_id) && data[indextr].status == 'CR'"
											>
												<span
													v-if="data[indextr].qtde_notas_distrib > 0"
													class="ml-1 text-warning"
													style="font-size: 12px"
												>Aguardando distribuição</span>
												<span v-else class="ml-1 text-success" style="font-size: 12px">Distribuída</span>
											</div>
										</div>
									</div>
								</vs-td>								

								<vs-td class="whitespace-no-wrap" align="center">
									<vx-tooltip
										v-if="podeAutorizar(data[indextr])"
										class="mr-2"
										:text="data[indextr].status == 'C0' ? 'Autorizar coleta' : 'Autorizar entrega'"
										position="left"
									>
										<vs-button
											color="primary"
											type="flat"
											size="small"
											@click="autorizar(data[indextr])"
										>AUTORIZAR</vs-button>
									</vx-tooltip>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex flex-items-center text-center" v-if="['E1', 'E2', 'E3', 'E4'].includes(data[indextr].status)">
										<vx-tooltip
											v-if="data[indextr].entrega_consolidada == 'S'"
											text="Desmarcar entrega consolidada"
											position="left"
										>												
											<!-- O "style" está sendo alterado de cor com os hexadecimais, porque a linha toda pode ficar colorida e sobrepor a regra, 
											desta forma a regra do style com !important vai comandar.-->
											<feather-icon
												icon="Share2Icon"
												svgClasses="w-5 h-5 hover:text-success"
												class="shareRotate"
												:style="'color:#28C76F; !important'"
												@click="setarEntregaConsolidada(data[indextr].coleta_id, false)"
											/>
										</vx-tooltip>
										<vx-tooltip
											v-else
											text="Marcar entrega como consolidada"
											position="left"
										>												
											<!-- O "style" está sendo alterado de cor com os hexadecimais, porque a linha toda pode ficar colorida e sobrepor a regra, 
											desta forma a regra do style com !important vai comandar.-->
											<feather-icon
												icon="Share2Icon"
												svgClasses="w-5 h-5 hover:text-success"
												class="shareRotate"
												:style="'color:#626262; !important'"
												@click="setarEntregaConsolidada(data[indextr].coleta_id, true)"
											/>
										</vx-tooltip>
									</div>									
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex flex-items-center text-center">
										<vx-tooltip
											v-if="podeEnviarInstrucao(data[indextr])"
											:text="veiculoCargaData.motorista != null ? 'Enviar instrução para ' + veiculoCargaData.motorista : 'Enviar instrução'"
											class="mr-2"
											position="left"
										>
											<feather-icon
												icon="MessageSquareIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="informarInstrucao(data[indextr])"
											/>
										</vx-tooltip>
										<vx-tooltip
											v-if="podeAutorizarBaldeacao(data[indextr])"
											text="Autorizar baldeação"
											position="left"
										>	
											<!-- O "style" está sendo alterado de cor com os hexadecimais, porque a linha toda pode ficar colorida e sobrepor a regra, 
											desta forma a regra do style com !important vai comandar.-->
											<feather-icon
												icon="RepeatIcon"
												svgClasses="w-4 h-4 hover:text-success"
												:style="data[indextr].baldeada == 'S' ? 'color:#28C76F; !important' : 
												        data[indextr].baldeada == 'N' && !vueIgualTrimNull(data[indextr].placa_baldeacao) ? 
															 'color:#FF9F43; !important' : 'color:#626262; !important'"
												@click="enviarInstrBaldeacao(data[indextr].coleta_id)"
											/>
										</vx-tooltip>
									</div>
								</vs-td>

								<vs-td class="whitespace-no-wrap">
									<div class="flex flex-items-center">
										<span v-if="data[indextr].notas_fiscais > 0">
											<vx-tooltip class="mr-2" text="Notas fiscais" position="left">
												<feather-icon
													icon="FileIcon"
													:svgClasses="'w-5 h-5 hover:text-success'"
													@click="notasFiscais(data[indextr].coleta_id)"
												/>
											</vx-tooltip>
										</span>
										<span v-else>
											<vx-tooltip
												v-if="data[indextr].img_rom_coleta != null"
												class="mr-2"
												text="Romaneios"
												position="left"
											>
												<feather-icon
													icon="FileIcon"
													:svgClasses="'w-5 h-5 hover:text-success'"
													@click="romaneios(data[indextr].url_imagem, data[indextr])"
												/>
											</vx-tooltip>
										</span>

										<vx-tooltip
											v-if="data[indextr].coleta_pos > 0 || data[indextr].coleta_log > 0"
											text="Auditoria"
											position="left"
										>
											<feather-icon
												icon="FileTextIcon"
												:svgClasses="'w-5 h-5 hover:text-success'"
												@click="auditoria(data[indextr].coleta_id)"
											/>
										</vx-tooltip>
									</div>
								</vs-td>
							</vs-tr>
						</template>
					</vs-table>
				</vx-card>
			</div>
		</div>

		<vs-popup class="holamundo width50" :title="'Definir rota'" :active.sync="popupRota">
			<vs-alert icon-pack="feather" icon="icon-info" class="h-full mb-1" color="primary">
				<div>Clique e arraste para organizar a rota.</div>
			</vs-alert>

			<vs-list>
				<draggable :list="organizarRota">
					<transition-group>
						<template v-for="item in organizarRota">
							<vs-list-item
								v-if="
								(item.status != 'C2' && item.status != 'C3' && item.status != 'C4' &&
								 item.status != 'E2' && item.status != 'E3' && item.status != 'E4')"
								:key="item.coleta_id"
								class="list-item"
								:title="item.numero != null ? item.numero + '' : 'ID: ' + item.coleta_id"
								:subtitle="item.destino"
							>
								<vs-avatar
									slot="avatar"
									size="small"
									:color="corStatus(item.status)"
									:text="inicialStatus(item.status)"
								/>
								<div class="flex flex-items-center justify-between">
									<div class="mr-4" v-if="item.hr_prev_col_ent != null">
										<span class="mr-1">Previsão:</span>
										<span class="font-semibold">{{item.hr_prev_col_ent | hora_min}}</span>
									</div>
									<div class="mr-4" v-if="item.distancia_destino != '-'">
										<span class="mr-1">Distância</span>
										<span class="font-semibold">{{item.distancia_destino}}</span>
									</div>
									<div class="mr-4" v-if="item.hr_prev_chegada != null">
										<span class="mr-1">Prev. Chegada</span>
										<span class="font-semibold">{{item.hr_prev_chegada | hora_min}}</span>
									</div>
								</div>
							</vs-list-item>
						</template>
					</transition-group>
				</draggable>
			</vs-list>

			<vs-divider />

			<div class="flex flex-row-reverse">
				<vs-button class="mr-3 mb-2" type="flat" color="danger" @click="popupRota=false">Cancelar</vs-button>
				<vs-button class="mr-3 mb-2" @click="gravarSeqAtendRotaCarga()">Definir</vs-button>
			</div>
		</vs-popup>

		<vs-prompt
			class="max550"
			@accept="autorizado(promptDados)"
			:title="'Autorizar ' + promptEtapa"
			accept-text="Autorizar"
			cancel-text="Cancelar"
			:active.sync="activePrompt"
		>
			<div class="con-exemple-prompt">
				<p class="mb-4">{{promptText}}</p>
				<p>Você autoriza a {{promptEtapa}} e o envio de uma notificação ao motorista?</p>
			</div>
		</vs-prompt>

		<vs-prompt
			class="max550"
			color="danger"
			@accept="desvinculado(promptDados)"
			:title="promptDados.numero != null ? 'Solicitação: ' + promptDados.numero : 'Solicitação: ID ' + promptDados.coleta_id"
			accept-text="SIM"
			cancel-text="NÃO"
			:active.sync="activePromptDesvincular"
		>
			<div class="con-exemple-prompt">
				<p>
					<span class="mr-1 font-semibold">Destino:</span>
					{{promptDados.destino}}
				</p>
				<p class="mb-4">
					<span class="mr-1 font-semibold">Etapa:</span>
					{{promptEtapa}}
				</p>
				<p>Deseja desvincular esta solicitação do veículo {{veiculoCargaData.placa}}?</p>
			</div>
		</vs-prompt>

		<vs-popup class="holamundo minh450" title="Enviar Instrução" :active.sync="popupInstrucao">
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<vs-input class="w-full" disabled label="Última instrução enviada" v-model="ultimaInstrucao" />
				</div>
			</div>
			<div class="vx-row mb-3">
				<div class="vx-col w-full">
					<label class="vs-input--label">Nova instrução</label>
					<v-select v-model="instrucao_local" :options="instrucaoOptions" clearable>
						<div slot="no-options">Opção não disponível</div>
					</v-select>
				</div>
			</div>

			<div class="vx-row mb-3" v-if="instrucaoValue == '99'">
				<div class="vx-col w-full">
					<vs-input
						maxlength="255"
						class="w-full"
						label="Texto da instrução"
						v-model="digitarInstrucao"
					/>
				</div>
			</div>
			<br v-if="instrucaoValue != '05' && instrucaoValue != '99'" />
			<br v-if="instrucaoValue != '05' && instrucaoValue != '99'" />
			<vs-alert icon-pack="feather" icon="icon-info" class="h-full my-4 mt-8 mb-6" color="warning">
				<div>Uma notificação será enviada para o celular do motorista{{motoristaInstrucao != null ? ':' : '.'}}</div>
				<div class="font-semibold">{{motoristaInstrucao}}</div>
			</vs-alert>
			<vs-button
				type="border"
				:disabled="validarInstrucao"
				@click="enviarInstrucao(coletaIdInstrucao, instrucaoValue, digitarInstrucao)"
			>Enviar instrução</vs-button>
		</vs-popup>
	</div>
</template>

<script>
import controleMixins from "@/mixins/controleMixins";
import procsMixins from "@/mixins/procsMixins";
import coletaMixins from "@/mixins/coletaMixins";

import draggable from "vuedraggable";
import vSelect from "vue-select";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
import ColFixa from "@/components/rgsoft/ColFixa.vue";

export default {
	name: "coletas-veiculo-carga",
	mixins: [controleMixins, procsMixins, coletaMixins],
	components: {
		draggable,
		vSelect,
		flatPickr,
		ColFixa,
	},
	data() {
		return {
			popupRota: false,
			organizarRota: [],

			activePrompt: false,
			activePromptDesvincular: false,
			promptEtapa: null,
			promptText: null,
			promptDados: [],

			filtros: [
				{
					localSaidaSelected: {
						label: this.$store.state.controle
							.coletasVeiculoCargaFiltros.local_saida_descr,
						value: this.$store.state.controle
							.coletasVeiculoCargaFiltros.local_saida,
					},
					hora_saida: this.$store.state.controle
						.coletasVeiculoCargaFiltros.hora_saida,
				},
			],

			localSaidaOptions: [
				{ label: "Pavilhão", value: "P" },
				{ label: "Veículo", value: "V" },
			],

			configdateTimePickerTime: {
				enableTime: true,
				enableSeconds: false,
				noCalendar: true,
				locale: Portuguese,
			},

			popupInstrucao: false,
			coletaIdInstrucao: 0,
			ultimaInstrucao: null,
			digitarInstrucao: null,
			instrucaoValue: null,
			motoristaInstrucao: null,

			instrucaoOptions: [
				{ label: "Manter carga no veículo", value: "02" },
				{ label: "Descarregar no pavilhão", value: "03" },
				{ label: "Ir para pavilhão", value: "04" },
				{ label: "Digitar instrução", value: "99" },
			],
		};
	},
	computed: {
		coletasVeiculoCargaData() {
			return this.$store.state.controle.dadosColetasVeiculoCargaData;
		},
		veiculoCargaData() {
			return this.$store.state.controle.dadosVeiculoCargaData;
		},
		instrucao_local: {
			get() {
				if (this.instrucaoValue == null) {
					return "Selecione a instrução";
				} else {
					return {
						label: this.instrucaoLabel(this.instrucaoValue),
						value: this.instrucaoValue,
					};
				}
			},
			set(obj) {
				if (obj != null) {
					this.instrucaoValue = obj.value;
				} else {
					this.instrucaoValue = null;
				}
			},
		},
		validarInstrucao() {
			let desabilitado = true;

			if (this.instrucaoValue != null) {
				desabilitado = false;

				if (
					this.instrucaoValue == "99" &&
					(this.digitarInstrucao == "" ||
						this.digitarInstrucao == null)
				) {
					desabilitado = true;
				}
			}

			return desabilitado;
		},
	},
	watch: {
		filtros: {
			handler: function (newValue, oldValue) {
				const payload = {
					placa: this.veiculoCargaData.placa,
					local_saida_descr: this.filtros[0].localSaidaSelected.label,
					local_saida: this.filtros[0].localSaidaSelected.value,
					hora_saida: this.filtros[0].hora_saida,
				};
				this.$store.commit(
					"controle/SET_COL_VEICULO_CARGA_FILTROS",
					payload
				);
			},
			deep: true,
		},
	},
	methods: {
		async refresh() {
			await this.$vs.loading({ scale: 0.5 });

			await this.getDataAtual();
			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);
			await this.retornarEntregasPendentesCarga(
				this.veiculoCargaData.placa
			);
			await this.atualizarColetasVeiculoEFiltros();

			await this.$vs.loading.close();
		},
		autorizar(dados) {
			if (dados.status == "C0") {
				this.promptEtapa = "coleta";
				this.promptText = `Esta coleta será realizada por esse veículo: "${this.veiculoCargaData.placa}".`;
			} else {
				this.promptEtapa = "entrega";
				this.promptText = `Esta entrega será realizada por esse veículo: "${this.veiculoCargaData.placa}".`;
			}

			this.promptDados = dados;
			this.activePrompt = true;
		},
		async autorizado(dados) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: dados.coleta_id,
				placa: this.veiculoCargaData.placa,
				autorizar: "S",
			};

			if (dados.status == "C0") {
				await this.$store
					.dispatch("controle/definirVeiculoColeta", payload)
					.catch((err) => {
						console.error(err);
					});
			} else {
				await this.$store
					.dispatch("controle/definirVeiculoEntrega", payload)
					.catch((err) => {
						console.error(err);
					});
			}

			await this.atualizarColetasVeiculoEFiltros();

			await this.$vs.loading.close();
		},
		async remover(dados) {
			if (dados.status == "C0" || dados.status == "C1") {
				this.promptEtapa = "Coleta";
			} else {
				this.promptEtapa = "Entrega";
			}
			this.promptDados = dados;
			this.activePromptDesvincular = true;
		},
		async desvinculado(dados) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: dados.coleta_id,
				placa: this.veiculoCargaData.placa,
			};

			await this.$store
				.dispatch("controle/desvincularVeiculoSolicitacao", payload)
				.catch((err) => {
					console.error(err);
				});
			await this.retornarDadosVeiculoCarga(this.veiculoCargaData.placa);
			await this.retornarEntregasPendentesCarga(
				this.veiculoCargaData.placa
			);
			await this.atualizarColetasVeiculoEFiltros();

			await this.$vs.loading.close();
		},
		async atualizarColetasVeiculoEFiltros() {
			const payload = {
				placa: this.veiculoCargaData.placa,
				local_saida_descr: this.filtros[0].localSaidaSelected.label,
				local_saida: this.filtros[0].localSaidaSelected.value,
				hora_saida: this.filtros[0].hora_saida,
			};
			await this.retornarColetasVeiculoCarga(payload);
			await this.$store.commit(
				"controle/SET_COL_VEICULO_CARGA_FILTROS",
				payload
			);
		},
		definirRota(dados) {
			this.organizarRota = JSON.parse(JSON.stringify(dados));
			this.popupRota = !this.popupRota;
		},
		async gravarSeqAtendRotaCarga() {
			await this.$vs.loading({ scale: 0.5 });
			await this.$http
				.post(
					`api/controle/GravarSeqAtendRotaCarga`,
					{
						lista_coletas: this.organizarRota,
					},
					{
						headers: {
							Authorization:
								"Bearer " + localStorage.getItem("apiToken"),
						},
					}
				)
				.then(async (response) => {
					if (response.data.status) {
						await this.atualizarColetasVeiculoEFiltros();
						await this.$vs.loading.close();
						this.popupRota = !this.popupRota;
					}
				})
				.catch(async (error) => {
					await this.$vs.loading.close();
				});
		},
		informarInstrucao(dados) {
			this.coletaIdInstrucao = dados.coleta_id;
			this.ultimaInstrucao = dados.txt_instrucao;
			this.instrucaoValue = null;
			this.digitarInstrucao = null;
			this.motoristaInstrucao = this.veiculoCargaData.motorista;
			this.popupInstrucao = !this.popupInstrucao;
		},
		async enviarInstrucao(col_id, instr, txt_instr) {
			const payload = {
				coleta_id: col_id,
				instrucao: instr,
				txt_instrucao: txt_instr,
			};

			await this.$store
				.dispatch("controle/enviarInstrucaoColeta", payload)
				.catch((err) => {
					console.error(err);
				});

			await this.atualizarColetasVeiculoEFiltros();
			this.popupInstrucao = false;
		},
		podeAutorizar(dados) {
			let retorno = false;
			if (
				dados.status == "C0" ||
				dados.status == "CR" ||
				dados.status == "E0"
			) {
				retorno = true;
				if (
					dados.coleta_fixa == "M" &&
					this.vueIgualZeroNull(dados.solic_origem_id)
				) {
					retorno = false;
				}
			}
			return retorno;
		},
		podeEnviarInstrucao(dados) {
			let retorno = true;
			if (
				dados.coleta_fixa == "M" &&
				this.vueIgualZeroNull(dados.solic_origem_id)
			) {
				if (this.vueIgualZeroNull(dados.qtde_notas_distrib)) {
					retorno = false;
				}
			}
			return retorno;
		},
		podeAutorizarBaldeacao(dados) {
			//O teste da carga está levemente diferente do teste da Rota. Cuidar as alterações e campos.

			// E3 - Liberamos a baldeação para o status "E3" => quando o veículo já chegou no local de ENTREGA e o atendimento vai demorar.
			// A carga pode ser baldeada para outro veículo para aguardar o atendimento e assim liberar o veículo anterior para outro serviço.
			let retorno = false;
			if (["CR", "E1", "E3"].includes(dados.status)) {
				retorno = true;
				if (
					dados.coleta_fixa == "M" &&
					this.vueIgualZeroNull(dados.solic_origem_id)
				) {
					retorno = false;
				}
			}
			return retorno;
		},
		async setarEntregaConsolidada(col_id, consol) {
			await this.$vs.loading({ scale: 0.5 });

			const payload = {
				coleta_id: col_id,
				consolidada: consol,
			};

			await this.$store
				.dispatch("controle/setarEntregaConsolidada", payload)
				.then(async response => {				

					if (response.data.retorno.cod_retorno != "Z100") {
						await this.$vs.notify({
							time: 10000,
							text: response.data.retorno.msg_retorno,
							iconPack: "feather",
							icon: "icon-alert-circle",
							color: "danger"
						});
					} 
				})
				.catch(async error => {
					await this.$vs.loading.close();
				});

			await this.atualizarColetasVeiculoEFiltros();
			await this.$vs.loading.close();
		},
	},
};
</script>

<style lang="scss">
.con-vs-popup.width50 .vs-popup {
	width: 50%;
	/* height: 100%; */
}
.sortable-chosen {
	background: rgba(var(--vs-primary), 0.05) !important;
}

.shareRotate {   
  display:inline-block;  
  transform: rotate(90deg)
 }
</style>
