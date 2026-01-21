<template>
  <div v-if="isAuthenticated" class="max-w-7xl mx-auto pt-4 px-6 p-8">

    <div class="mb-8">
      <h2 class="text-3xl md:text-4xl font-bold">Votación de Temas</h2>
      <p class="mt-2 text-gray-600">Elige el tema que más te inspire para la próxima ronda. Solo puedes votar una
        vez.</p>
    </div>

    <div class="h-full">
      <div class="flex items-center gap-2 mb-8">
        <button v-for="round in [1, 2, 3, 4]" :key="round" @click="selectRound(round)"
          :class="selectedRound === round
            ? 'px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow'
            : 'px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-100'">
          Ronda {{ round }}
        </button>
      </div>

      <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg grid grid-cols-1 md:grid-cols-2 gap-4 text-center text-sm shadow">
        <div>
          <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</span>
          <span class="block text-xl font-bold text-blue-600 mt-1">{{ stats.totalParticipants }}</span>
        </div>
        <div>
          <span class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Votos Tema (Ronda {{ selectedRound }})</span>
          <span class="block text-xl font-bold text-blue-600 mt-1">{{ roundThemeVotes }}</span>
        </div>
      </div>

      <div class="pb-8">
        <div v-if="loading" class="text-center py-10 text-gray-500">Cargando temas...</div>
        <div v-else-if="error" class="text-center py-10 text-red-500">{{ error }}</div>
        <div v-else-if="themes.length === 0" class="text-center py-10 text-gray-500">No hay temas propuestos para esta ronda.</div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="theme in themes" :key="theme.id"
            class="flex flex-col bg-white rounded-2xl shadow transition overflow-hidden"
            :class="{ 'ring-2 ring-green-500 ring-offset-2': userVotedFor === theme.id }">
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
// CAMBIO: Importamos 'currentUser' para el watcher
import { getToken, isAuthenticated, currentUser } from '../store/auth.js';
import { toast } from 'vue3-toastify';

const API_URL = import.meta.env.VITE_API_URL;

const themes = ref([]);
const selectedRound = ref(1);
const haVotado = ref(false);
const userVotedFor = ref(null);
const loading = ref(true);
const error = ref(null);
// CAMBIO: El estado de stats ahora coincide con lo que se usa en el template
const stats = ref({ totalParticipants: 0 }); 
const roundThemeVotes = ref(0);

// --- FUNCIONES ---

async function fetchRoundThemeVotes(round) {
  try {
    const response = await fetch(`${API_URL}/api/stats/temas/${round}`);
    if (!response.ok) throw new Error('Error al cargar votos de temas de la ronda.');
    const data = await response.json();
    roundThemeVotes.value = data.totalVotesInRound;
  } catch (err) {
    console.error(err);
    roundThemeVotes.value = 0;
  }
}

async function fetchThemes(round) {
  loading.value = true;
  error.value = null;
  userVotedFor.value = null; 
  const token = getToken();

  try {
    const response = await fetch(`${API_URL}/api/temas/${round}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (!response.ok) throw new Error('Error al cargar los temas.');

    const data = await response.json();
    
    // CAMBIO: Se construye la URL completa para cada imagen
    themes.value = data.temas.map(theme => ({
      ...theme,
      imageUrl: `${API_URL}${theme.imageUrl}` // Ej: http://127.0.0.1:8000 + /storage/themes/archivo.jpg
    }));

    haVotado.value = data.haVotado;
    userVotedFor.value = data.userVotedFor;

    await fetchRoundThemeVotes(round);

  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

async function fetchGlobalStats() {
  try {
    const response = await fetch(`${API_URL}/api/stats`);
    if (!response.ok) throw new Error('No se pudieron cargar las estadísticas globales.');
    const data = await response.json();
    // CAMBIO: Solo guardamos los stats que este componente usa
    stats.value = {
      totalParticipants: data.totalParticipants
    };
  } catch (err) {
    console.error(err);
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
    await fetchRoundThemeVotes(selectedRound.value);

  } catch (err) {
    toast.error(err.message);
  }
}

async function selectRound(round) {
  if (selectedRound.value === round || loading.value) return;
  selectedRound.value = round;
  
  if (isAuthenticated.value) {
    // No es necesario 'loading.value = true' aquí
    // porque 'fetchThemes' ya lo maneja
    await fetchThemes(round); 
  }
}

// CAMBIO: Se añade el watcher para isAuthenticated
watch(isAuthenticated, (isAuthNow, wasAuthBefore) => {
  // Si el usuario acaba de iniciar sesión (o ya estaba logueado al cargar)
  if (isAuthNow) {
    fetchGlobalStats();
    fetchThemes(selectedRound.value); // Carga los temas de la ronda actual
  } 
  // Si el usuario acaba de cerrar sesión
  else if (!isAuthNow && wasAuthBefore) {
    themes.value = [];
    userVotedFor.value = null;
    haVotado.value = false;
    roundThemeVotes.value = 0;
    stats.value = { totalParticipants: 0 };
    error.value = null;
    loading.value = false;
    selectedRound.value = 1;
  }
}, { immediate: true }); // 'immediate: true' ejecuta esto al cargar

// CAMBIO: onMounted se simplifica porque el watcher 'immediate' hace el trabajo
onMounted(() => {
  if (!isAuthenticated.value) {
    loading.value = false; // Asegura que 'loading' sea falso si no hay login
  }
});
</script>