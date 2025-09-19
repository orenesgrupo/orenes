# Changelog
Todas las versiones notables de este proyecto se documentarán aquí.

El formato se basa en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto sigue [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.0] - 2025-09-19
### Añadido
- Soporte para carga automática de librerías de iconos personalizados en Elementor

## [2.2.1] - 2025-09-19
### Corregido
- Limpieza de cabeceras CSP más clara y editable.
- Eliminación automática de `.git`, `.gitattributes`, `.gitignore`, LICENSE y README.md en producción.

## [2.2.0] - 2025-09-18
### Añadido
- Generación automática de `fonts.css` desde `/fonts`.
- Creación de `main.scss` y `main.js` si no existen en el hijo.
### Cambiado
- Mejora de compilación SCSS.
- Refactor de filtros de placeholders y resolvers.
