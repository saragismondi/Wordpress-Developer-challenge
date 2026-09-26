# AI_LOG

**Herramienta:** Claude Code (terminal), con un segundo modelo para el code review.

**Cómo la usé:** la IA ejecutó y verificó; las decisiones de alcance, prioridad y qué se arregla o se documenta las tomé yo. Abajo están los prompts que fui usando en cada fase.

## Reglas que le fijé desde el inicio

> "Antes de arrancar: todo local. No toques git, ni commits, ni ramas, ni push, nada remoto, eso lo hago yo. No apliques ningún cambio sin que te dé el ok. Y si afirmás algo, mostrame dónde está en el código o medilo, no me digas 'probablemente'. Dame respuestas exactas, no ambiguas."

## Prompts por fase

**1. Entender el producto (24/09)**

> "Leé la consigna, el tema, el plugin y el seeder. No cambies nada todavía. Quiero entender cómo se arma la home, qué consulta hace cada bloque y de qué depende cada cosa (opciones, JSON, caché)."

**2. Entender las tareas y priorizar**

> "Ok, ahora listame todos los problemas que ves, pero ordenados. Ahora filtra por cuánto le pegan al usuario y a la estabilidad, sobre todo lo de 'a veces se cae'. No me los ordenes por lo fácil que son de arreglar. Para cada uno decime dónde está en el código y si se repite en otros files."

> "De esa lista estos los arreglamos, algunos issues los vamos a  fixiar y otros van documentados en FINDINGS.md. Hace una review por si se se me escapa algo."

**3. Revisar specs y armar el plan**

> "Antes de tocar código revisá cómo funcionan WP_Query, la Settings API y el object cache en esto que estamos haciendo. Armame un plan en pasos cortos, que cada uno se pueda verificar solo. Y corré bin/bench.sh ahora así tenemos la línea base antes de cambiar nada."

**4. Implementar y medir**

> "Vamos con el paso 1 nomás. Mostrame el diff antes de aplicarlo."

> "Aplicalo. Corré bin/test.sh y bin/lint.sh, después bench y comparalo con la línea base. Y compará el HTML de la home antes y después, tiene que dar idéntico byte a byte. Si algo cambia avisame, no lo 'arregles' por tu cuenta. guarda en memoria que es importante que no arrastres errores hasta el push, por eso primero review, y yo reviso y luego ejecutas y reviso again"

(y así con cada paso)

**5. Testing manual (25/09)**

> "Armame una guía de testing manual para revisar en el navegador: la home completa, el admin de cotizaciones probando 1.234,50, N/A y el campo vacío, qué pasa si el clima se cae (voy a renombrar el JSON) y las notas relacionadas. Pasos concretos y espectativa concreta de cada uno."

**6. Code review con otro modelo**

> "Revisá estos cambios en modo solo lectura, no edites nada. Hacé de tech lead escéptico: ¿cuánto cuestan las queries si no hay object cache? ¿Aguanta 3M visitas por mes? ¿Hay riesgo de estampida cuando expira la caché? ¿Hay algo sin escapar? ¿Me fui de alcance? Numerá los hallazgos y clasifica po severidad y yo te digo que fixeamos y que no."

> (de vuelta al modelo principal) "Te paso el review. Para cada hallazgo decime si es real o no, mostrando el código. Después decido yo qué se arregla."

**7. Decisiones sobre el review**

> "Del code review arreglá solo el aviso de error de cotizaciones, que muestra el texto del editor sin escaparlo. Los otros tres (precios con 3 decimales, negativos viejos y la página de Clima sin mensaje si fallan los 3 pronósticos) no los toques: dejalos documentados en FINDINGS.md con el porqué, son casos raros y quedan fuera del alcance."

**8. Cierre contra el checklist del PR**

> "Revisemos el checklist del template ítem por ítem. Arreglá el prefijo de las funciones de clima y el texto sin traducir; el test de queries queda documentado."

**9. Armar el historial de commits**

> "Trabajé todo en local y verificado. Ahora armame el plan de commits: agrupá los cambios por tema, un commit atómico por cada uno, con conventional commits, y que ningún commit deje el sitio roto si alguien hace checkout en ese punto. Los commits, el push y el PR los ejecuto yo."

Todo el código fue revisado, testeado y medido manualmente antes de entregarlo.