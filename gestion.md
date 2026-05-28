# Gestión del Proyecto SIGEA

Este documento detalla las 6 tareas principales del desarrollo del sistema **SIGEA** para el seguimiento y control del proyecto.

---

### Tarea 1: Login del sistema
**Descripción**: Módulo para la autenticación segura de usuarios mediante roles predefinidos (administrador, docente y estudiante), el cual incluye la retención del correo electrónico en el campo de texto en caso de cometer errores en la contraseña para optimizar la experiencia de uso.
- Diseñar interfaz del login
- Implementar validación de credenciales en la bd
- Implementar persistencia del correo electrónico ante errores
- Diseñar pantalla de selección de rol inicial

---

### Tarea 2: Barra de navegación y perfil
**Descripción**: Menú lateral azul marino con un contenedor de logo blanco y botones dinámicos para cambiar de sección. En la parte inferior, muestra un botón del usuario que abre un menú flotante para editar datos básicos o cerrar la sesión activa.
- Diseñar barra lateral responsiva
- Implementar menú flotante del usuario (popover)
- Implementar redirección de cerrar sesión
- Diseñar formulario de edición de datos personales

---

### Tarea 3: Módulo de banco de preguntas
**Descripción**: Panel que permite a los docentes poblar, clasificar y actualizar sus preguntas según materia y unidad temática. Ofrece soporte para la estructuración de reactivos en formatos de opción múltiple con cuatro respuestas o bien cuestionamientos de verdadero y falso.
- Diseñar interfaz del banco de preguntas
- Implementar registro de preguntas en la bd
- Implementar categorización por unidades y materias
- Diseñar formulario para opciones y respuestas correctas

---

### Tarea 4: Generación y asignación de exámenes
**Descripción**: Herramienta interactiva para que los docentes seleccionen reactivos específicos de su banco de preguntas, definan un título y publiquen la evaluación. Permite a los administradores asignar estudiantes de forma visual a cada docente para segmentar la visualización del grupo.
- Diseñar interfaz para la creación de exámenes
- Implementar guardado de exámenes en la bd
- Diseñar panel visual de asignación de estudiantes
- Implementar vinculación de estudiantes con docentes en la bd

---

### Tarea 5: Resolución de exámenes y calificaciones
**Descripción**: Interfaz interactiva para que los estudiantes visualicen sus exámenes pendientes, contesten las preguntas y envíen las respuestas. El sistema calcula de forma automática la puntuación obtenida y la almacena de inmediato en la sección de historial de resultados.
- Diseñar interfaz de resolución del examen
- Implementar cálculo de puntuación automática
- Diseñar historial de resultados del estudiante
- Implementar registro de intentos y respuestas en la bd

---

### Tarea 6: Módulo de recuperación de contraseña
**Descripción**: Mecanismo de restablecimiento de contraseña autocontenido en la sesión del usuario. Consiste en una pantalla de tres pasos donde el usuario ingresa su correo, valida un código numérico de seis dígitos recibido por email y define sus nuevas credenciales seguras.
- Diseñar interfaz del asistente de tres pasos
- Implementar envío de código de verificación de 6 dígitos por correo
- Implementar validación del código y expiración de sesión temporal
- Implementar actualización y cifrado de la nueva contraseña en la bd
