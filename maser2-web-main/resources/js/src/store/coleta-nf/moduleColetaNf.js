
import state from './moduleColetaNfState.js'
import mutations from './moduleColetaNfMutations.js'
import actions from './moduleColetaNfActions.js'
import getters from './moduleColetaNfGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

