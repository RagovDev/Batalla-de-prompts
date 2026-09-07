# 🏆 Batalla de Prompts - Plataforma de Concurso interactivo

Una plataforma web Full-Stack desarrollada para gestionar un concurso de generación de imágenes ("Batalla de Prompts"). Permite a los usuarios registrarse, participar subiendo imágenes generadas por IA en diferentes rondas, votar por sus favoritas y visualizar los resultados del torneo. Cuenta con un completo panel de administración para gestionar el flujo del concurso.

## ✨ Características Principales

### 👥 Para Usuarios (Participantes)
* **Autenticación Segura:** Registro e inicio de sesión protegidos con tokens (Laravel Sanctum).
* **Participación por Rondas:** Sistema estructurado en 4 rondas temáticas.
* **Galería Interactiva:** Subida y visualización de imágenes generadas para cada tema.
* **Sistema de Votación Doble:** 
  * Votación por las mejores imágenes de otros participantes.
  * Votación para elegir los mejores temas del concurso.
* **Resultados en Tiempo Real:** Dashboard dinámico con la puntuación acumulada y el ranking final de los participantes.

### 🛡️ Para Administradores (Panel de Control)
* **Control de Acceso (RBAC):** Rutas y vistas protegidas exclusivamente para usuarios con rol de administrador.
* **Gestión de Temas (CRUD):** Creación, edición y eliminación de temas para cada ronda, incluyendo la subida de imágenes de muestra.
* **Dashboard de Estadísticas:** Tabla de seguimiento detallada para auditar la participación de cada usuario (quién subió imagen, quién votó imágenes y quién votó temas en cada una de las 4 rondas).

---

## 🛠️ Tecnologías Utilizadas

El proyecto está dividido en dos partes (Frontend y Backend) para garantizar escalabilidad y mantenimiento.

**Frontend:**
* [Vue.js 3](https://vuejs.org/) (Composition API)
* [Vue Router](https://router.vuejs.org/) (Navegación SPA)
* [Tailwind CSS](https://tailwindcss.com/) (Diseño responsivo y utilidades)
* [Vite](https://vitejs.dev/) (Empaquetador)

**Backend:**
* [Laravel 11](https://laravel.com/) (Framework API REST)
* [MySQL](https://www.mysql.com/) (Base de datos relacional)
* [Laravel Sanctum](https://laravel.com/docs/sanctum) (Autenticación por API Tokens)

---

## 🚀 Instalación y Configuración Local

### 1. Requisitos Previos
* Node.js y npm instalados.
* PHP 8.2 o superior.
* Composer.
* Servidor MySQL (XAMPP, Laragon, TablePlus, etc.).

### 2. Configuración del Backend (Laravel)

# 1. Entra a la carpeta del backend
cd backend

# 2. Instala las dependencias de PHP
composer install

# 3. Copia el archivo de entorno y genera la clave
cp .env.example .env
php artisan key:generate

# 4. Configura tu base de datos en el archivo .env
# DB_DATABASE=nombre_de_tu_bd
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_contraseña

# 5. Ejecuta las migraciones
php artisan migrate

# 6. Crea el enlace simbólico para las imágenes
php artisan storage:link

# 7. Inicia el servidor de desarrollo
php artisan serve
