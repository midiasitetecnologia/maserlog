
import state from './moduleAtendimentoState.js'
import mutations from './moduleAtendimentoMutations.js'
import actions from './moduleAtendimentoActions.js'
import getters from './moduleAtendimentoGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

