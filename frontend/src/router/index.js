// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";
import { isAuthenticated } from '../store/auth.js';

// Importar las vistas
import AuthView from '../views/AuthView.vue';
import NotFoundView from '../views/NotFoundView.vue';
import ParticiparView from "../views/ParticiparView.vue";
import TemasView from '../views/TemasView.vue'
import VotacionesView from "../views/VotacionesView.vue";
import ResultadosView from "../views/ResultadosView.vue";

const routes = [
  {
    path: "/",
    redirect: "/participar" // Ruta por defecto
  },
  {
    path: '/login', 
    name: 'login',
    component: AuthView,
    meta: { requiresGuest: true } 
  }, 
  {
    path: "/participar",
    name: "participar",
    component: ParticiparView,
    meta: { requiresAuth: true } 
  },
  {
    path: '/temas', 
    name: 'temas',
    component: TemasView,
    meta: { requiresAuth: true } 
  },
  {
    path: "/votaciones",
    name: "votaciones",
    component: VotacionesView,
    meta: { requiresAuth: true } 
  },
  {
    path: "/resultados",
    name: "resultados",
    component: ResultadosView,
    meta: { requiresAuth: true } 
  },
  { 
    path: '/404', 
    name: 'NotFound', 
    component: NotFoundView 
  },
  { 
    path: '/:pathMatch(.*)*', 
    redirect: '/404' 
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  // 1. Si la ruta REQUIERE AUTENTICACIÓN y el usuario NO está autenticado...
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    // ...lo redirigimos a la página de login.
    next({ path: '/login' });
  } 
  // 2. Si la ruta es PARA INVITADOS (login) y el usuario SÍ está autenticado...
  else if (to.meta.requiresGuest && isAuthenticated.value) {
    // ...lo redirigimos a la página principal.
    next({ path: '/' });
  } 
  // 3. En cualquier otro caso, lo dejamos pasar.
  else {
    next();
  }
});

export default router;
