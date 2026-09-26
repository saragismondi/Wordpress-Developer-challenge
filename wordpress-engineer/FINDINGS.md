# FINDINGS — AgroNews

Relevamiento del portal heredado: qué encontré, por qué importa, qué resolví y cómo encararía lo que quedó pendiente. Las referencias `archivo:línea` apuntan al código **original** (antes de los cambios), salvo los hallazgos 8 y 15, que están en código nuevo de este cambio y apuntan al código modificado.

La tabla y el detalle siguen el orden de prioridad: primero la severidad y, dentro de cada severidad, el criterio de abajo.

## Criterio de priorización

Ordené por impacto sobre el negocio, en este orden:

1. **Disponibilidad**: lo que puede tirar el sitio ("a veces se cae"). Un portal caído no vende publicidad ni retiene lectores.
2. **Latencia que percibe el lector** en la home, que es la página con más tráfico.
3. **Integridad de datos que ve el público**: una cotización mal mostrada en un portal agropecuario es un error editorial visible.
4. **Eficiencia y calidad de código**: queries, caché, markup, i18n.

Dentro de cada nivel, primero lo que tiene mejor relación impacto/riesgo del cambio.

## Resumen de hallazgos

| # | Hallazgo | Dónde | Severidad | Estado |
| --- | --- | --- | --- | --- |
| 1 | Pedido HTTP bloqueante, sin timeout ni caché, en cada render de la home | `themes/agronews/inc/weather.php:19` | Crítica | Resuelto |
| 2 | Cotizaciones guardadas como texto libre: `1.234,50` se muestra como `1` y un valor no numérico rompe la home | `plugins/agronews-home/includes/settings.php:406`, `themes/agronews/template-parts/block-quotes.php:32` | Alta | Resuelto |
| 3 | N+1 de queries: se apaga la caché de términos y meta y después cada tarjeta consulta categoría, miniatura y alt por separado | `themes/agronews/inc/blocks.php:114-115`, `inc/template-tags.php:167-173`, `template-parts/block-most-read.php:30` | Alta | Resuelto |
| 4 | No hay ninguna capa de caché (página, fragmentos ni object cache) | todo el tema | Alta | No resuelto (ver pregunta 3) |
| 5 | Bloques partidos en dos queries (lead + `offset`) y sin `no_found_rows` | `template-parts/block-featured.php:11-19`, `template-parts/block-sections.php:25-33`, `inc/blocks.php:109-116` | Media | Resuelto |
| 6 | "Más leídas" ordena por `meta_value_num` sobre todas las notas en cada request | `themes/agronews/inc/blocks.php:137-139` | Media | No resuelto |
| 7 | "Relacionadas" con `orderby => rand` | `themes/agronews/inc/blocks.php:188` | Media | No resuelto |
| 8 | El parser de cotizaciones lee como miles cualquier número con punto y exactamente 3 decimales (`238.100` → 238100), no solo `1.234`; además acepta `0` como cotización válida | `plugins/agronews-home/includes/settings.php:458` (código nuevo) | Media | No resuelto (documentado) |
| 9 | Imágenes armadas a mano: sin `srcset`/`sizes`, sin `width`/`height` (CLS), lead con `loading="lazy"` (LCP) y tamaño por defecto `medium` en vez de `agronews-card` | `themes/agronews/inc/template-tags.php:109,160,178` | Media | No resuelto |
| 10 | La imagen de la nota destacada se agranda y se recorta: pierde un tercio de su alto y se ve borrosa | `themes/agronews/css/portal.css:213-216`, `inc/template-tags.php:109` | Media | No resuelto (documentado) |
| 11 | La opción `wp_page_for_privacy_policy` apunta a una página borrada: cada ítem del menú la vuelve a consultar (7 queries de más en la home) | `bin/setup.sh` (borra `privacy-policy`), core `get_privacy_policy_url()` | Baja | No resuelto (documentado) |
| 12 | "Temas del día": `get_terms` ordenado por `count` en cada página (está en el footer) | `themes/agronews/inc/blocks.php:220-228` | Baja | No resuelto |
| 13 | La barra de cotizaciones muestra 0 decimales (`1040.25` → `1.040`) | `template-parts/block-quotes.php:32` | Baja | No resuelto (decisión de producto) |
| 14 | La barra de cotizaciones esconde la mayoría de los valores detrás de un scroll horizontal interno, sin ninguna señal de que hay más | `themes/agronews/css/portal.css:93-109` | Baja | No resuelto (documentado) |
| 15 | El aviso de cotización inválida interpolaba el valor tipeado con `sanitize_text_field()` pero sin `esc_html()` | `plugins/agronews-home/includes/settings.php:415-423` (código nuevo) | Baja | Resuelto |
| 16 | Texto fijo sin traducir: "Clima no disponible"; hint del admin en español ("Ej: 285000") | `template-parts/block-weather.php:18`, `includes/settings.php:344` | Baja | Resuelto |
| 17 | `an_weather_home()` y `an_weather_province()` no llevaban el prefijo `agronews`: `bin/lint.sh` daba 2 errores (`PrefixAllGlobals`) | `themes/agronews/inc/weather.php:18,40` | Baja | Resuelto |

