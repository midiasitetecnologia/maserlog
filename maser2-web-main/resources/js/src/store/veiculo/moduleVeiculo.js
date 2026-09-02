
import state from './moduleVeiculoState.js'
import mutations from './moduleVeiculoMutations.js'
import actions from './moduleVeiculoActions.js'
import getters from './moduleVeiculoGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

