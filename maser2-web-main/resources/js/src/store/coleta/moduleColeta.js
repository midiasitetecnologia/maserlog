
import state from './moduleColetaState.js'
import mutations from './moduleColetaMutations.js'
import actions from './moduleColetaActions.js'
import getters from './moduleColetaGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