## Mediciones

Home (`front-page.php`) con el seed completo (2000 notas), `bin/bench.sh` (10 corridas después de 2 de calentamiento), Docker Desktop sobre Windows 11.

| | TTFB mediana | TTFB p95 | Queries | Tiempo en queries |
| --- | --- | --- | --- | --- |
| Antes | 1900,7 ms | 1978,0 ms | 264 | 151,6 ms |
| Después | 150,6 ms | 199,7 ms | 71 | 50,8 ms |

- El HTML de la home es **idéntico byte a byte** antes y después (63.398 bytes): los cambios no alteran lo que ve el lector.
- `bin/test.sh`: 10 tests, 44 assertions, todos en verde.
- `bin/lint.sh`: 0 errores. El original daba 2 (hallazgo 17, resuelto).
- De las 71 queries que quedan, 7 corresponden al hallazgo 11 y el resto son las esperables de WordPress: unas 7 por bloque (query, meta, términos, miniaturas) más las de core.

## Detalle

### 1. Clima: pedido HTTP bloqueante en la home — Crítica — Resuelto

`an_weather_home()` hacía `file_get_contents()` contra `https://www.agronews.example/.../buenos-aires.json`, es decir, el JSON **del propio tema** pedido de vuelta por la red pública. Sin timeout, sin caché y en cada render de la home. En el entorno ese round trip tarda alrededor de 1,6 s y explicaba casi todo el TTFB de la home: 1,90 s de mediana, contra 152 ms de queries.

**Arreglo:** `an_weather_home()` reutiliza `an_weather_province( 'buenos-aires' )`, que ya existía en el mismo archivo y lee el JSON desde disco con validación y caché estática. El JSON viaja con el deploy del tema, así que se mantiene la intención original ("el widget toma el pronóstico nuevo apenas se despliega") sin pasar por la red.

Si en el futuro el pronóstico viniera de un proveedor externo real, lo haría con `wp_remote_get()` con timeout corto (2 s), la respuesta guardada en un transient y una copia "última buena" que se sirve si el proveedor falla. Idealmente se refresca por cron, fuera del request del lector.

### 2. Cotizaciones con formato local — Alta — Resuelto

El sanitize guardaba el texto tal cual (`sanitize_text_field`) y la plantilla le pasaba ese string a `number_format()`. Ver la pregunta 2 para el mecanismo.

**Arreglo:**
- `agronews_home_parse_quote()` normaliza al guardar. Acepta `1.234,50`, `1.234`, `1234,5` y `512.35` (el formato que ya usaban el seeder y los tests), y guarda un decimal con punto.
- Si el valor es ambiguo o no numérico, **se rechaza**: se conserva el valor anterior y el editor ve un aviso con `add_settings_error()`.
- `agronews_home_get_quotes()` descarta valores no numéricos que ya estuvieran guardados, y la plantilla castea a `float`. Un dato viejo nunca vuelve a romper la home.
- Tests nuevos en `tests/test-settings.php`.

### 3. N+1 de queries en las tarjetas — Alta — Resuelto

