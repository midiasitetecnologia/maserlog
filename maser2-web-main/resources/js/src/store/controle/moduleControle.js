
import state from './moduleControleState.js'
import mutations from './moduleControleMutations.js'
import actions from './moduleControleActions.js'
import getters from './moduleControleGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

