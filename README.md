# 🧩 Migración y Modernización del Módulo de Clientes

## 📄 Descripción

Este proyecto simula la migración de un pequeño CRM legacy (PHP plano) hacia una plataforma moderna compuesta por una **API en Laravel** y, opcionalmente, un **frontend en Next.js**.

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