`agronews_get_block_query_args()` desactivaba `update_post_term_cache` y `update_post_meta_cache`, pero `agronews_render_card()` usa `get_the_category()`, `get_post_thumbnail_id()` y el alt de la imagen, y "Más leídas" usa `get_post_meta()`. Cada tarjeta disparaba varias queries propias: con ~45 tarjetas en la home, eso explica la mayor parte de las 264 queries.

**Arreglo:** las cachés de términos y meta quedan activas (una query por bloque para cada una) y se llama a `update_post_thumbnail_cache()` después de cada query de bloque, lo que precarga en bloque los attachments y su meta. Lo mismo en `agronews_get_related_query()` (página de nota).

### 5. Queries partidas y conteo innecesario — Media — Resuelto

"Destacadas" pedía 1 nota y después 4 con `offset`; "Por sección" hacía lo mismo por cada categoría, y en ese bloque las dos listas se dibujan igual. Ahora cada bloque hace una sola query y la primera nota del loop es la destacada. Todas usan `no_found_rows` porque ningún bloque pagina, así que se evita el `SQL_CALC_FOUND_ROWS`.

### Resto de los hallazgos (4 y 6 a 17): cómo encararía los pendientes

Los hallazgos 15, 16 y 17 quedaron resueltos; el resto, documentado.

