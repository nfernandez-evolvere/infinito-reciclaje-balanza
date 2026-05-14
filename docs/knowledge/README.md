# Base de conocimiento — Sistema de Gestión de Balanza
## Infinito Reciclaje × EVOLVERE 2026

Esta carpeta contiene la documentación de usuario del sistema. Está escrita en lenguaje no técnico, pensada para los usuarios reales del producto.

---

## A quién está dirigida

| Archivo | Dirigido a | Cuándo usarlo |
|---------|-----------|--------------|
| [configuracion-inicial.md](configuracion-inicial.md) | Admin (Nacho) | Antes del go-live — pasos para dejar el sistema listo para operar |
| [onboarding-operador.md](onboarding-operador.md) | Operador (Roberto y otros) | Primera semana de uso — cómo usar el sistema día a día |
| [onboarding-admin.md](onboarding-admin.md) | Admin (Nacho) | Primera semana de uso — cómo gestionar el sistema |
| [modulo-balanza.md](modulo-balanza.md) | Operador | Referencia del módulo de registro de pesajes |
| [modulo-abms.md](modulo-abms.md) | Admin | Cómo gestionar vehículos, orígenes, servicios, tipos y usuarios |
| [modulo-dashboard.md](modulo-dashboard.md) | Admin | Cómo leer e interpretar el panel de control |
| [modulo-pesajes-admin.md](modulo-pesajes-admin.md) | Admin | Cómo revisar, editar y auditar pesajes |
| [modulo-reportes.md](modulo-reportes.md) | Admin | Cómo generar y exportar reportes |
| [modulo-alarmas.md](modulo-alarmas.md) | Admin | Cómo funcionan las alarmas y cómo configurarlas |

---

## Notas para uso en RAG / chatbot

Estos archivos están estructurados para ser ingestados por un sistema de recuperación semántica:

- **Cada archivo es autocontenido** — se puede leer sin contexto de los demás.
- **Encabezados descriptivos** (`## Cómo registrar un pesaje`) en lugar de genéricos (`## Paso 1`) — mejoran la recuperación por similitud semántica.
- **FAQs al final de cada módulo** — cubren las preguntas reales que surgen en el uso diario.
- **Rol explícito al inicio** — permite filtrar por audiencia en el sistema de RAG.
- **Sin jerga técnica** — ningún archivo menciona controladores, migraciones, SQL ni términos de programación.

Para indexar, tratar cada sección `##` como un chunk independiente.

---

*Base de conocimiento generada: 12/05/2026*
