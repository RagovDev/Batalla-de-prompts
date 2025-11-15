<template>
  <div class="max-w-7xl mx-auto pt-4 px-6 p-8">
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h2 class="text-3xl md:text-4xl font-bold">Panel de Administración</h2>
        <p class="mt-2 text-gray-600">Gestionar los temas del concurso.</p>
      </div>
      <button
        @click="showCreateForm"
        class="px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700"
      >
        + Añadir Nuevo Tema
      </button>
    </div>

    <div v-if="showForm" class="bg-white p-6 rounded-2xl shadow-md mb-8">
      <h3 class="text-xl font-semibold mb-6">{{ isEditing ? 'Editar Tema' : 'Crear Nuevo Tema' }}</h3>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Título del Tema</label>
            <input v-model="form.title" type="text" required class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ronda</label>
            <select v-model.number="form.ronda" required class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <option :value="1">Ronda 1</option>
              <option :value="2">Ronda 2</option>
              <option :value="3">Ronda 3</option>
              <option :value="4">Ronda 4</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Imagen de Muestra (Opcional)</label>
          <input ref="fileInput" @change="handleFileUpload" type="file" accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100"/>
          <div v-if="form.previewUrl" class="mt-2">
             <img :src="form.previewUrl" class="w-32 h-32 object-cover rounded-lg border" alt="Previsualización" />
          </div>
        </div>
        
        <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>

        <div class="flex justify-end gap-4">
          <button @click="hideForm" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Cancelar
          </button>
          <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700">
            {{ isEditing ? 'Actualizar Tema' : 'Guardar Tema' }}
          </button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-2xl shadow-md overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-500">Cargando temas...</div>
      <table v-else class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
          <tr>
            <th class="px-6 py-3">Imagen</th>
            <th class="px-6 py-3">Título del Tema</th>
            <th class="px-6 py-3 text-center">Ronda</th>
            <th class="px-6 py-3 text-center">Votos</th>
            <th class="px-6 py-3 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="theme in themes" :key="theme.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <img v-if="theme.image_url" :src="theme.image_url" :alt="theme.title" class="w-16 h-10 object-cover rounded-md" />
              <span v-else class="text-xs text-gray-400">Sin imagen</span>
            </td>
            <td class="px-6 py-4 font-medium">{{ theme.title }}</td>
            <td class="px-6 py-4 text-center">{{ theme.ronda }}</td>
            <td class="px-6 py-4 text-center">{{ theme.votes }}</td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="showEditForm(theme)" class="font-medium text-blue-600 hover:text-blue-800">Editar</button>
              <button @click="handleDelete(theme.id)" class="font-medium text-red-600 hover:text-red-800">Borrar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router'; // <-- CAMBIO: Importar useRouter
import { getToken, logout } from '../store/auth.js'; // <-- CAMBIO: Importar logout
import { toast } from 'vue3-toastify';

const API_URL = import.meta.env.VITE_API_URL;
const router = useRouter(); // <-- CAMBIO: Inicializar el router

const themes = ref([]);
const loading = ref(true);
const error = ref(null);
const fileInput = ref(null);

const showForm = ref(false);
const isEditing = ref(false);
const editingThemeId = ref(null);

const form = ref({
  title: '',
  ronda: 1,
  image: null,
  previewUrl: null
});

// --- Lógica de UI ---

function resetForm() {
  form.value = { title: '', ronda: 1, image: null, previewUrl: null };
  isEditing.value = false;
  editingThemeId.value = null;
  error.value = null;
  if (fileInput.value) fileInput.value.value = '';
}

function showCreateForm() {
  resetForm();
  showForm.value = true;
}

function showEditForm(theme) {
  resetForm();
  showForm.value = true;
  isEditing.value = true;
  editingThemeId.value = theme.id;
  
  form.value.title = theme.title;
  form.value.ronda = theme.ronda;
  form.value.previewUrl = theme.image_url; 
}

function hideForm() {
  showForm.value = false;
  resetForm();
}

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (file) {
    form.value.image = file;
    form.value.previewUrl = URL.createObjectURL(file);
  }
}

// --- Lógica de API (CRUD) ---

// 1. LEER (Read)
async function fetchThemes() {
  loading.value = true;
  const token = getToken();

  // Si no hay token, no intentes y redirige.
  if (!token) {
      toast.error("Sesión no válida. Redirigiendo al login.");
      logout();
      router.push('/login');
      return;
  }

  try {
    const response = await fetch(`${API_URL}/api/admin/themes`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    // CAMBIO: Manejo de error 401
    if (response.status === 401) {
      toast.error("Tu sesión ha expirado. Por favor, inicia sesión de nuevo.");
      logout(); // Limpia el token inválido
      router.push('/login'); // Envía al usuario a la página de login
      return;
    }
    if (!response.ok) throw new Error('Error al cargar los temas.');

    const data = await response.json();
    
    // CAMBIO: Construcción correcta de la URL de la imagen
    themes.value = data.map(theme => {
        let fullUrl = null;
        if (theme.image_url) {
            // El backend devuelve la ruta (ej: "themes/archivo.jpg")
            // Storage::url() la convierte en "/storage/themes/archivo.jpg"
            // Necesitamos añadir la URL base del API.
            fullUrl = `${API_URL}/storage/${theme.image_url}`;
        }
        return {
            ...theme,
            image_url: fullUrl
        };
    });

  } catch (err) {
    error.value = err.message;
    toast.error(err.message);
  } finally {
    loading.value = false;
  }
}

// 2. CREAR / ACTUALIZAR (Create / Update)
async function handleSubmit() {
  const token = getToken();
  error.value = null;

  const formData = new FormData();
  formData.append('title', form.value.title);
  formData.append('ronda', form.value.ronda);
  if (form.value.image) {
    formData.append('image', form.value.image);
  }

  let url = `${API_URL}/api/admin/themes`;
  let method = 'POST';

  if (isEditing.value) {
    url = `${API_URL}/api/admin/themes/${editingThemeId.value}`;
    formData.append('_method', 'PUT'); 
    method = 'POST'; 
  }

  try {
    const response = await fetch(url, {
      method: method,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData
    });
    
    // CAMBIO: Manejo de error 401 también aquí
    if (response.status === 401) {
      toast.error("Tu sesión ha expirado. Por favor, inicia sesión de nuevo.");
      logout();
      router.push('/login');
      return;
    }
    if (!response.ok) {
      const errData = await response.json();
      throw new Error(errData.message || 'Error al guardar el tema.');
    }

    toast.success(isEditing.value ? '¡Tema actualizado!' : '¡Tema creado!');
    hideForm();
    await fetchThemes(); // Recarga la lista

  } catch (err) {
    error.value = err.message;
    toast.error(err.message);
  }
}

// 3. BORRAR (Delete)
async function handleDelete(themeId) {
  if (!confirm('¿Estás seguro de que quieres borrar este tema? Esta acción no se puede deshacer.')) return;

  const token = getToken();

  try {
    const response = await fetch(`${API_URL}/api/admin/themes/${themeId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    // CAMBIO: Manejo de error 401 también aquí
    if (response.status === 401) {
      toast.error("Tu sesión ha expirado. Por favor, inicia sesión de nuevo.");
      logout();
      router.push('/login');
      return;
    }
    if (!response.ok) {
      const errData = await response.json();
      throw new Error(errData.message || 'Error al borrar el tema.');
    }

    toast.success('Tema borrado con éxito.');
    await fetchThemes(); // Recarga la lista

  } catch (err) {
    toast.error(err.message);
  }
}

// Carga inicial de datos
onMounted(() => {
  fetchThemes();
});
</script>