- **4. Sin caché:** es la mejora de mayor impacto que queda. Está desarrollada en la pregunta 3.
- **6. Más leídas:** ordenar por meta sobre todas las notas es caro y el ranking cambia poco minuto a minuto. Cachearía la lista de IDs en un transient de 10 minutos, o la escribiría el mismo job de analytics que actualiza `an_views`. A futuro, contador en tabla propia o en la herramienta de analytics.
- **7. Relacionadas con `rand`:** `ORDER BY RAND()` recorre toda la categoría en cada nota y no se puede cachear. Guardaría en un transient los IDs de las ~30 notas más recientes de cada categoría, invalidado al publicar en esa categoría, y elegiría 4 al azar en PHP.
- **8. Ambigüedad del parser:** la regla "punto con grupos de 3 dígitos = miles" (`^\d{1,3}(?:\.\d{3})+(?:,\d+)?$`) es la que permite que `1.234,50` y `1.234` funcionen, pero también toma `238.100` como 238100 y no como 238,1, sin avisarle al editor. Es una ambigüedad propia del formato: sin más contexto, `238.100` puede ser las dos cosas. También se acepta `0`, que la barra muestra como cotización. No hay tests para estos bordes ni para `-5` (que se rechaza). Lo encararía así: definir con el equipo editorial un solo formato (el local, `1.234,50`) y rechazar un punto con 3 decimales sin coma, con un aviso que pida confirmar; decidir si `0` es válido; y sumar tests para `238.100`, `0` y `-5`. Hay otra asimetría: al guardar se rechazan los negativos, pero la lectura (`agronews_home_get_quotes()`, que solo pide `is_numeric`) deja pasar un negativo guardado antes de este cambio. Se cierra agregando `$value >= 0` a esa condición. Por último, el refactor de queries de los bloques quedó respaldado por el diff de HTML y el conteo de queries, pero no tiene test automático. Sumaría un test de integración de `agronews_get_block_query()` que verifique cantidad y orden.
- **9. Imágenes:** usar `wp_get_attachment_image()` o `the_post_thumbnail()` con el tamaño registrado (`agronews-card` / `agronews-lead`), que agrega `srcset`, `sizes`, `width` y `height`, y `fetchpriority="high"` sin lazy en la imagen principal.
- **10. Imagen destacada recortada:** la plantilla pide el tamaño `large`, que no existe para imágenes de 800×450, así que WordPress sirve el original. La regla `.an-card--lead .an-card__media img` le da `width: 100%`, `max-height: 420px` y `object-fit: cover`. Medido en un navegador de 1521 px: la imagen de 800×450 se muestra a 1160 px de ancho, o sea agrandada 1,45 veces, y de los 652 px de alto que le corresponderían se ven 420. Se pierde el 36 % de la imagen, repartido arriba y abajo. En el seed se nota porque se cortan los textos "AGRONEWS" y el número. Con fotos reales se cortarían cabezas o el sujeto de la foto, y el agrandamiento la deja borrosa. El recorte empieza con más de 747 px de ancho (420 × 16/9), o sea en cualquier escritorio; en celular no hay recorte. El comentario del CSS dice que el recorte es intencional, para no empujar el resto de la home. Lo encararía así: usar el tamaño registrado `agronews-lead` con `wp_get_attachment_image()` (que suma `srcset`), registrar un tamaño más ancho para escritorio y reemplazar el `max-height` por un `aspect-ratio` fijo acordado con diseño (por ejemplo 21:9), con `object-position` si hace falta. Va junto con el hallazgo 9.
- **11. Página de privacidad inexistente:** `bin/setup.sh` borra la página `privacy-policy` pero no limpia la opción `wp_page_for_privacy_policy`. `get_privacy_policy_url()` se evalúa por cada ítem del menú, y como un post inexistente no queda en caché sin object cache, cada llamada repite `SELECT * FROM wp_posts WHERE ID = 3`. No lo toqué porque es parte del entorno. Si en producción pasara lo mismo, el arreglo es poner la opción en `0` al borrar la página (o asignar una página válida), sin cambios de código.
- **12. Temas del día:** transient de 1 hora con la lista de términos.
- **13. Decimales:** lo dejaría a definición editorial. Dólar y granos suelen publicarse con 2 decimales.
- **14. Barra de cotizaciones:** `.an-quotes` tiene `overflow-x: auto` y la lista usa `white-space: nowrap`. La página no se desborda: medí que el documento no tiene scroll horizontal ni en 1521 px ni en 372 px. Lo que aparece es un scroll dentro de la barra. En escritorio faltan 23 px, así que el último valor sale cortado ("ARS 1.2…"). En celular la barra muestra 332 px de 1183: queda oculto el 72 % de las cotizaciones y la única pista es la barra de scroll del sistema, que en muchos celulares ni se ve. Lo encararía con diseño: que la lista pase a varias líneas (`flex-wrap: wrap`) o un degradado en el borde que indique que hay más, y en celular mostrar solo las cotizaciones principales.
- **15. Escape del aviso:** `add_settings_error()` recibía el valor tipeado pasado por `sanitize_text_field()`, que limpia la entrada pero no escapa la salida, y el core imprime el mensaje sin volver a escaparlo. Solo lo podía aprovechar un usuario con `manage_options` contra sí mismo, pero era código nuevo de este cambio y no cumplía la regla de escapar al mostrar. Resuelto: el valor y la etiqueta pasan por `esc_html()` antes de armar el mensaje.
- **16. i18n:** resuelto. El texto de reemplazo del clima pasa por `esc_html_e( 'Weather not available', 'agronews' )`, en inglés como el resto del front y traducible con el text domain del tema. Queda pendiente algo relacionado: en la categoría clima (`category.php`), si fallan los JSON de las 3 provincias, el contenedor queda vacío y sin mensaje. Iría el mismo texto de reemplazo que en la home.
- **17. Prefijo de funciones:** resuelto. Pasaron a llamarse `agronews_weather_home()` y `agronews_weather_province()`, y actualicé los únicos llamadores del repo (`template-parts/block-weather.php` y `category.php`). `bin/lint.sh` queda en 0 errores. Si hubiera código fuera del repo que llame a los nombres viejos (plugins, child theme o snippets en producción), la alternativa es dejarlos un release como wrappers con `_deprecated_function()`.

## Preguntas

### ¿Qué pasa con la home si el servicio externo se pone lento o se cae?

Mecanismo, antes del arreglo:
1. Cada render de la home ejecutaba `file_get_contents()` sincrónico contra la URL pública. PHP no sigue hasta que termina ese pedido.
2. `file_get_contents()` no tenía timeout propio, así que regía `default_socket_timeout` (60 s por defecto). Si el servicio tarda, cada request a la home queda bloqueado hasta 60 s ocupando un proceso de Apache/PHP.
3. Los procesos son finitos (`MaxRequestWorkers`) y **compartidos por todo el sitio**. Con tráfico de portal, en segundos se ocupan todos esperando al clima, los nuevos requests se encolan y el sitio entero, no solo la home, deja de responder: el "a veces se cae".
4. Si el servicio directamente no responde, cada home tarda el timeout completo y muestra "Clima no disponible". La degradación funcional está, pero la de performance no.
5. Además, en condiciones normales cada home pagaba el round trip completo (1,6 s en este entorno), porque no había caché.

