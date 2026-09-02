
import state from './moduleResumoDiaState.js'
import mutations from './moduleResumoDiaMutations.js'
import actions from './moduleResumoDiaActions.js'
import getters from './moduleResumoDiaGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

