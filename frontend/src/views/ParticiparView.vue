<template>
  <div v-if="isAuthenticated" class="max-w-7xl mx-auto pt-4 px-6 p-8">
    <div class="mb-8">
      <h2 class="text-3xl md:text-4xl font-bold">Participar</h2>
      <p class="mt-2 text-gray-600">Sube tu imagen para cada ronda. Solo puedes subir una imagen por ronda.</p>
    </div>

    <main class="grid grid-cols-1 lg:grid-cols-3 gap-12">
      <div class="bg-white p-8 rounded-2xl shadow-md">
        <h2 class="text-xl font-semibold mb-6">Envía tu imagen</h2>

        <div v-if="currentUserImage" class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-lg mb-6">
          <p class="font-bold">¡Ya subiste tu imagen para la Ronda {{ selectedRound }}!</p>
          <p class="text-sm">Puedes borrarla desde la galería si quieres reemplazarla.</p>
        </div>

        <div class="mb-6">
          <label class="block mb-3 text-sm font-medium text-gray-700">Selecciona la ronda</label>
          <div class="flex border-b border-gray-200">
            <button
              type="button"
              v-for="round in [1,2,3,4]"
              :key="round"
              @click="selectedRound = round"
              :class="[
                'px-4 py-2 -mb-px text-sm font-medium focus:outline-none',
                selectedRound === round ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Ronda {{ round }}
            </button>
          </div>
        </div>

        <fieldset :disabled="!!currentUserImage">
          <form class="space-y-6" @submit.prevent="publishImage">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700">Nombre del participante</label>
              <input
                :value="participantName"
                type="text"
                readonly
                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 cursor-not-allowed"
              />
            </div>
            <div class="space-y-3">
              <label class="block text-sm font-medium text-gray-700">Sube tu imagen</label>
              <input 
                ref="fileInputRef"
                type="file" 
                @change="handleFileUpload"
                required
                accept="image/*"
                class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-blue-600 hover:file:bg-indigo-100"
              />
              <div v-if="imagePreview" class="mt-4">
                <img :src="imagePreview" class="w-32 rounded-lg border shadow-sm" alt="Previsualización" />
              </div>
            </div>
            <div v-if="loading" class="text-center text-blue-600">Subiendo imagen...</div>
            <div v-if="error" class="text-center text-red-500">{{ error }}</div>
            <button
              type="submit"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Publicando...' : 'Publicar Imagen' }}
            </button>
          </form>
        </fieldset>
      </div>

      <div class="lg:col-span-2">
        <h2 class="text-xl font-semibold mb-6">Mis Imágenes Subidas (Ronda {{ selectedRound }})</h2>
        <div v-if="galleryLoading" class="text-center text-gray-500">Cargando mis imágenes...</div>
        <div v-else-if="myImagesInRound.length === 0" class="text-center text-gray-500 p-10 bg-gray-50 rounded-lg">
          Aún no has subido una imagen en esta ronda.
        </div>
        <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-6">
          <div v-for="img in myImagesInRound" :key="img.id" class="relative bg-white rounded-xl shadow-sm overflow-hidden flex flex-col items-center p-2">
            <button
              @click="deleteImage(img.id)"
              class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 leading-none hover:bg-red-600 focus:outline-none z-10"
              aria-label="Borrar imagen"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <img :src="img.url" class="w-full h-40 object-cover rounded-md" :alt="img.name" />
            <div class="text-center w-full mt-2">
              <p class="text-xs text-gray-500">{{ img.votes }} votos</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  
  <div v-else class="min-h-screen text-center py-20">
    <h2 class="text-3xl font-bold">Acceso Denegado</h2>
    <p class="mt-2 text-gray-600">Por favor, <router-link to="/login" class="text-blue-600 font-semibold hover:underline">inicia sesión</router-link> para subir una imagen.</p>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue"; 
import { toast } from 'vue3-toastify';
import { getToken, isAuthenticated, currentUser } from '../store/auth.js';

const API_URL = import.meta.env.VITE_API_URL;
const participantName = ref(currentUser.value || ""); 
const selectedRound = ref(1);
const imageFile = ref(null);
const imagePreview = ref(null);
const fileInputRef = ref(null);
const galleries = ref({ 1: [], 2: [], 3: [], 4: [] });
const loading = ref(false);
const error = ref(null);
const galleryLoading = ref(false);

const currentUserImage = computed(() => {
  if (!participantName.value.trim() || !galleries.value[selectedRound.value]) {
    return null;
  }
  const currentParticipantName = participantName.value.trim().toLowerCase();
  return galleries.value[selectedRound.value].find(
    img => img.name.trim().toLowerCase() === currentParticipantName
  );
});

// CAMBIO: Nueva propiedad computada para filtrar solo tus imágenes
const myImagesInRound = computed(() => {
  if (!galleries.value[selectedRound.value] || !participantName.value) {
    return [];
  }
  const currentName = participantName.value.trim().toLowerCase();
  return galleries.value[selectedRound.value].filter(
    img => img.name.trim().toLowerCase() === currentName
  );
});


async function fetchGallery(round) {
  galleryLoading.value = true;
  try {
    const response = await fetch(`${API_URL}/api/rondas/${round}/images`);
    if (!response.ok) throw new Error('Error al cargar la galería.');
    const images = await response.json();
    galleries.value[round] = images.map(img => ({
      ...img,
      url: `${API_URL}${img.url}` 
    }));
  } catch (err) {
    console.error(`Error fetching gallery for round ${round}:`, err);
    galleries.value[round] = []; 
  } finally {
    galleryLoading.value = false;
  }
}

onMounted(() => {
  if (isAuthenticated.value) {
    Promise.all([
      fetchGallery(1),
      fetchGallery(2),
      fetchGallery(3),
      fetchGallery(4)
    ]);
  }
});

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  imageFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => { imagePreview.value = e.target.result; };
  reader.readAsDataURL(file);
}

async function publishImage() {
  if (!imageFile.value) {
    return toast.error('Por favor, selecciona una imagen.');
  }
  loading.value = true;
  error.value = null;
  const formData = new FormData();
  formData.append('image', imageFile.value);
  const token = getToken();
  try {
    const response = await fetch(`${API_URL}/api/rondas/${selectedRound.value}/upload`, {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` },
      body: formData,
    });
    if (!response.ok) {
      const errData = await response.json();
      throw new Error(errData.message || 'Ocurrió un error al subir la imagen.');
    }
    await response.json();
    toast.success('¡Imagen subida con éxito!'); 
    await fetchGallery(selectedRound.value);
    
    imageFile.value = null;
    imagePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
    
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

async function deleteImage(imageId) {
  if (!confirm('¿Estás seguro de que quieres borrar tu imagen?')) return;
  
  const token = getToken();
  
  try {
    const response = await fetch(`${API_URL}/api/images/${imageId}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (!response.ok) throw new Error('No se pudo borrar la imagen.');
    
    toast.success('Imagen borrada correctamente.'); 
    await fetchGallery(selectedRound.value);

  } catch (err) {
    console.error(err);
    alert(err.message);
  }
}
</script>