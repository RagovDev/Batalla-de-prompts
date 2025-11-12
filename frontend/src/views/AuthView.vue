<template>
  <div class="flex items-center justify-center h-full w-full">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <div class="pt-8">
        <h2 class="text-3xl font-extrabold text-center text-gray-900">
          {{ isRegistering ? 'Crea tu Cuenta' : 'Inicia Sesión' }}
        </h2>
      </div>

      <form class="space-y-6" @submit.prevent="handleSubmit">
        <div v-if="isRegistering">
          <label for="name" class="block text-sm font-medium text-gray-700">Nombre de Usuario</label>
          <input v-model="form.name" id="name" name="name" type="text" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" id="email" name="email" type="email" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input v-model="form.password" id="password" name="password" type="password" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div v-if="isRegistering">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
          <input v-model="form.password_confirmation" id="password_confirmation" name="password_confirmation" type="password" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div v-if="error" class="p-3 text-sm text-red-700 bg-red-100 rounded-md">
          {{ error }}
        </div>

        <div>
          <button type="submit" :disabled="loading"
                  class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400">
            {{ loading ? 'Procesando...' : (isRegistering ? 'Registrarse' : 'Iniciar Sesión') }}
          </button>
        </div>
      </form>

      <div class="text-sm text-center">
        <a @click.prevent="toggleMode" href="#" class="font-medium text-blue-600 hover:text-blue-500">
          {{ isRegistering ? '¿Ya tienes una cuenta? Inicia sesión' : '¿No tienes cuenta? Regístrate' }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { login } from '../store/auth.js'; 

const API_URL = import.meta.env.VITE_API_URL;
const router = useRouter();

const isRegistering = ref(false);
const error = ref(null);
const loading = ref(false);

// CAMBIO: Usamos un objeto 'form' reactivo para manejar los campos
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Función para limpiar el formulario al cambiar de modo
function toggleMode() {
  isRegistering.value = !isRegistering.value;
  error.value = null;
  form.name = '';
  form.email = '';
  form.password = '';
  form.password_confirmation = '';
}

async function handleSubmit() {
  error.value = null;
  loading.value = true;

  if (isRegistering.value) {
    // --- LÓGICA DE REGISTRO ---
    if (form.password !== form.password_confirmation) {
      error.value = 'Las contraseñas no coinciden.';
      loading.value = false;
      return;
    }
    
    try {
      const response = await fetch(`${API_URL}/api/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(form)
      });

      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Error en el registro.');

      // Éxito en el registro
      alert('¡Registro exitoso! Ahora, por favor inicia sesión.');
      toggleMode(); // Cambia al formulario de login

    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
    
  } else {
    // --- LÓGICA DE LOGIN ---
    try {
      const response = await fetch(`${API_URL}/api/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: form.email,
          password: form.password
        })
      });

      const data = await response.json();
      if (!response.ok) throw new Error(data.message || 'Credenciales inválidas.');
      
      // CAMBIO: Llamamos a nuestra nueva función 'login' del store
      await login(data.token); 
      
      router.push('/'); // Redirige al usuario a la página principal

    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  }
}
</script>