<template>
  <div v-if="isAuthenticated" class="max-w-7xl mx-auto pt-4 px-6 p-8">
    <div class="mb-8">
      <h2 class="text-3xl md:text-4xl font-bold">Resultados</h2>
      <p class="mt-2 text-gray-500 text-sm">Realice un seguimiento del progreso de los participantes en la competencia Batalla de Prompts.</p>
    </div>

    <div class="flex items-center gap-2 mb-6">
      <button @click="activeView = 'all'"
        :class="activeView === 'all' ? 'px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow' : 'px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-100'">
        Todas las Rondas
      </button>
      <button v-for="round in [1, 2, 3, 4]" :key="round" @click="activeView = round"
        :class="activeView === round ? 'px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-full shadow' : 'px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-gray-100'">
        Ronda {{ round }}
      </button>
    </div>

    <div class="p-3 mb-6 text-sm text-center bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">
      <p>
        <strong>Nota sobre la Puntuación:</strong> La <strong>posición</strong> en cada ronda se determina por los Votos Ponderados (`# Votos × Ronda`). La <strong>Puntuación Total</strong> de la ronda es la suma de esos Votos Ponderados + los Puntos de Clasificación (10, 8, 6, o 4). En caso de empate, el orden de subida decide la posición.
      </p>
    </div>

    <div v-if="loading" class="text-center py-10"><p class="text-gray-500">Cargando resultados...</p></div>
    <div v-else-if="error" class="text-center py-10"><p class="text-red-500 font-semibold">{{ error }}</p></div>
    
    <div v-else>
      <div v-if="activeView === 'all'" class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
              <th class="px-6 py-3">Participante</th>
              <th class="px-6 py-3 text-center">Puntuación Ronda 1</th>
              <th class="px-6 py-3 text-center">Puntuación Ronda 2</th>
              <th class="px-6 py-3 text-center">Puntuación Ronda 3</th>
              <th class="px-6 py-3 text-center">Puntuación Ronda 4</th>
              <th class="px-6 py-3 text-center">Total (Clasificación)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="participant in displayedData" :key="participant.name" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium whitespace-nowrap">{{ participant.name }}</td>
              <td class="px-6 py-4 text-center">{{ participant.round1 }}</td>
              <td class="px-6 py-4 text-center">{{ participant.round2 }}</td>
              <td class="px-6 py-4 text-center">{{ participant.round3 }}</td>
              <td class="px-6 py-4 text-center">{{ participant.round4 }}</td>
              <td class="px-6 py-4 text-center font-bold text-blue-600">{{ participant.total }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
              <th class="px-6 py-3">Posición</th>
              <th class="px-6 py-3">Participante</th>
              <th class="px-6 py-3 text-center"># Votos</th>
              <th class="px-6 py-3 text-center">Puntos (Clasificación)</th>
              <th class="px-6 py-3 text-center">Puntuación Total (Ronda {{ activeView }})</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="(participant, index) in displayedData" :key="participant.name" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium">{{ index + 1 }}</td>
              <td class="px-6 py-4 font-medium whitespace-nowrap">{{ participant.name }}</td>
              <td class="px-6 py-4 text-center">{{ participant.votes }}</td>
              <td class="px-6 py-4 text-center">{{ participant.points }}</td>
              <td class="px-6 py-4 text-center font-bold text-blue-600">{{ participant.finalScore }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div v-else class="min-h-screen text-center py-20">
    <h2 class="text-3xl font-bold">Acceso Denegado</h2>
    <p class="mt-2 text-gray-600">Por favor, <router-link to="/login" class="text-blue-600 font-semibold hover:underline">inicia sesión</router-link> para ver los resultados.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { isAuthenticated } from '../store/auth.js';

const API_URL = import.meta.env.VITE_API_URL;
const allScores = ref([]);
const loading = ref(true);
const error = ref(null);
const activeView = ref('all');

const displayedData = computed(() => {
  if (!allScores.value) return [];

  // Vista de "Todas las Rondas"
  if (activeView.value === 'all') {
    const summaryData = allScores.value.map(p => ({
      name: p.name,
      // CAMBIO: Se añade `?.` y `|| 0` para evitar errores si los datos no existen
      round1: p.roundData[1]?.finalScore || 0,
      round2: p.roundData[2]?.finalScore || 0,
      round3: p.roundData[3]?.finalScore || 0,
      round4: p.roundData[4]?.finalScore || 0,
      total: p.total
    }));
    return summaryData.sort((a, b) => b.total - a.total);
  }
  
  // Vista de cada ronda
  const round = activeView.value;
  return allScores.value
    .map(p => ({
      name: p.name,
      // CAMBIO: Se añade `?.` y `|| 0` para evitar errores
      votes: p.roundData[round]?.votes || 0,
      points: p.roundData[round]?.points || 0,
      finalScore: p.roundData[round]?.finalScore || 0,
      rankingScore: (p.roundData[round]?.votes || 0) * round
    }))
    .sort((a, b) => b.rankingScore - a.rankingScore); 
});

async function fetchDashboardData() {
  loading.value = true;
  error.value = null;
  try {
    // Esta ruta es pública en Laravel, no necesita token
    const response = await fetch(`${API_URL}/api/dashboard`);
    if (!response.ok) throw new Error('No se pudo conectar con el servidor.');
    
    const data = await response.json();
    allScores.value = data.scores;

  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  // Esta vista está protegida por v-if="isAuthenticated",
  // así que podemos asumir que el usuario está logueado si onMounted se ejecuta.
  fetchDashboardData();
});
</script>