import Vue from 'vue'
import Vuex from 'vuex'

import state from "./state"
import getters from "./getters"
import mutations from "./mutations"
import actions from "./actions"

Vue.use(Vuex)

import moduleDataAtual from './data-atual/moduleDataAtual.js'
import moduleAuth from './auth/moduleAuth.js'
import moduleMotorista from './motorista/moduleMotorista.js'
import moduleVeiculo from './veiculo/moduleVeiculo.js'
import moduleTipoVeiculo from './tipo-veiculo/moduleTipoVeiculo.js'
import moduleCliente from './cliente/moduleCliente.js'
import moduleColetaNf from './coleta-nf/moduleColetaNf.js'
import moduleColetaFixa from './coleta-fixa/moduleColetaFixa.js'
import moduleColetaFixaBloq from './coleta-fixa-bloq/moduleColetaFixaBloq.js'
import moduleColeta from './coleta/moduleColeta.js'
import moduleAtendimento from './atendimento/moduleAtendimento.js'
import moduleControle from './controle/moduleControle.js'
import moduleOperacao from './operacao/moduleOperacao.js'
import moduleResumoDia from './resumo-dia/moduleResumoDia.js'
import moduleDashboard from './dashboard/moduleDashboard.js'
import moduleUsers from './users/moduleUsers.js'
import moduleSysCfg from './sys-cfg/moduleSysCfg.js'
import moduleDistanceMatrix from './distance-matrix/moduleDistanceMatrix.js'
import moduleEmpresa from './empresa/moduleEmpresa.js'
import moduleLogPro from './log-pro/moduleLogPro.js'
import moduleWisdom from './wisdom/moduleWisdom.js'

export default new Vuex.Store({
    getters,
    mutations,
    state,
    actions,
    modules: {        
        dataAtual: moduleDataAtual,
        auth: moduleAuth,        
        motorista: moduleMotorista,
        veiculo: moduleVeiculo,
        tipoVeiculo: moduleTipoVeiculo,
        cliente: moduleCliente,
        coletaNf: moduleColetaNf,
        coletaFixa: moduleColetaFixa,
        coletaFixaBloq: moduleColetaFixaBloq,
        coleta: moduleColeta,
        atendimento: moduleAtendimento,
        controle: moduleControle,
        operacao: moduleOperacao,
        resumoDia: moduleResumoDia,
        dashboard: moduleDashboard,
        users: moduleUsers,
        sysCfg: moduleSysCfg,
        distanceMatrix: moduleDistanceMatrix,
        empresa: moduleEmpresa,
        logPro: moduleLogPro,
        wisdom: moduleWisdom
    },
    strict: process.env.NODE_ENV !== 'production'
})
