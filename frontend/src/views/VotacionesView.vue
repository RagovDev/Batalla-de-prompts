<template>
  <div class="max-w-7xl mx-auto px-6 flex flex-col h-full">
    <div v-if="isAuthenticated">
      <div class="pt-8 mb-8">
        <h2 class="text-3xl md:text-4xl font-bold">Votaciones</h2>
        <p class="mt-2 text-gray-600">Vota por tu favorito. Solo puedes votar una vez por ronda.</p>
      </div>

      <div
        class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg grid grid-cols-1 md:grid-cols-3 gap-4 text-center text-sm shadow">
        <div>
          <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</span>
          <span class="block text-xl font-bold text-blue-600 mt-1">{{ stats.totalParticipants }}</span>
        </div>
        <div>
          <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Votos Imágenes (Ronda {{
            selectedRound }})</span>
          <span class="block text-xl font-bold text-blue-600 mt-1">{{ roundImageVotes }}</span>
        </div>
        <div>
          <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Votos Totales (Temas)</span>
          <span class="block text-xl font-bold text-blue-600 mt-1">{{ stats.totalThemeVotes }}</span>
        </div>
      </div>

      <div class="flex items-center gap-2 mb-6">
        <button v-for="round in [1, 2, 3, 4]" :key="round" @click="selectRound(round)"
          :class="selectedRound === round
            ? 'px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow'
            : 'px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-100'">
          Ronda {{ round }}
        </button>
      </div>

      <div class="flex-grow overflow-y-auto pb-8">
        <div v-if="loading" class="text-center py-10 text-gray-500">Cargando imágenes...</div>
        <div v-else-if="error" class="text-center py-10 text-red-500">{{ error }}</div>
        <div v-else-if="images.length === 0" class="text-center py-10 text-gray-500">No hay imágenes en esta ronda.
        </div>

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

    <div v-else class="min-h-screen text-center flex items-center justify-center">
      <div>
        <h2 class="text-3xl font-bold">Acceso Denegado</h2>
        <p class="mt-2 text-gray-600">Por favor, <router-link to="/login"
            class="text-blue-600 font-semibold hover:underline">inicia sesión</router-link> para realizar la votación.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { getToken, isAuthenticated, currentUser } from '../store/auth.js';
import { toast } from 'vue3-toastify';

const API_URL = import.meta.env.VITE_API_URL;
const selectedRound = ref(1);
const images = ref([]);
const userVotes = ref({}); // Historial de votos del usuario actual
const loading = ref(true); // Empieza en true para la carga inicial
const error = ref(null);
const stats = ref({ totalParticipants: 0, totalThemeVotes: 0 }); // Stats globales
const roundImageVotes = ref(0); // Votos de imágenes de la ronda actual

// --- FUNCIONES ASÍNCRONAS ---

async function fetchRoundImageVotes(round) {
  try {
    const response = await fetch(`${API_URL}/api/stats/images/${round}`);
    if (!response.ok) throw new Error('Error al cargar votos de imágenes de la ronda.');
    const data = await response.json();
    roundImageVotes.value = data.totalVotesInRound;
  } catch (err) {
    console.error(`Error obteniendo votos de imágenes ronda ${round}:`, err);
    roundImageVotes.value = 0;
  }
}

async function fetchImages(round) {
  images.value = [];
  error.value = null;
  try {
    const response = await fetch(`${API_URL}/api/rondas/${round}/images`);
    if (!response.ok) throw new Error('No se pudieron cargar las imágenes de la ronda.');
    let fetchedImages = await response.json();

    // CORRECCIÓN: Se asegura de construir la URL completa
    images.value = fetchedImages.map(img => ({
      ...img,
      url: `${API_URL}${img.url}` // Ej: http://127.0.0.1:8000 + /storage/uploads/img.jpg
    }));

    await fetchRoundImageVotes(round); // Carga los votos de esta ronda
  } catch (err) {
    error.value = err.message;
    console.error(`Error obteniendo imágenes ronda ${round}:`, err);
  }
}

