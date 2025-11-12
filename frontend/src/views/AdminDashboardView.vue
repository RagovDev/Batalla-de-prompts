<template>
  <div class="max-w-7xl mx-auto pt-4 px-6 p-8">
    <div class="mb-8">
      <h2 class="text-3xl md:text-4xl font-bold">Panel de Administración</h2>
      <p class="mt-2 text-gray-600">Bienvenido, {{ currentUser }}. Aquí podrás gestionar el concurso.</p>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-md">
      <h3 class="text-xl font-semibold mb-6">Próximamente...</h3>
      <p>Aquí podrás editar los temas de las rondas y ver estadísticas detalladas.</p>
    </div>
  </div>
</template>

<script setup>
import { currentUser } from '../store/auth.js';
</script>