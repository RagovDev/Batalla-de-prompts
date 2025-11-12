import { ref, computed } from 'vue';

// --- ESTADO REACTIVO ---
// Creamos variables reactivas (refs) para almacenar el estado de autenticación.
// Estas variables intentan cargarse desde el localStorage al iniciar la aplicación,
// lo que permite "recordar" la sesión del usuario entre recargas.

// Almacena el token de autenticación (ej. "1|abc..."). Es null si no hay sesión.
const token = ref(localStorage.getItem('userToken') || null);
// Almacena el nombre del usuario logueado (ej. "andres").
const userName = ref(localStorage.getItem('userName') || null);
// Almacena si el usuario es admin (true/false).
// Comparamos con 'true' porque localStorage solo guarda strings.
const isAdmin = ref(localStorage.getItem('isAdmin') === 'true');

// --- PROPIEDADES COMPUTADAS (GETTERS) ---
// Estas son variables reactivas de solo lectura que otros componentes pueden importar
// para saber el estado actual de la autenticación.

/**
 * Propiedad computada que devuelve `true` si el usuario está autenticado (si token existe),
 * o `false` si no lo está.
 */
export const isAuthenticated = computed(() => !!token.value);

/**
 * Propiedad computada que devuelve el nombre del usuario actual.
 */
export const currentUser = computed(() => userName.value);

/**
 * Propiedad computada que devuelve `true` si el usuario actual es administrador.
 */
export const isAdminUser = computed(() => isAdmin.value); 

/**
 * Función simple (no reactiva) para obtener el valor actual del token.
 * Útil para añadirlo a las cabeceras de las peticiones API.
 */
export const getToken = () => token.value;

// --- ACCIONES ---

/**
 * Cierra la sesión del usuario.
 * Limpia el estado reactivo (la memoria) y el localStorage (el disco).
 */
export function logout() {
  // 1. Limpia las variables reactivas
  token.value = null;
  userName.value = null;
  isAdmin.value = false; 
  
  // 2. Limpia el almacenamiento persistente del navegador
  localStorage.removeItem('userToken');
  localStorage.removeItem('userName');
  localStorage.removeItem('isAdmin'); 
}

/**
 * Inicia la sesión del usuario.
 * Esta función es llamada por AuthView.vue después de un login exitoso.
 *  El token de Sanctum recibido del backend.
 */
export async function login(newToken) {
  // 1. Guarda el token en el estado y en localStorage
  token.value = newToken;
  localStorage.setItem('userToken', newToken);
  
  // Define la URL del backend (leyendo desde el archivo .env)
  const API_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

  // 2. Ahora que tenemos token, pedimos los datos del usuario
  try {
    // Petición a la ruta protegida /api/user de Laravel
    const response = await fetch(`${API_URL}/api/user`, {
      headers: {
        'Authorization': `Bearer ${newToken}`, // Envía el token para autorización
        'Accept': 'application/json',
      }
    });

    if (!response.ok) {
      // Si la petición falla (ej. token inválido), lanza un error
      throw new Error('No se pudo obtener la información del usuario.');
    }

    const user = await response.json();
    
    // 3. Guarda la información del usuario (nombre y rol)
    userName.value = user.name;
    localStorage.setItem('userName', user.name);
    
    // Guarda el estado de administrador.
    // (MySQL devuelve 1 o 0 para booleano, lo convertimos a true/false)
    isAdmin.value = user.is_admin === 1 || user.is_admin === true;
    localStorage.setItem('isAdmin', isAdmin.value);

  } catch (error) {
    // 4. Si algo falla en el proceso, se desloguea al usuario por seguridad
    console.error("Error al obtener datos del usuario:", error);
    logout();
  }
}