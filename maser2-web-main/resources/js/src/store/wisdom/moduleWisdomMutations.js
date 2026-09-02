export default {
	SET_WISDOM(state, wisdom) {
		state.wisdomData = wisdom
	},
	REMOVE_RECORD(state, itemId) {
		const wisdomIndex = state.wisdomData.findIndex((u) => u.id == itemId)
		state.wisdomData.splice(wisdomIndex, 1)
	},
}
