
import state from './moduleClienteState.js'
import mutations from './moduleClienteMutations.js'
import actions from './moduleClienteActions.js'
import getters from './moduleClienteGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

