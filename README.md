<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</p>

<h1 align="center">Sistema de Gestión de Empleados</h1>

## Descripción general

Este proyecto es una aplicación web desarrollada con Laravel cuyo objetivo principal es administrar el ciclo básico de empleados dentro de una organización.

En esta primera versión, el sistema se enfoca en:

- Registro (alta) de empleados.
- Baja lógica de empleados (sin eliminación física en la base de datos).
- Administración de usuarios autenticados.
- Manejo de roles y permisos básicos para controlar el acceso a las funciones del sistema.
- Listado y consulta de empleados activos e inactivos.

El enfoque es ligero y portable, pensado para instalarse con la menor cantidad de dependencias posibles en cada entorno donde se despliegue.

---

## Tecnologías utilizadas

El proyecto está construido sobre el siguiente stack:

- **PHP 8.2+**
- **Laravel 11**
- **Composer** para la gestión de dependencias de PHP.
- **MariaDB o MySQL** como sistema de gestión de base de datos relacional.
- **Blade** como motor de plantillas para la capa de presentación.
- **HTML5, CSS3 y JavaScript** para la interfaz de usuario.
- Opcionalmente, un framework CSS (por ejemplo, Bootstrap o Tailwind CSS) para el maquetado y estilos.

---

## Requisitos del sistema

Para ejecutar el proyecto se requiere:

- PHP 8.2 o superior con extensiones recomendadas para Laravel (pdo, mbstring, tokenizer, openssl, etc.).
- Composer instalado globalmente.
- Servidor de base de datos:
  - MariaDB o MySQL.
- Servidor web:
  - Nginx o Apache, o bien un entorno tipo Laragon/XAMPP/WAMP en entornos de desarrollo.
- Node.js y npm (solo si se desea compilar assets front-end).

---

## Instalación

1. Clonar el repositorio:

   ```bash
   git clone https://ruta-de-tu-repositorio.git
   cd nombre-de-tu-proyecto
