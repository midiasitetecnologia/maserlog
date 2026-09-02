
import state from './moduleTipoVeiculoState.js'
import mutations from './moduleTipoVeiculoMutations.js'
import actions from './moduleTipoVeiculoActions.js'
import getters from './moduleTipoVeiculoGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

