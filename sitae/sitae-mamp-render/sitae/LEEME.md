# SITAE – PHP + MySQL (MAMP) / PostgreSQL (Render gratis)

El mismo código funciona en dos lugares:
- **Tu computadora con MAMP** → usa MySQL.
- **Internet con Render + Neon (planes gratuitos)** → usa PostgreSQL. Se activa solo cuando existe la variable `DATABASE_URL`.

Roles: **Administrador** (licencias + usuarios), **Editor** (crea, edita y elimina licencias), **Lector** (solo consulta).
El administrador registra usuarios en la pantalla **Usuarios** y les asigna el rol; no hay registro público.

---
## A) Instalación local con MAMP

1. Copia esta carpeta a `htdocs` de MAMP y llámala `sitae`
   (Mac: `/Applications/MAMP/htdocs/sitae` · Windows: `C:\MAMP\htdocs\sitae`). `index.html` debe quedar dentro de `sitae`.
2. Abre MAMP y pulsa **Start**. Anota el puerto de Apache y el de MySQL (pantalla de inicio de MAMP).
3. Abre phpMyAdmin (WebStart de MAMP → Tools → phpMyAdmin) → **Importar** → `sql/sitae.sql`.
4. Revisa `api/config.php`: por defecto host localhost, puerto **3306**, usuario **root**, contraseña **root**. Cambia el puerto si el tuyo es otro.
5. Crea el primer administrador, con una de estas dos formas:
   - **Fácil:** abre `http://localhost:8888/sitae/instalar.php` (usa tu puerto de Apache) y llena el formulario.
   - **Terminal:** `php herramientas/crear_admin.php` (usa el PHP de MAMP, por ejemplo `/Applications/MAMP/bin/php/php8.x.x/bin/php`).
6. Entra a `http://localhost:8888/sitae/`, pulsa **Usuarios** y crea editores y lectores.
7. Opcional: importa `sql/datos_ejemplo.sql` para ver 4 licencias de demostración.

Debe abrirse desde Apache (MAMP); Live Server o doble clic no ejecutan PHP.

---
## B) Publicar gratis en Render (con Neon)

Render gratuito no incluye MySQL y su base gratuita expira, por eso la base va en Neon (PostgreSQL gratuito). Verifica las condiciones vigentes de ambos servicios.

1. **GitHub:** crea un repositorio **privado** y sube todo el contenido de esta carpeta (`Dockerfile` e `index.html` en la raíz del repositorio).
2. **Neon** (neon.tech): crea un proyecto y copia la cadena de conexión **directa** (sin "pooler"). Empieza con `postgresql://`.
3. **Render** (render.com): *New → Web Service* → elige el repositorio → Language: **Docker** → plan **Free**.
4. En *Environment* agrega:
   - `DATABASE_URL` = la cadena de Neon
   - `INSTALL_KEY` = una clave inventada por ti
5. Cuando termine el despliegue, abre `https://TU-APP.onrender.com/instalar.php`, escribe la clave y crea el administrador. Las tablas se crean solas.
6. Inicia sesión y registra usuarios desde **Usuarios**.

Limitaciones del plan gratuito: la app se duerme tras unos minutos sin uso (la primera visita tarda); las sesiones se pierden al reiniciarse; Neon puede tardar unos segundos en "despertar"; haz respaldos exportando datos desde Neon. Antes de guardar licencias reales en servidores externos, consulta con Infotecnología.

---
## Qué se adaptó respecto a la versión XAMPP
- `api/config.php` y `api/conexion.php`: eligen MySQL o PostgreSQL; función `q()` para escapar columnas.
- `api/licencias.php`, `api/auth.php`, `api/comun.php`: consultas compatibles con ambos motores (búsqueda sin distinguir mayúsculas, `ON CONFLICT`, id nuevo, códigos de error de duplicado, cookie segura tras el proxy de Render).
- Nuevos: `sql/sitae_postgres.sql`, `sql/datos_ejemplo_postgres.sql`, `instalar.php`, `Dockerfile`, `.dockerignore`.

## Estructura
```text
sitae/
  index.html, lista.html, licencia.html, usuarios.html
  css/  js/  api/
  sql/       sitae.sql (MySQL) · sitae_postgres.sql · datos de ejemplo
  herramientas/crear_admin.php   (solo consola)
  instalar.php                   (una vez; se bloquea al existir un administrador)
  Dockerfile                     (para Render)
```
