<template>
  <header class="bg-white shadow-sm">
    <div class="container mx-auto px-6 py-3 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <path d="M44 4H30.6666V17.3334H17.3334V30.6666H4V44H44V4Z" fill="currentColor"></path>
        </svg>
        <h1 class="text-xl font-bold text-indigo-600">IAC</h1>
        <RouterLink to="/" class="text-xl font-bold text-gray-800">Batalla de Prompts</RouterLink>
      </div>

      <nav class="flex items-center gap-6">

        <div v-if="isAuthenticated" class="flex items-center gap-6">          
          <RouterLink to="/temas" class="text-gray-700 font-medium hover:text-indigo-600">
            Temas
          </RouterLink>
          <RouterLink to="/participar" class="text-gray-700 font-medium hover:text-indigo-600">
            Participar
          </RouterLink>
          <RouterLink to="/votaciones" class="text-gray-700 font-medium hover:text-indigo-600">
            Votaciones
          </RouterLink>
          <RouterLink to="/resultados" class="text-gray-700 font-medium hover:text-indigo-600">
            Resultados
          </RouterLink>

          <div class="h-6 w-px bg-gray-200"></div>
          
          <div class="flex items-center gap-4">
            <span class="text-gray-700">Hola, <strong>{{ currentUser }}</strong></span>
            <button @click="handleLogout" class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-full shadow hover:bg-red-700">
              Cerrar Sesión
            </button>
          </div>
        </div>

        <div v-else>
  <RouterLink 
    to="/login" 
    class="login-button px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow hover:bg-blue-700"
  >
    Iniciar Sesión
  </RouterLink>
</div>

      </nav>
    </div>
  </header>
</template>

<script setup>
import { RouterLink, useRouter } from "vue-router";
import { isAuthenticated, currentUser, logout } from '../store/auth.js';

const router = useRouter();

function handleLogout() {
  router.push('/login');
  logout();
}
</script>

<style scoped>
/* Resalta el link activo */
.router-link-exact-active:not(.login-button) {
  font-weight: bold;
  color: #4f46e5;
}
</style>