Con el arreglo, la home ya no depende de la red para el clima. La regla general: ninguna dependencia externa en el camino crítico del render sin timeout corto, caché y fallback a la última respuesta buena.

### Un editor carga una cotización como `1.234,50`. ¿Qué pasa y por qué?

Antes del arreglo:
- El valor se guardaba como texto, sin convertirlo a número.
- En la home, `number_format()` recibe el string. PHP 8 lo convierte a número tomando solo el **prefijo numérico válido**: `1.234` (el punto se interpreta como separador decimal y la coma corta la lectura). Emite un warning y formatea `1.234` con 0 decimales: **la barra muestra "1" en vez de "1.235"**, sin ningún error visible para el editor.
- Peor: si el valor no empieza con un número (`USD 1.234`, `N/A`, `—`), PHP 8 lanza un **TypeError** y la home entera muestra un error fatal. Es otra causa posible de "a veces se cae", disparada desde el admin.

Cómo evito que vuelva a pasar:
- **Validar en la entrada:** se normaliza al guardar con reglas explícitas del formato local, y lo ambiguo se rechaza con un aviso al editor en vez de adivinar.
- **Guardar en formato canónico:** decimal con punto. La presentación se resuelve al mostrar.
- **Render tolerante:** la capa de lectura ignora lo que no sea numérico, así un dato viejo o importado no rompe la página.
- **Tests** que fijan los casos (`1.234,50`, `1.234`, `238,1`, `N/A`, valor previo conservado).
- Si las cotizaciones pasaran a cargarse desde una fuente automática, aplicaría el mismo parser en esa entrada.

### Caché en producción con 30–40 notas por día y títulos corregidos en caliente

El volumen de escritura es bajo (una publicación cada ~20–30 minutos en horario de redacción), así que conviene **cachear agresivo e invalidar por evento**, no depender de TTLs cortos.

**Capas:**

1. **CDN / caché de página completa** (edge, Varnish o Nginx FastCGI cache). Sirve el HTML a anónimos, que son casi todo el tráfico.
   - Home: `s-maxage=300` más `stale-while-revalidate=60` y `stale-if-error=86400`.
   - Nota: 1 h. Archivos de categoría, tag y autor: 10 min.
   - Se saltea para usuarios logueados y con cookies de comentario, y nunca cachea `wp-admin` ni previews.
2. **Object cache persistente** (Redis o Memcached).
   - Cachea opciones, términos, usuarios y, desde WP 6.1, los resultados de `WP_Query`, invalidados por `last_changed`.
   - Encima, fragmentos caros en transients: más leídas (10 min), temas del día (1 h), IDs de relacionadas por categoría.
   - **Sin servidor de object cache**, los transients van a `wp_options`: siguen convirtiendo decenas de queries en una, así que igual suman, pero en producción recomendaría Redis.
3. **Navegador**:
   - CSS y JS versionados (`AGRONEWS_VERSION`) con `max-age` largo e `immutable`.
   - Imágenes con `max-age` largo.
   - HTML con `max-age` corto; el que manda es el edge.

**Invalidación**, con hooks en `transition_post_status` (publicar, despublicar) y `post_updated` sobre notas publicadas (corrección de título):

- Purgar la URL de la nota, la home, los archivos de sus categorías y tags, el archivo de su autor y los feeds. Donde se vea el título, se purga.
- Invalidar los transients de fragmentos afectados: relacionadas de esa categoría y, si cambió el título, las listas que lo muestran.
- En el object cache, `WP_Query` se invalida solo, porque `clean_post_cache()` actualiza `last_changed`.
- Con 30–40 notas por día son pocas purgas y todas puntuales, no un vaciado total.

**Protección contra estampida:** purga *soft* (marcar como vencido y seguir sirviendo lo viejo mientras se regenera) o `stale-while-revalidate`, más un lock corto al regenerar fragmentos, para que un pico después de publicar no mande cientos de requests al origen a la vez.

**TTLs como red de seguridad:** con purga por evento, el TTL solo cubre el caso de que una purga falle, así que puede ser generoso. Los 5 minutos de la home acotan el peor caso de un título corregido que no se purgó.
