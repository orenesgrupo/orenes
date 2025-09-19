# Orenes Theme

Tema padre para WordPress desarrollado por **Samuel E. Cerezo** para el Grupo Orenes.  
Diseñado como un **naked theme** optimizado, con integración con Elementor y soporte extendido para SCSS, tipografías y recursos modernos.

---

## Características principales

- **Compilación automática de SCSS**  
  Todos los ficheros en `/scss` del tema hijo se compilan automáticamente en `/css/*.css` y se cargan en el frontend.

- **Gestión de tipografías**  
  Fuentes en `/fonts` se registran automáticamente, se exponen a Elementor y se generan en un `fonts.css` optimizado.  
  - Formatos soportados: **WOFF2** y **WOFF**.  
  - Eliminación automática de formatos obsoletos (TTF, SVG).

- **Soporte completo para Elementor**  
  - Plantillas traducibles con Polylang.  
  - Etiquetas dinámicas personalizadas (ejemplo: campos ACF de taxonomías).  
  - Widgets personalizados (ejemplo: **Mapbox Map**).

- **Seguridad y limpieza**  
  - Encabezados CSP configurables vía filtros.  
  - Eliminación de archivos de core innecesarios (`readme.html`, `license.txt`...).  
  - Subida segura de SVG (validación de contenido).  
  - Desactivación de pingbacks, XML-RPC y endpoints REST no autenticados.  
  - Limpieza de `wp_head`.

- **Optimización de imágenes**  
  Conversión automática a **WEBP** al subir JPG/PNG/GIF con nombre normalizado.

- **Estructura lista para hijo**  
  El tema está pensado para ser siempre extendido mediante un **tema hijo**, con la siguiente estructura mínima:  
  ```
  child/
  ├── scss/
  │   ├── main.scss
  │   └── admin.scss
  ├── js/
  │   ├── main.js
  │   └── admin.js
  └── fonts/
  ```

---

## Instalación

1. Clonar o descargar el repositorio en el directorio de temas de WordPress:
   ```bash
   git clone https://github.com/orenesgrupo/orenes.git wp-content/themes/orenes
   ```

2. Activar el tema desde el **Administrador de WordPress**.  
   > Nota: se recomienda crear y usar siempre un **tema hijo** para personalizaciones.

3. Estructura mínima del hijo:
   ```
   wp-content/themes/orenes-child/
   ├── style.css
   ├── functions.php
   ├── scss/main.scss
   ├── scss/admin.scss
   ├── js/main.js
   ├── js/admin.js
   └── fonts/
   ```

---

## Actualizaciones

El tema se actualiza automáticamente a través de GitHub Releases.  
Cualquier versión nueva publicada en [Releases](https://github.com/orenesgrupo/orenes/releases) aparecerá como actualización disponible en WordPress.

---

## Requisitos

- WordPress 6.0+  
- PHP 8.0+  
- Plugins recomendados:  
  - [Elementor](https://elementor.com/)  
  - [Polylang](https://polylang.pro/)  
  - [Advanced Custom Fields](https://www.advancedcustomfields.com/)  

---

## Desarrollo

- **SCSS**: se compila automáticamente al vuelo en el servidor usando [`scssphp`](https://scssphp.github.io/scssphp/).  
- **JS**: cualquier archivo en `/js` del hijo se carga automáticamente si existe.  
- **Fonts**: añadir subcarpetas en `/fonts` con nombre de familia y pesos en formato `300.woff2`, `400i.woff`, etc.

---

## Licencia

GPL-2.0 o posterior.  
Este tema está distribuido con la misma licencia que WordPress.

---

## Créditos

Desarrollado por **Samuel E. Cerezo**.  
© Grupo Orenes.
