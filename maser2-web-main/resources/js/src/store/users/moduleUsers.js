
import state from './moduleUsersState.js'
import mutations from './moduleUsersMutations.js'
import actions from './moduleUsersActions.js'
import getters from './moduleUsersGetters.js'

export default {  
  namespaced: true,
  state: state,
  mutations: mutations,
  actions: actions,
  getters: getters
}

