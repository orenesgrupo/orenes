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

- **Carga automática de funciones**  
  Todos los archivos PHP dentro de `/functions` (tanto en el tema padre como en el hijo) se cargan automáticamente de forma recursiva.

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

## Filtros disponibles

El tema expone varios **filtros** para personalizar su comportamiento desde el tema hijo o un plugin:

### `csp_policies`
Permite modificar las directivas de seguridad CSP enviadas en cabeceras HTTP.  
```php
add_filter('csp_policies', function ($policies) {
    $policies['img-src'][] = 'https://mi.cdn.com';
    $policies['script-src'][] = 'https://cdn.miapp.com';
    return $policies;
});
```

### `content_placeholders`
Permite añadir o sustituir placeholders dinámicos en contenidos de Elementor.  
```php
add_filter('content_placeholders', function ($placeholders) {
    $placeholders['{{site}}'] = esc_html(get_bloginfo('name'));
    return $placeholders;
});
```

### `content_links`
Permite extender el resolver de enlaces dinámicos (`{{post-123}}`, `{{term-123}}`, etc.).  
```php
add_filter('content_links', function ($resolver) {
    return function ($kind, $id) use ($resolver) {
        if ($kind === 'user') {
            return get_author_posts_url((int) $id);
        }
        return $resolver($kind, $id);
    };
});
```

### `fonts_cleanup_enabled`
Permite desactivar la limpieza automática de fuentes no soportadas.  
```php
add_filter('fonts_cleanup_enabled', '__return_false');
```

### `fonts_cleanup_extensions`
Permite definir qué extensiones de fuentes deben eliminarse.  
```php
add_filter('fonts_cleanup_extensions', function ($ext) {
    return ['svg','ttf','eot'];
});
```

---

## Desarrollo

- **SCSS**: se compila automáticamente al vuelo en el servidor usando [`scssphp`](https://scssphp.github.io/scssphp/).  
- **JS**: los archivos `main.js` y `admin.js` se crean automáticamente si no existen y se cargan en frontend/admin.  
- **Fonts**: añadir subcarpetas en `/fonts` con nombre de familia y pesos en formato `300.woff2`, `400i.woff`, etc.  
- **Functions**: cualquier archivo PHP en `/functions` del padre o hijo se carga automáticamente.

---

## Licencia

GPL-2.0 o posterior.  
Este tema está distribuido con la misma licencia que WordPress.

---

## Créditos

Desarrollado por **Samuel E. Cerezo**.  
© Grupo Orenes.
