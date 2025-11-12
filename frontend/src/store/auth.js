import { ref, computed } from 'vue';

const token = ref(localStorage.getItem('userToken') || null);
const userName = ref(localStorage.getItem('userName') || null);

export const isAuthenticated = computed(() => !!token.value);
export const currentUser = computed(() => userName.value);
export const getToken = () => token.value;

/**
 * Función de Logout - Limpia el estado y localStorage
 */
export function logout() {
  token.value = null;
  userName.value = null;
  localStorage.removeItem('userToken');
  localStorage.removeItem('userName');
  // Ya no es necesario el reload, el router se encargará
}

/**
 * Función de Login - Guarda el token y obtiene los datos del usuario
 * @param {string} newToken - El token de Sanctum recibido del login
 */
export async function login(newToken) {
  // 1. Guardar el token
  token.value = newToken;
  localStorage.setItem('userToken', newToken);

  const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

  try {
    // 2. Usar el token para pedir la información del usuario a /api/user
    const response = await fetch(`${API_URL}/api/user`, {
      headers: {
        'Authorization': `Bearer ${newToken}`,
        'Accept': 'application/json',
      }
    });

    if (!response.ok) {
      throw new Error('No se pudo obtener la información del usuario.');
    }

    const user = await response.json();
    
    // 3. Guardar el nombre del usuario
    userName.value = user.name;
    localStorage.setItem('userName', user.name);

  } catch (error) {
    console.error("Error al obtener datos del usuario:", error);
    // Si falla (ej. token inválido), cerramos la sesión
    logout();
  }
}