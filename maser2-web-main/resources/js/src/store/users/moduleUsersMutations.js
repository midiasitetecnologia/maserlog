export default {
	SET_USERS(state, users) {
		state.usersData = users
	},
	REMOVE_RECORD(state, itemId) {
		const usersIndex = state.usersData.findIndex((u) => u.id == itemId)
		state.usersData.splice(usersIndex, 1)
	},
}
