<template>
  <div id="app" class="bg-gray-100 flex flex-col min-h-screen">
    <Navbar />
    <main class="flex-grow">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue';
</script>