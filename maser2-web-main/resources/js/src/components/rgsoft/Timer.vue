<template>
	<span>{{timeFromNow}}</span>
</template>

<script>
export default {
	name: "timer",
	props: {
		created: ""
	},
	data() {
		return {
			timeFromNow: ""
		};
	},
	created() {
		this.getTimeFromNow();
		//Avaliar bem antes de alternar esse tempo, todos os lugares que estão sendo usado.
		setInterval(this.getTimeFromNow, 1000);
	},
	destroyed() {
		clearInterval(this.getTimeFromNow);
	},
	methods: {
		getTimeFromNow() {
			if (this.created != null && this.created !== undefined) {
				var moment = require("moment");
				this.timeFromNow = moment(this.created).fromNow(true);
			} else {
				this.timeFromNow = "";
			}			
		}
	}
};
</script>

<style lang="scss">
</style>