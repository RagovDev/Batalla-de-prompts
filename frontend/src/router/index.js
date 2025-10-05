// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";

// Importar las vistas
import AuthView from '../views/AuthView.vue';
import ParticiparView from "../views/ParticiparView.vue";
import TemasView from '../views/TemasView.vue'
import VotacionesView from "../views/VotacionesView.vue";
import ResultadosView from "../views/ResultadosView.vue";

const routes = [
  {
    path: "/",
    redirect: "/participar" // 👈 ruta por defecto
  },
  {
    path: '/login', 
    name: 'login',
    component: AuthView
  }, 
  {
    path: "/participar",
    name: "participar",
    component: ParticiparView,
  },
  {
    path: '/temas', // <-- AÑADE ESTA RUTA
    name: 'temas',
    component: TemasView
  },
  {
    path: "/votaciones",
    name: "votaciones",
    component: VotacionesView,
  },
  {
    path: "/resultados",
    name: "resultados",
    component: ResultadosView,
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
