
import state from './moduleColetaFixaBloqState.js'
import mutations from './moduleColetaFixaBloqMutations.js'
import actions from './moduleColetaFixaBloqActions.js'
import getters from './moduleColetaFixaBloqGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

