
import state from './moduleLogProState.js'
import mutations from './moduleLogProMutations.js'
import actions from './moduleLogProActions.js'
import getters from './moduleLogProGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

