# 🧩 Migración y Modernización del Módulo de Clientes

## 📄 Descripción

Este proyecto simula la migración de un pequeño CRM legacy (PHP plano) hacia una plataforma moderna compuesta por una **API en Laravel** y un **frontend en Next.js**.

El objetivo principal es trasladar la gestión de clientes garantizando **control, seguridad y previsibilidad** en las operaciones sobre la base de datos.  
**No se utiliza ningún ORM (como Eloquent)**; todas las consultas se escriben en **SQL nativo** usando el facade `DB` de Laravel (`DB::select`, `DB::insert`, `DB::update`, `DB::delete`) y **bindings** para prevenir inyección SQL.

---

## 🧱 Componentes principales

### 🗂 Legacy Endpoint
El archivo `legacy_clients.php` se encuentra en la carpeta `/legacy`, dentro del directorio principal del proyecto.

Incluye un modo `?format=json` que:
- Exporta los clientes sin datos sensibles (por ejemplo, `password_hash`).
- Divide el campo `full_name` en `first_name` y `last_name`.
- Facilita la importación en el nuevo sistema moderno mediante el comando Artisan.

---

### ⚙️ API Laravel
- **Migración:** tabla `clients` con los campos:
  - `id`, `first_name`, `last_name`, `email`, `status` (o `active`), `legacy_id`, `created_at`, `updated_at`
- **Controlador REST:** gestiona las peticiones HTTP.
- **FormRequest:** valida las entradas antes de llegar al servicio.
- **ClientService:** contiene las consultas SQL nativas y la lógica de negocio.

---

### 🧭 Comando Artisan
`php artisan import:legacy-clients`

- Sincroniza los clientes desde el endpoint legacy.
- Para cada cliente recibido:
  - Comprueba si ya existe por `legacy_id`.
  - Inserta o actualiza según corresponda.
- Usa **transacciones** y es **idempotente** (puede ejecutarse varias veces sin duplicar datos).

---

### 🧪 Tests y documentación
- Los tests de **feature y unitarios** se encuentran en la carpeta `/tests` y **cubren todos los métodos** del servicio (`ClientService`) y del controlador (`ClientController`).
- En la carpeta `/docs` se incluye:
  - Una **colección de Postman** para probar los endpoints manualmente.

---

## 💡 Principios de diseño

- **Seguridad:** todas las consultas usan placeholders (`?`) y bindings → evita inyección SQL.
- **Claridad y separación:**  
  - El controlador solo orquesta.  
  - El `ClientService` accede a los datos.  
  - El comando Artisan delega la lógica de importación.
- **Timestamps y datos legacy:**  
  - `created_at` se preserva desde el legacy cuando existe.  
  - `updated_at` se inicializa en `NULL` y solo se modifica al actualizar registros.
- **Idempotencia y transacciones:**  
  Cada inserción/actualización se ejecuta dentro de una transacción para mantener la consistencia del sistema.


## 💻 Frontend Next.js

El frontend de este proyecto está disponible en:

https://github.com/alexmruiz/front-clients.git

Es una aplicación Next.js que consume la API de Laravel para mostrar, crear, actualizar y eliminar clientes.

⚡ Instalación rápida del frontend

Clonar el repositorio:

git clone https://github.com/alexmruiz/front-clients.git

cd front-clients

---

Instalar dependencias:

 npm install

Levantar el servidor de desarrollo:

 npm run dev

La aplicación estará disponible en http://localhost:3000
.

## ⚙️ Instalación y puesta en marcha de la API Laravel

Sigue estos pasos para levantar el proyecto en tu entorno local:

---

### 🧩 1. Clonar el repositorio

git clone https://github.com/alexmruiz/clients-api.git
cd clients-api

---

### 🐘 2. Instalar dependencias de Laravel

composer install

---

### 🔧 3. Configurar el entorno

Copia el archivo de entorno y ajusta las variables según tu configuración local (base de datos, etc.):

cp .env.example .env

Luego, genera la clave de aplicación de Laravel:

php artisan key:generate

Edita el archivo `.env` y actualiza los valores de conexión:

`DB_CONNECTION=sqlite`

---

### 🗄️ 4. Ejecutar las migraciones

Crea la base de datos y ejecuta las migraciones:

php artisan migrate

---

### 🧪 5. Levantar el archivo legacy

Para que la API pueda consumir los clientes legacy, coloca el archivo legacy_clients.php en un directorio accesible por tu servidor web. Por ejemplo, en XAMPP:

C:\xampp\htdocs\legacy_clients.php

Asegúrate de que la URL para acceder al archivo sea algo como:

http://localhost/legacy_clients.php

---

### 🔄 6. Importar los clientes del sistema legacy

Ejecuta el comando Artisan para sincronizar los datos:

php artisan import:legacy-clients

Este comando leerá los clientes desde el endpoint legacy (?format=json) y los insertará o actualizará en la tabla clients.

---

### 🧪 7. Ejecutar los tests

El proyecto incluye tests tanto de **servicio** como de **controlador**.

Para ejecutar todos los tests:

php artisan test

También puedes ejecutar tests específicos:

php artisan test --filter=ClientServiceTest
php artisan test --filter=ClientControllerTest

---

### 🚀 8. Levantar el servidor de desarrollo

Por último, inicia el servidor de Laravel:

php artisan serve

La API estará disponible en:

**[http://localhost:8000/api/clients](http://localhost:8000/api/clients)**

---

### 🧰 8. Documentación y pruebas manuales

En la carpeta `/docs` encontrarás:

* El archivo **Postman Collection** para probar manualmente los endpoints (`POST /api/clients`, `GET /api/clients`, etc.).
* Ejemplos de peticiones con headers (`Accept: application/json`) y cuerpos JSON válidos.

Puedes importar el archivo en Postman desde:
**File → Import → Upload Files → Seleccionar el archivo `.json` de la carpeta `docs`**

---

### ✅ Endpoints principales

| Método   | Endpoint            | Descripción                                 |
| -------- | ------------------- | ------------------------------------------- |
| `GET`    | `/api/clients`      | Obtiene todos los clientes (con paginación) |
| `GET`    | `/api/clients/{id}` | Obtiene un cliente por ID                   |
| `POST`   | `/api/clients`      | Crea un nuevo cliente                       |
| `PUT`    | `/api/clients/{id}` | Actualiza un cliente existente              |
| `DELETE` | `/api/clients/{id}` | Elimina un cliente                          |

