<template>
  <div id="logo">
      <img src="./assets/logo.jpg" class="logo" alt="Agent Deployment System" />
  </div>
  <div id="search">
    <input type="text" id="address" placeholder="Enter desired location..." v-model="address">
    <span class="go" @click="translateAddress">Search</span>
  </div>
  <div id="output">
    <p>Data: {{ data }}</p>
  </div>
</template>

<script>
import { Loader } from '@googlemaps/js-api-loader';
import axios from 'axios'
import VueAxios from 'vue-axios'

export default {
  data() {
    return {
      address: "",
      coordinates: null,
      error: null,
      data: {},
    };
  },
  methods: {
    async translateAddress() {
      try {
        const apiKey = "AIzaSyDph61L7EUMiOR56LovtehIm8CBp2XM1rg";
        const loader = new Loader({ apiKey });
        await loader.load();

        const geocoder = new google.maps.Geocoder();

        geocoder.geocode({ address: this.address }, (results, status) => {
          if (status === "OK" && results.length > 0) {
            this.coordinates = results[0].geometry.location.toJSON();
            this.error = null;

            console.log(this.coordinates.lat)
            console.log(this.coordinates.lng)

            axios
                .get('api.php?action=get_near&lat='+this.coordinates.lat+'&lon='+this.coordinates.lng)
                // .get('http://localhost:80/index.php?action=get_near&lat='+this.coordinates.lat+'&lon='+this.coordinates.lng)
                .then((response) => {
                  this.data = response
                })
          } else {
            this.coordinates = null;
            this.error = "Could not translate the address to coordinates.";
          }
        });
      } catch (error) {
        this.coordinates = null;
        this.error = "An error occurred while fetching data from Google Maps API.";
        console.error(error);
      }
    },
  },
};
</script>