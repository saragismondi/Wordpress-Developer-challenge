Herramienta: Claude Code (terminal), con otros modelos para el code review y la revisión de la documentación.

Cómo la usé: la IA leyó el código, ejecutó y verificó; las decisiones de alcance, prioridad y qué se arregla
o se documenta las tomé yo. Abajo va una selección de mis prompts, textuales y con fecha, extraídos de las
transcripciones de Claude Code. No incluyo las transcripciones completas ni las respuestas de la IA: omití lo
que no es trabajo técnico sobre el challenge. No usé CLAUDE.md en el repo; las reglas de trabajo quedaron en la
memoria de la herramienta.

Abajo va una selección de mis prompts, resumidos, con fecha, extraídos de las transcripciones de Claude Code.

----------------------------------------------------------------------
1. ARRANQUE Y REGLAS DE TRABAJO — 24/09
----------------------------------------------------------------------

[24/09 19:39]
> LEE EL RESPOSITORIO, SOLO LEELO, [...]

[24/09 20:02]
> [...] VOY A APROBAR CADA COSA QUE PONER EN EL PLAN. DAME EL PLAN DE NUEVO [...]

[24/09 20:04]
> [...] NO VAMOS A HACER NINGUN COMMIT , NADA AUN EN REMOTO,  TODO SE TRABAJA LOCAL Y CONTROLADO POR MI [...]  AL FINAL DE TODO SE ENTREGA REMOTO


----------------------------------------------------------------------
2. VERIFICACIÓN: DOCUMENTO DE CHEQUEO Y CODE REVIEW CON OTRO MODELO — 24/09
----------------------------------------------------------------------

[24/09 20:08]
> [...] ¿ME  ARMAS UN DOCU PARA CHEQUEAR LOS CAMBIOS QUE HICISTE CON MIS OJOS Y VER SI ESTA OK, ADEMAS DE LOS TEST QUE HAYA

[24/09 20:09]
> VAMOS A PONER OTRO MODELO QUE TE HAGA CODE REVIEW... 

[24/09 20:12]
> PORQUE NO CONSIDERASTE QE HACER UN QUERY DE 200 LLAMADOS ES IGUAL DE DENSA O LENTA ¿COMO LO ESTAS PENSANDO?


----------------------------------------------------------------------
3. ALCANCE: QUÉ SE ARREGLA Y QUÉ SE DOCUMENTA — 24/09
----------------------------------------------------------------------

[24/09 21:19]
> no renombres las funciones, trae problemas - solo documenta en finding

[24/09 21:24]
> [...] pimero veo una scroll horizontal, eso est mal seguro...

[24/09 21:27]
> si, levantá el original para comparar

[24/09 21:30]
> si, avanzá pero solo documentar


[24/09 21:51]
> DOCUMENTA LOS NUEVOS FINDINGS  PERO NO LOS ARREGLES


----------------------------------------------------------------------
4. TESTING MANUAL Y CONTROL CONTRA LA CONSIGNA — 25/09
----------------------------------------------------------------------

[25/09 17:53]
> ok al 1 y el 2 


[25/09 19:02]
> [...] COSAS QUE QUEDARON SIN TILDAR ¿LOS PIDE A TODOS OBLIGATORIOS EL ASSEMENT? 

[25/09 19:05]
> ok, hacé la 1 y la 3 

----------------------------------------------------------------------
5. REVISIÓN DE LA DOCUMENTACIÓN CON OTROS MODELOS — 25 y 26/09
----------------------------------------------------------------------

[25/09 23:08]
> AHORA REVISA QUE FINDINGS Y IA_LOG--- SEAN CONSISTENTES, COHERENTES Y NO HAYA CONTRADICCION O AMBUGUEDAD

[26/09 00:07]
> MIRA EL CODE REVIEW QUE TE HIZO CODEX

[26/09 00:37]  (las 4 reglas las propuso la IA; las adopté y las fijé para todos mis proyectos)
> PARA QUE NO REPITAS NUNCA MAS ESTO EN NINGUN PROYECTO GUARDA EN MEMORIA: [...] 1. Cada recomendación mía tiene que traer 4 datos, o no vale: - Qué pide la consigna, con la cita textual. - Evidencia: qué archivo leí o qué comando ejecuté, con el resultado. - Costo y riesgo. - Verificado sí/no. [...] 2. No cambio una recomendación sin un dato nuevo [...] 3. Vos decidís. [...] 4. Alcance congelado. [...] Cualquier review nuevo (de Codex o de otra IA) se contrasta con este mismo formato antes de tocar algo.


Todo el código fue revisado por mí, testeado y medido antes de entregarlo.
