
import state from './moduleColetaFixaState.js'
import mutations from './moduleColetaFixaMutations.js'
import actions from './moduleColetaFixaActions.js'
import getters from './moduleColetaFixaGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

