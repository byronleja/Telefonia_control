# GestiCell — Sistema de Gestión de Teléfonos Corporativos

Sistema web construido con **Laravel + Blade** para gestionar empleados, dispositivos móviles y solicitudes de renovación.

---

## 🚀 Instalación desde cero

### 1. Crear el proyecto Laravel
```bash
composer create-project laravel/laravel gesticell
cd gesticell
```

### 2. Copiar los archivos de este repositorio
Reemplaza/copia los siguientes directorios y archivos en tu proyecto:

```
app/Models/                         → Empleado, Dispositivo, Renovacion, Adjunto
app/Http/Controllers/               → Empleado, Dispositivo, Renovacion, Reporte Controllers
resources/views/layouts/app.blade.php
resources/views/empleados/          → index, create, edit, show
resources/views/dispositivos/       → index, create, edit, show
resources/views/renovaciones/       → index, create, edit, show
resources/views/reportes/           → index, renovaciones, dispositivos
database/migrations/                → 4 migraciones
database/seeders/DatabaseSeeder.php
routes/web.php
```

### 3. Configurar el `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gesticell
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### 4. Crear la base de datos
```sql
CREATE DATABASE gesticell CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Ejecutar migraciones y seeders
```bash
php artisan migrate --seed
```

### 6. Configurar storage (para archivos adjuntos)
```bash
php artisan storage:link
```

### 7. Iniciar servidor
```bash
php artisan serve
```

Accede en: **http://localhost:8000**

---

## 📁 Estructura del proyecto

```
app/
├── Models/
│   ├── Empleado.php          → SoftDeletes, relaciones, scope activos
│   ├── Dispositivo.php       → SoftDeletes, scope disponibles
│   ├── Renovacion.php        → Auto-genera REN-YYYY-NNNN en boot()
│   └── Adjunto.php           → URL y tamaño formateado
│
├── Http/Controllers/
│   ├── EmpleadoController.php
│   ├── DispositivoController.php
│   ├── RenovacionController.php  → Upload de adjuntos incluido
│   └── ReporteController.php     → Dashboard + 2 reportes con filtros

resources/views/
├── layouts/app.blade.php     → Sidebar oscuro, diseño moderno
├── empleados/                → index, create, edit, show
├── dispositivos/             → index, create, edit, show
├── renovaciones/             → index, create, edit, show (con timeline)
└── reportes/                 → index (dashboard), renovaciones, dispositivos
```

---

## ✨ Funcionalidades principales

| Módulo | Funciones |
|---|---|
| **Empleados** | CRUD completo, filtros por departamento/estado, historial de dispositivos y renovaciones |
| **Dispositivos** | CRUD completo, filtros, estados (disponible/asignado/reparación/baja), asignación a empleado |
| **Renovaciones** | CRUD + número automático `REN-2026-0001`, `fecha_entrega`, `usuario_entrega`, adjuntos, timeline de estados |
| **Reportes** | Dashboard con estadísticas, reporte de renovaciones y dispositivos con filtros exportables |

---

## 🔢 Numeración automática de solicitudes

El modelo `Renovacion` genera el número en el evento `creating`:

```php
// Ejemplo: REN-2026-0001, REN-2026-0002 …
protected static function boot(): void
{
    parent::boot();
    static::creating(function ($renovacion) {
        if (empty($renovacion->numero_solicitud)) {
            $renovacion->numero_solicitud = self::generarNumeroSolicitud();
        }
    });
}
```

Se reinicia el contador cada año automáticamente.

---

## 📎 Archivos adjuntos

- Admite: PDF, JPG, PNG, DOC, DOCX
- Máximo: 10 MB por archivo
- Se almacenan en `storage/app/public/adjuntos/renovaciones/{id}/`
- Se pueden agregar múltiples archivos por renovación
- Tipos: `solicitud`, `aprobacion`, `entrega`, `otro`

---

## 🗂️ Rutas disponibles

```
GET  /                          → Redirige al dashboard
GET  /empleados                 → Lista de empleados
GET  /dispositivos              → Lista de dispositivos
GET  /renovaciones              → Lista de renovaciones
GET  /reportes                  → Dashboard
GET  /reportes/renovaciones     → Reporte filtrable
GET  /reportes/dispositivos     → Reporte filtrable
DELETE /adjuntos/{adjunto}      → Eliminar archivo adjunto
```

---

## 🛠️ Requisitos

- PHP >= 8.1
- Laravel >= 10
- MySQL >= 5.7 / MariaDB >= 10.3
- Composer
