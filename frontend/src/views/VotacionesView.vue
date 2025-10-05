<template>
  <div v-if="isAuthenticated" class="p-8">

    <div class="px-6 py-3">
      <h2 class="text-3xl md:text-4xl font-bold">Votaciones</h2>
      <p class="mt-2 text-gray-600">Vota por tu favorito. Solo puedes votar una vez por ronda.</p>
    </div>

    <div class="max-w-7xl mx-auto px-6 pt-4">
      <div class="flex items-center gap-2 mb-6">
        <button v-for="round in [1, 2, 3, 4]" :key="round" @click="selectRound(round)"
          :class="selectedRound === round
            ? 'px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow'
            : 'px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-100'">
          Ronda {{ round }}
        </button>
      </div>

      <div v-if="loading" class="text-center py-10 text-gray-500">Cargando imágenes...</div>
      <div v-else-if="error" class="text-center py-10 text-red-500">{{ error }}</div>
      <div v-else-if="images.length === 0" class="text-center py-10 text-gray-500">No hay imágenes en esta ronda.</div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <div v-for="image in images" :key="image.id"
          class="flex flex-col bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
          <div class="w-full aspect-[4/5]">
            <img :src="image.url" :alt="image.name" class="w-full h-full object-cover" />
          </div>

          <div class="p-4 flex flex-col flex-grow">
            <h3 class="text-base font-semibold text-gray-900 mb-2">{{ image.name }}</h3>

            <div class="flex items-center gap-1 text-sm text-gray-500 mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M14 9l-3 3m0 0l-3-3m3 3V4m0 8a9 9 0 11-9 9h18a9 9 0 01-9-9z" />
              </svg>
              <span>{{ image.votes }} Votos</span>
            </div>

            <button
              class="w-full mt-auto flex items-center justify-center gap-2 rounded-lg h-11 font-semibold transition-colors"
              :class="getButtonClass(image)" :disabled="isVotingDisabled(image)" @click="handleVote(image)">
              <svg v-if="userVotes[selectedRound] === image.id" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8.5 8.5a1 1 0 01-1.414 0l-4-4A1 1 0 014.207 9.793L7.5 13.086l7.793-7.793a1 1 0 011.414 0z"
                  clip-rule="evenodd" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                  d="M2 10a8 8 0 1116 0A8 8 0 012 10zm9-3a1 1 0 10-2 0v2.586L7.293 8.293a1 1 0 10-1.414 1.414L10 13.414l4.121-4.121a1 1 0 00-1.414-1.414L11 9.586V7z" />
              </svg>
              <span>{{ getButtonText(image) }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="min-h-screen text-center py-20">
    <h2 class="text-3xl font-bold">Acceso Denegado</h2>
    <p class="mt-2 text-gray-600">Por favor, <router-link to="/login"
        class="text-blue-600 font-semibold hover:underline">inicia sesión</router-link> para realizar la votación.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getToken, isAuthenticated, currentUser } from '../store/auth.js';
import { toast } from 'vue3-toastify';

const API_URL = 'http://localhost:3000';
const selectedRound = ref(1);
const images = ref([]);
const userVotes = ref({}); // Objeto para rastrear los votos del usuario. Ej: { 1: 'imageId123', 2: 'imageId456' }
const loading = ref(true);
const error = ref(null);

// CAMBIO PRINCIPAL: Separamos la carga de datos inicial
async function fetchInitialData() {
  loading.value = true;
  error.value = null;
  const token = getToken();

  try {
    // 1. Primero, obtenemos el historial de votos del usuario
    const votesResponse = await fetch(`${API_URL}/api/me/votes`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (!votesResponse.ok) throw new Error('No se pudo cargar tu historial de votos.');
    const votesHistory = await votesResponse.json();
    userVotes.value = votesHistory; // Rellenamos nuestro estado con los votos históricos

    // 2. Después, cargamos las imágenes de la primera ronda
    await fetchImages(1);

  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

async function fetchImages(round) {
  loading.value = true;
  images.value = [];
  try {
    const response = await fetch(`${API_URL}/api/rondas/${round}/images`);
    if (!response.ok) throw new Error('No se pudieron cargar las imágenes de la ronda.');
    let fetchedImages = await response.json();
    images.value = fetchedImages.map(img => ({ ...img, url: `${API_URL}${img.url}` }));
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

function selectRound(round) {
  selectedRound.value = round;
  fetchImages(round);
}

async function handleVote(image) {
  const token = getToken();
  const voterName = currentUser.value;
  try {
    const response = await fetch(`${API_URL}/api/vote`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ imageId: image.id, voterName: voterName })
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.message || 'Error al procesar el voto.');
    }
    image.votes++;
    userVotes.value[selectedRound.value] = image.id;
    toast.success('¡Voto registrado con éxito!');
  } catch (err) {
    toast.error(err.message);
  }
}

function isVotingDisabled(image) {
  if (image.name === currentUser.value) return true;
  if (userVotes.value[selectedRound.value]) return true;
  return false;
}

function getButtonClass(image) {
  // AHORA ESTA LÓGICA FUNCIONARÁ CORRECTAMENTE AL CARGAR LA PÁGINA
  if (userVotes.value[selectedRound.value] === image.id) {
    return 'bg-green-100 text-green-600 cursor-not-allowed'; // Votado
  }
  if (image.name === currentUser.value) {
    return 'bg-gray-200 text-gray-500 cursor-not-allowed'; // Tu propia imagen
  }
  if (userVotes.value[selectedRound.value]) {
    return 'bg-gray-100 text-gray-400 cursor-not-allowed'; // Ya se votó en la ronda
  }
  return 'bg-blue-600 text-white hover:bg-blue-700'; // Habilitado para votar
}

function getButtonText(image) {
  // Y ESTA TAMBIÉN
  if (userVotes.value[selectedRound.value] === image.id) return 'Votado';
  if (image.name === currentUser.value) return 'Tu Imagen';
  return 'Votar';
}

onMounted(() => {
  if (isAuthenticated.value) {
    fetchInitialData(); // Llamamos a la nueva función
  }
});
</script>