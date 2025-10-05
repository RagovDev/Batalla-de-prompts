<template>
  <div v-if="isAuthenticated" class="p-8">

    <div class="px-6 py-3">
      <h2 class="text-3xl md:text-4xl font-bold">Votación de Temas</h2>
      <p class="mt-2 text-gray-600">Elige el tema que más te inspire para la próxima ronda. Solo puedes votar una
        vez.</p>
    </div>

    <div class="max-w-7xl mx-auto flex flex-col h-full px-6 pt-4">

      <div class="flex items-center gap-2 mb-8">
        <button v-for="round in [1, 2, 3, 4]" :key="round" @click="selectRound(round)"
          :class="selectedRound === round
            ? 'px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow'
            : 'px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-100'">
          Ronda {{ round }}
        </button>
      </div>

      <div class="flex-grow overflow-y-auto pb-8">
        <div v-if="loading" class="text-center py-10 text-gray-500">Cargando temas...</div>
        <div v-else-if="error" class="text-center py-10 text-red-500">{{ error }}</div>
        <div v-else-if="themes.length === 0" class="text-center py-10 text-gray-500">No hay temas propuestos para esta
          ronda.</div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="theme in themes" :key="theme.id"
            class="flex flex-col bg-white rounded-2xl shadow transition overflow-hidden">
            <div class="aspect-video w-full">
              <img :src="theme.imageUrl" :alt="theme.title" class="w-full h-full object-cover">
            </div>
            <div class="p-6 flex flex-col flex-grow">
              <h3 class="text-lg font-bold text-gray-900 mb-2">{{ theme.title }}</h3>
              <div class="flex items-center gap-1 text-sm text-gray-500 mb-4">
                <span>{{ theme.votes }} Votos</span>
              </div>
              <button @click="handleVote(theme)" :disabled="haVotado"
                class="w-full mt-auto flex items-center justify-center gap-2 rounded-lg h-11 font-semibold transition-colors"
                :class="haVotado
                  ? (userVotedFor === theme.id ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400 cursor-not-allowed')
                  : 'bg-blue-600 text-white hover:bg-blue-700'">
                <span v-if="userVotedFor === theme.id">¡Has Votado!</span>
                <span v-else-if="haVotado">Votación Cerrada</span>
                <span v-else>Votar por este tema</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div v-else class="min-h-screen text-center py-20">
    <h2 class="text-3xl font-bold">Acceso Denegado</h2>
    <p class="mt-2 text-gray-600">Por favor, <router-link to="/login"
        class="text-blue-600 font-semibold hover:underline">inicia sesión</router-link> para votar por los temas.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { getToken, isAuthenticated } from '../store/auth.js';
import { toast } from 'vue3-toastify';

const API_URL = 'http://localhost:3000';

const themes = ref([]);
const selectedRound = ref(1);
const haVotado = ref(false);
const userVotedFor = ref(null);
const loading = ref(true);
const error = ref(null);

async function fetchThemes(round) {
  loading.value = true;
  error.value = null;
  const token = getToken();

  try {
    const response = await fetch(`${API_URL}/api/temas/${round}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (!response.ok) throw new Error('Error al cargar los temas.');

    const data = await response.json();
    themes.value = data.temas;
    haVotado.value = data.haVotado;
    // CAMBIO: Ahora guardamos el ID del tema votado que nos envía el backend
    userVotedFor.value = data.userVotedFor; 

  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

async function handleVote(theme) {
  if (haVotado.value) return;

  const token = getToken();
  try {
    const response = await fetch(`${API_URL}/api/temas/votar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ themeId: theme.id })
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.message);

    theme.votes++;
    haVotado.value = true;
    userVotedFor.value = theme.id;
    toast.success('¡Gracias por tu voto!');

  } catch (err) {
    toast.error(err.message);
  }
}

function selectRound(round) {
  selectedRound.value = round;
}

watch(selectedRound, (newRound) => {
  if (isAuthenticated.value) {
    fetchThemes(newRound);
  }
});

onMounted(() => {
  if (isAuthenticated.value) {
    fetchThemes(1);
  }
});
</script>