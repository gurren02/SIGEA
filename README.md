# SIGEA

Sistema de Generacion y Evaluacion automatica de examenes, construido con PHP, MySQL y Docker.

## Ejecutar con Docker

```bash
docker compose up -d --build
```

Abrir: http://localhost:8080

Para reinstalar la base desde cero y volver a cargar todos los datos semilla:

```bash
docker compose down -v
docker compose up -d --build
```

## Cuentas iniciales

| Rol | Correo | Contrasena |
| --- | --- | --- |
| Administrador | admin1@sigea.test | Pass12345* |
| Administrador | admin2@sigea.test | Pass12345* |
| Administrador | admin3@sigea.test | Pass12345* |
| Docente | docente1@sigea.test | Pass12345* |
| Docente | docente2@sigea.test | Pass12345* |
| Docente | docente3@sigea.test | Pass12345* |
| Estudiante | estudiante1@sigea.test | Pass12345* |
| Estudiante | estudiante2@sigea.test | Pass12345* |
| Estudiante | estudiante3@sigea.test | Pass12345* |

## Datos semilla

- Materias: CALCULO DIFERENCIAL, CALCULO INTEGRAL, CALCULO VECTORIAL, FUND PROGRAMACION, ESTRUCTURA DE DATOS, DESARR FRONTEND, INTELIGENCIA ARTIF, QUIMICA, ADM BD, PROG WEB, DES BACK END, ING SOFTWARE.
- Cada docente tiene banco de preguntas precargado con 20 preguntas por unidad, de la UNIDAD 1 a la 5, para cada materia.
- Las preguntas semilla usan textos ASCII sin acentos ni signos conflictivos, con respuestas y distractores relacionados con la materia y unidad.
- Asignaciones iniciales: `docente1` con `estudiante1`, `docente2` con `estudiante2`, `docente3` con `estudiante3`.

## Estructura

- `app/Core`: conexion, bootstrap y helpers.
- `app/Models`: operaciones de usuarios y examenes.
- `app/Services`: servicios transversales, como PDF.
- `app/Components`: layout reutilizable.
- `public`: vistas publicas y modulos por rol.
- `database/init.sql`: esquema y datos semilla.