// Función REFINADA para cargar TODOS los datos necesarios del usuario
async function loadUserDataAndRoundData() {
  console.log("loadUserDataAndRoundData: Iniciando carga de datos...");
  loading.value = true;
  error.value = null;
  userVotes.value = {}; // Limpia SIEMPRE los votos previos antes de cargar nuevos
  const token = getToken();

  if (!isAuthenticated.value || !token) {
    console.log("loadUserDataAndRoundData: No autenticado, deteniendo.");
    images.value = []; // Limpia imágenes si no está logueado
    roundImageVotes.value = 0;
    loading.value = false;
    return;
  }

  try {
    console.log("loadUserDataAndRoundData: Obteniendo historial de votos del usuario...");
    const votesResponse = await fetch(`${API_URL}/api/me/votes`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (!votesResponse.ok) throw new Error('No se pudo cargar tu historial de votos.');
    const votesHistory = await votesResponse.json();
    userVotes.value = votesHistory;
    console.log("loadUserDataAndRoundData: Historial cargado:", votesHistory);

    console.log(`loadUserDataAndRoundData: Obteniendo imágenes para ronda ${selectedRound.value}...`);
    await fetchImages(selectedRound.value);

    console.log("loadUserDataAndRoundData: Obteniendo estadísticas globales...");
    await fetchGlobalStats();

  } catch (err) {
    error.value = err.message;
    console.error("loadUserDataAndRoundData Error:", err);
  } finally {
    loading.value = false;
    console.log("loadUserDataAndRoundData: Carga de datos finalizada.");
  }
}


async function fetchGlobalStats() {
  try {
    const response = await fetch(`${API_URL}/api/stats`);
    if (!response.ok) throw new Error('No se pudieron cargar las estadísticas globales.');
    const data = await response.json();
    stats.value = {
      totalParticipants: data.totalParticipants,
      totalThemeVotes: data.totalThemeVotes
    };
  } catch (err) {
    console.error("fetchGlobalStats Error:", err);
    stats.value = { totalParticipants: 0, totalThemeVotes: 0 };
  }
}

async function handleVote(image) {
  const token = getToken();
  const voterName = currentUser.value;

  if (isVotingDisabled(image)) {
    toast.warn("No puedes votar por esta imagen.");
    return;
  }

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
    userVotes.value = { ...userVotes.value, [selectedRound.value]: image.id };
    toast.success('¡Voto registrado con éxito!');
    await fetchRoundImageVotes(selectedRound.value);
  } catch (err) {
    toast.error(err.message);
  }
}

// --- FUNCIONES SÍNCRONAS Y WATCHERS ---

async function selectRound(round) {
  if (selectedRound.value === round || loading.value) return;
  selectedRound.value = round;
  loading.value = true;
  await fetchImages(round);
  loading.value = false;
}

function isVotingDisabled(image) {
  if (!currentUser.value) return true;
  if (image.name === currentUser.value) return true;
  if (userVotes.value && userVotes.value[selectedRound.value]) return true;
  return false;
}

function getButtonClass(image) {
  if (!currentUser.value) return 'bg-gray-100 text-gray-400 cursor-not-allowed';
  if (userVotes.value && userVotes.value[selectedRound.value] === image.id) {
    return 'bg-green-100 text-green-600 cursor-not-allowed';
  }
  if (image.name === currentUser.value) {
    return 'bg-gray-200 text-gray-500 cursor-not-allowed';
  }
  if (userVotes.value && userVotes.value[selectedRound.value]) {
    return 'bg-gray-100 text-gray-400 cursor-not-allowed';
  }
  return 'bg-blue-600 text-white hover:bg-blue-700';
}

function getButtonText(image) {
  if (!currentUser.value) return 'Votar';
  if (userVotes.value && userVotes.value[selectedRound.value] === image.id) return 'Votado';
  if (image.name === currentUser.value) return 'Tu Imagen';
  return 'Votar';
}

watch(isAuthenticated, (isAuthNow, wasAuthBefore) => {
  console.log("Watcher isAuthenticated cambió:", { isAuthNow, wasAuthBefore });
  if (isAuthNow) {
    console.log("Usuario autenticado detectado. Llamando a loadUserDataAndRoundData()...");
    loadUserDataAndRoundData();
  }
  else if (!isAuthNow && wasAuthBefore) {
    console.log("Usuario cerró sesión. Limpiando datos...");
    images.value = [];
    userVotes.value = {};
    roundImageVotes.value = 0;
    error.value = null;
    loading.value = false;
    selectedRound.value = 1;
    stats.value = { totalParticipants: 0, totalThemeVotes: 0 };
  }
}, { immediate: true });

onMounted(() => {
  console.log("Componente Votaciones Montado. Estado inicial Auth:", isAuthenticated.value);
  fetchGlobalStats();
  if (!isAuthenticated.value) {
    loading.value = false;
  }
});
</script>