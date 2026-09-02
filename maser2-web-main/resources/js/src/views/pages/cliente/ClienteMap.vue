<template>
	<div>
		<vx-card>
			<div class="mt-5">
				<gmap-map :center="center" :zoom="15" style="width: 100%; height: 500px">
                    <gmap-info-window
						:options="infoOptions"
						:position="infoWindowPos"
						:opened="infoWinOpen"
						@closeclick="infoWinOpen=false"
					>{{infoContent}}</gmap-info-window>
					<gmap-info-window
                        :key="i"
						v-for="(m,i) in markers"
						:options="infoOptions"
						:position="m.position"
						:opened="true"						
					>{{m.infoText}}</gmap-info-window>
					<gmap-marker
						:key="i"
						v-for="(m,i) in markers"
						:position="m.position"
						:clickable="true"
						@click="toggleInfoWindow(m,i)"
					></gmap-marker>
				</gmap-map>
			</div>
		</vx-card>
	</div>
</template>

<script>

export default {
    data() {
        return {
            center: { lat: parseFloat(this.$route.params.lat), lng: parseFloat(this.$route.params.lng) },
            infoContent: '',
            infoWindowPos: null,
            infoWinOpen: false,
            currentMidx: null,
            //optional: offset infowindow so it visually sits nicely on top of our marker
            infoOptions: {
            pixelOffset: { width: 0, height: -35 }
            },
            markers: [
                { position: { lat: parseFloat(this.$route.params.lat), lng: parseFloat(this.$route.params.lng) }, infoText: decodeURIComponent(this.$route.params.nome) },
            ]
        }
    },
    methods: {
        toggleInfoWindow: function(marker, idx) {
            this.infoWindowPos = marker.position;
            this.infoContent = marker.infoText;
            //check if its the same marker that was selected if yes toggle
            if (this.currentMidx == idx) {
                this.infoWinOpen = !this.infoWinOpen;
            }
            //if different marker set infowindow to open and reset current marker index
            else {
                this.infoWinOpen = true;
                this.currentMidx = idx;
            }
        }
    }
};

</script>
