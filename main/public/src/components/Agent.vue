<template>
  <div id="output">
    <div class="agent" v-for="agent in passData.data.result.slice(0, 5)">
      <div class="agent_avatar" :style="{ backgroundImage: 'url(https://loremflickr.com/280/280?lock='+agent.id+')' }">
        <span>#{{ agent.id }}</span>
      </div>
      <div class="agent_info">
        <div class="agent_info--name">{{ agent.first_name }} {{ agent.last_name }}</div>
        <div class="agent_info--location">Lat: {{ agent.latitude }}<br/>Lng: {{ agent.longitude }}</div>
        <div class="agent_info--distance">Distance: {{ agent.distance.toFixed(2) }} km</div>
        <div class="agent_info--deploy">
          <span class="btn">Deploy</span>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'Agent',
  props: ['passData']
}
</script>