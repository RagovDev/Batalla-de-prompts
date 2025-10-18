<template>
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
      <div class="pt-8">
        <h2 class="text-3xl font-extrabold text-center text-gray-900">
          {{ isRegistering ? 'Crea tu Cuenta' : 'Inicia Sesión' }}
        </h2>
      </div>

      <form class="space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nombre de Usuario</label>
          <input v-model="name" id="name" name="name" type="text" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input v-model="password" id="password" name="password" type="password" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div v-if="isRegistering">
          <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
          <input v-model="confirmPassword" id="confirmPassword" name="confirmPassword" type="password" required
                 class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div v-if="error" class="p-3 text-sm text-red-700 bg-red-100 rounded-md">
          {{ error }}
        </div>

        <div>
          <button type="submit"
                  class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ isRegistering ? 'Registrarse' : 'Iniciar Sesión' }}
          </button>
        </div>
      </form>

      <div class="text-sm text-center">
        <a @click.prevent="isRegistering = !isRegistering; error = null" href="#" class="font-medium text-blue-600 hover:text-blue-500">
          {{ isRegistering ? '¿Ya tienes una cuenta? Inicia sesión' : '¿No tienes cuenta? Regístrate' }}
        </a>
      </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import { useRouter } from 'vue-router';
import { login } from '../store/auth.js'; 

const API_URL = import.meta.env.VITE_API_URL;
const router = useRouter();

const isRegistering = ref(false);
const name = ref('');
const password = ref('');
const confirmPassword = ref('');
const error = ref(null);

async function handleSubmit() {
  error.value = null;

  if (isRegistering.value && password.value !== confirmPassword.value) {
    error.value = 'Las contraseñas no coinciden.';
    return;
  }
  
  const endpoint = isRegistering.value ? '/api/register' : '/api/login';
  
  try {
    const response = await fetch(`${API_URL}${endpoint}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: name.value, password: password.value })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Ocurrió un error.');
    }

    if (isRegistering.value) {
      toast.success('¡Registro exitoso! Ahora, por favor inicia sesión.'); 
      isRegistering.value = false;
    } else {
      login(data.token, name.value);
      router.push('/');
    }

  } catch (err) {
    error.value = err.message;
  }
}
</script>