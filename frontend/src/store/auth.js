import { ref, computed } from 'vue';

// Estado reactivo para el token. Lo inicializamos desde localStorage.
const token = ref(localStorage.getItem('userToken') || null);
const userName = ref(localStorage.getItem('userName') || null);

// Una propiedad computada para saber fácilmente si el usuario está logueado.
export const isAuthenticated = computed(() => !!token.value);
export const currentUser = computed(() => userName.value);

// Función para guardar el token y el nombre al iniciar sesión.
export function login(newToken, name) {
  token.value = newToken;
  userName.value = name;
  localStorage.setItem('userToken', newToken);
  localStorage.setItem('userName', name);
}

// Función para limpiar el estado al cerrar sesión.
export function logout() {
  token.value = null;
  userName.value = null;
  localStorage.removeItem('userToken');
  localStorage.removeItem('userName');
}

// Función para obtener el token actual.
export function getToken() {
  return token.value;
}