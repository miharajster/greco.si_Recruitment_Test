<template>
  <div id="search">
    <input type="text" id="address" placeholder="Enter desired location..." @change="$emit('changeInput', $event.target.value)" v-on:keyup.enter="clickGo">
    <span class="go" @click="clickGo">Search</span>
  </div>
</template>
<script>
export default {
  name: 'Search',
  methods: {
    clickGo(){
      this.$parent.translateAddress();
    }
  },
}
</script>