<template>
  <div id="logo">
    <a href="/">
      <img src="./assets/logo.jpg" class="logo" alt="Agent Deployment System"/>
    </a>
  </div>
  <Search @changeInput="changeLocation" @clickEvent=""/>
  <Agent :passData="data" v-if="data.data"/>
</template>

<script>
import Search from './components/Search.vue';
import Agent from './components/Agent.vue';

import { Loader } from '@googlemaps/js-api-loader';
import axios from 'axios'

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

            axios
                .get('api.php?action=get_near&lat='+this.coordinates.lat+'&lon='+this.coordinates.lng)
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
    changeLocation(event){
      this.address = event;
    },
  },
  components: {
    Search, Agent,
  }
};
</script>