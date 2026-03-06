# Inventario INDES - Backend (API REST)

Este es el backend del Sistema de Inventario para INDES, construido con Laravel. Provee la API RESTful que consume la aplicación frontend.

## Requisitos Previos
- PHP >= 8.2
- Composer
- MySQL o MariaDB
- Node.js y NPM (para compilar activos si es necesario)

## Guía de Instalación Local

1. **Clonar/Extraer el proyecto**
   Ingresa a la carpeta del backend.

2. **Instalar dependencias de PHP**
   ```bash
   composer install
   ```

3. **Configurar Variables de Entorno**
   Copia el archivo de ejemplo para crear tu configuración local:
   ```bash
   cp .env.example .env
   ```
   Abre el archivo `.env` y configura la conexión a la base de datos:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=inventory_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generar la Key de la Aplicación y JWT Secret**
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar Migraciones (Base de Datos)**
   Crea la estructura de tablas en tu base de datos:
   ```bash
   php artisan migrate
   ```
   *(Opcional)* Si cuentas con seeders para cargar datos iniciales, ejecuta:
   ```bash
   php artisan db:seed
   ```

6. **Levantar el Servidor de Desarrollo**
   ```bash
   php artisan serve
   ```
   La API estará disponible en `http://localhost:8000`.

## Estructura Principal
- `app/Http/Controllers`: Lógica de los endpoints.
- `app/Models`: Modelos Eloquent de la base de datos.
- `routes/api.php`: Definición de las rutas del sistema.
- `database/migrations`: Esquemas de las tablas.
