// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";
import { isAuthenticated, isAdminUser } from '../store/auth.js';

// Importar las vistas
import AuthView from '../views/AuthView.vue';
import NotFoundView from '../views/NotFoundView.vue';
import ParticiparView from "../views/ParticiparView.vue";
import TemasView from '../views/TemasView.vue'
import VotacionesView from "../views/VotacionesView.vue";
import ResultadosView from "../views/ResultadosView.vue";
import AdminDashboardView from '../views/AdminDashboardView.vue';

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
    path: "/admin",
    name: "admin",
    component: AdminDashboardView,
    meta: { requiresAdmin: true } // Nueva marca de metadatos
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
  const isAuth = isAuthenticated.value;
  const isAdmin = isAdminUser.value;

  // 1. Si la ruta REQUIERE SER ADMIN y el usuario NO es admin
  if (to.meta.requiresAdmin && !isAdmin) {
    next({ path: '/' }); // O redirige a '/404' si lo prefieres
  }
  // 2. Si la ruta REQUIERE AUTENTICACIÓN (y no es admin) y el usuario NO está autenticado
  else if (to.meta.requiresAuth && !isAuth) {
    next({ path: '/login' });
  } 
  // 3. Si la ruta es PARA INVITADOS (login) y el usuario SÍ está autenticado
  else if (to.meta.requiresGuest && isAuth) {
    next({ path: '/' });
  } 
  // 4. En cualquier otro caso, déjalo pasar
  else {
    next();
  }
});

export default router;
