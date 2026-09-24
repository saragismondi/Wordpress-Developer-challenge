# Challenge técnico — WordPress Engineer

¡Bienvenido, y gracias por tomarte el tiempo de hacer este challenge! 🙌

## Contexto

Heredás **AgroNews**, un portal de noticias del sector agropecuario: WordPress,
tema clásico propio, un plugin a medida que arma la home y una barra de
cotizaciones. El equipo que lo hizo ya no está.

El sitio funciona, pero la home va lenta y el equipo editorial cuenta que "a
veces se cae". Nadie sabe bien por qué.

La tarea es la que te tocaría el día 1: entender qué pasa, priorizar y arreglar
lo que más impacta, sin romper lo que anda.

### Qué hay en el repo

- **Un tema clásico, `agronews`**, basado en Underscores y sin build step. La
  portada se compone de cuatro bloques, cada uno con su propia query y su propio
  template part:

  | Bloque | Qué muestra |
  | --- | --- |
  | Destacadas | 5 notas de una categoría configurable |
  | Últimas | las 10 notas más recientes |
  | Por sección | 3 categorías, 4 notas cada una |
  | Más leídas | 6 notas ordenadas por el meta `an_views` |

  Además hay una barra de cotizaciones, un widget de clima y un bloque de "temas
  del día" en el footer. Las notas tienen ficha de autor, relacionadas y links
  para compartir.

- **Un plugin, `agronews-home`**, con una página de ajustes que configura la
  categoría de cada bloque de la home y las siete cotizaciones. Trae su propia
  suite de PHPUnit.

- **Un seeder** que arma el sitio entero sin acceso a red: 12 secciones, 40
  etiquetas, 4 usuarios y 2.000 notas, cada una con su imagen destacada.

- **Un script de bench** que mide la home: TTFB, cantidad de queries y memoria.

- **Tooling**: PHP_CodeSniffer con WordPress-Extra y PHPCompatibilityWP, PHPUnit
  sobre la suite de tests de WordPress, y un workflow de CI que corre los dos.

## Setup

Todo corre con Docker, lo podés configurar desde esta carpeta:

```bash
bin/setup.sh    # levanta WordPress y lo instala
bin/seed.sh     # carga ~2.000 notas con imágenes, usuarios y configuración
bin/bench.sh    # mide la home: TTFB, cantidad de queries, memoria
```

Sitio en `http://localhost:8088`, admin `admin` / `admin`. El puerto se cambia
en `.env` si lo tenés ocupado.

Te conviene guardar la salida de `bench.sh` antes de empezar: te va a servir
como línea de base para comparar después.

Hay **8 tests de PHPUnit** que corrés con `bin/test.sh`, y la idea es que sigan
en verde. Si algún cambio tuyo los toca, podés ajustarlos: solo contanos el
razonamiento. El linter se corre con `bin/lint.sh`. Hay un `Makefile` con los
mismos comandos (`make setup`, `make seed`, `make bench`, `make test`,
`make lint`), y el detalle de todo está en [SETUP.md](SETUP.md).

> El workflow de CI y el template de Pull Request viven en el `.github/` de esta
> carpeta. GitHub solo lee workflows desde la raíz del repositorio, así que si
> querés que el CI te corra, subí `.github/` un nivel.

## Qué tenés que entregar

### 1. `FINDINGS.md`

El relevamiento. Por cada hallazgo: qué es (con `archivo:línea`), por qué
importa, severidad, y si lo resolviste o no, ordenado por prioridad y contando
con qué criterio lo ordenaste. Si algo queda sin resolver, contanos cómo lo
encararías: un hallazgo bien explicado también cuenta.

Además, nos gustaría que respondas estas tres preguntas en el mismo documento:

- **¿Qué pasa con la home si el servicio externo del que depende se pone lento o
  se cae?** Contanos el mecanismo.
- **Un editor carga una cotización como `1.234,50`. ¿Qué pasa y por qué?** Si lo
  arreglaste, ¿cómo evitarías que vuelva a pasar?
- **Este sitio publica 30–40 notas por día y corrige títulos en caliente. ¿Cómo
  lo cachearías en producción y qué se invalida cuando un editor publica o
  edita?** Capas, TTLs y el mecanismo de invalidación. No hace falta
  implementarlo entero, alcanza con que el razonamiento cierre.

### 2. Los arreglos

En commits atómicos, con [conventional commits](https://www.conventionalcommits.org/),
desde ramas `fix/…` o `refactor/…`. Cerrás con un Pull Request a `main` usando
el template del repo: qué cambiaste, por qué, cómo lo verificaste y qué riesgo
tiene.

Para la home, sumá la medición antes y después con `bin/bench.sh`, pegada en el
PR.

### 3. Log de uso de IA

`AI_LOG.md` o una carpeta `prompts/` con los prompts tal cual los escribiste,
transcripciones exportadas, y las reglas o archivos de contexto que hayas usado
(`CLAUDE.md`, `.cursorrules`, etc.). Nos sirve mucho más el material crudo que
un resumen prolijo. Trabajamos AI-first, así que nos interesa ver cómo la usás.

## Reglas

- Podés usar los plugins y herramientas que quieras. Contanos por qué elegiste
  cada dependencia nueva: el sitio lo mantiene un equipo chico y cada plugin es
  algo que después hay que sostener.
- No hace falta que quede perfecto. Si algo dejó de andar con un cambio tuyo,
  mencionalo y listo.
- Código y comentarios en inglés. `FINDINGS.md` y el PR, en español o inglés.

## Cómo lo evaluamos

En orden de peso:

1. El criterio con el que priorizaste.
2. Los problemas resueltos de verdad y medidos.
3. La calidad del código WordPress: APIs, hooks, escape, caché.
4. El proceso: commits, PR, tests en verde.

No hacemos revisión técnica en vivo, así que todo lo que quieras que veamos
conviene que esté escrito. Si pasás, la siguiente etapa es una entrevista con el
equipo.

## Entrega

Link al repo con acceso y link al PR, por mail.

Si algo de la consigna no se entiende o te trabás con el setup, escribinos sin
problema:

- Antonella Manzur — <antonella@braintly.com>
