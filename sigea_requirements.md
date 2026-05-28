# Especificación Técnica y Requerimientos del Sistema SIGEA

Este documento detalla los requerimientos, diseño, estructura, seguridad y arquitectura técnica del proyecto **SIGEA (Sistema de Generación y Evaluación Automática)**.

---

## 1. Requerimientos del Proyecto
El sistema está diseñado para digitalizar y automatizar el ciclo de vida de los exámenes académicos mediante tres perfiles principales:

- **Administrador**:
  - Control de accesos y gestión de la plataforma.
  - Creación y edición de cuentas de usuarios (Administradores, Docentes, Estudiantes).
  - Asignación de alumnos a sus respectivos docentes.
- **Docente**:
  - Gestión de bancos de preguntas organizados por materias y unidades temáticas (unidades 1 a 5).
  - Creación, configuración y publicación de exámenes.
  - Calificación, validación y consulta de estadísticas de rendimiento de los alumnos.
- **Estudiante**:
  - Visualización de exámenes asignados y pendientes.
  - Resolución de evaluaciones en tiempo real dentro de la plataforma.
  - Consulta de calificaciones e historial de exámenes realizados.
- **Módulo de Recuperación**:
  - Flujo de restablecimiento de contraseña seguro en 3 pasos autocontenidos en la sesión del usuario.

---

## 2. Estilo del Diseño (Liquid Crystal UI)
El diseño del sistema prioriza la estética premium, la limpieza visual y el uso de micro-animaciones fluidas:

- **Fondo de Pantalla**: Un gradiente dinámico y sutil que mezcla tonos azules y verdes pastel (`#f0f7ff`, `#e8f4ff`, `#f0fbf5`), animado mediante transiciones CSS suaves. No se emplean tonos morados ni violetas, y los círculos difuminados (*blobs*) tienen una opacidad extremadamente baja (< 0.09) para evitar distracciones.
- **Barra Lateral (Sidebar)**: De color azul marino sólido (`rgba(26, 53, 96, 0.95)` / `var(--navy)`) con contrastes en blanco para textos e iconos. Cuenta con un bloque destacado en la cabecera para el logo que tiene fondo blanco (`#ffffff`) y tipografía oscura para un aspecto moderno y corporativo.
- **Controles y Formularios**: Campos de entrada en color blanco puro (`#ffffff`) con bordes sólidos y definidos (`#c8d6e8`) que cambian a azul vívido con una sombra de enfoque al ser seleccionados.
- **Modales e Interacciones**: Modales con cabeceras de color semántico (azul para información, verde para éxito, ámbar para alertas) e iconos circulares. Los elementos interactivos presentan efectos de escala y sombreados (*glassmorphism*).

---

## 3. Tipografía y Recursos Visuales
- **Fuentes**: La tipografía del sistema es **Nunito** de Google Fonts (utilizada como alternativa de Google Sans), que proporciona un estilo redondeado, moderno y de fácil lectura.
- **Iconos**: Se utilizan los iconos vectoriales de la librería **Material Symbols Rounded** de Google Fonts para mantener consistencia y un aspecto limpio libre de emojis.

---

## 4. Estructura del Proyecto
El proyecto sigue una estructura limpia dividida por responsabilidades:

```
SIGEA/
├── app/
│   ├── Components/     # Elementos reutilizables de UI (sidebar, flash, footer)
│   ├── Core/           # Núcleo: Database, bootstrap, helpers, config
│   ├── Models/         # Clases de negocio y consultas (User.php, Exam.php)
│   └── Services/       # Servicios transversales (SimplePdf.php, Mailer.php)
├── database/
│   └── init.sql        # Esquema inicial y datos semilla SQL
├── PHPMailer/          # Librería nativa para el envío de correos
├── public/
│   ├── admin/          # Panel e interfaces del Administrador
│   ├── assets/         # Recursos públicos (CSS, JS, imágenes, logo)
│   ├── student/        # Panel e interfaces del Estudiante
│   ├── teacher/        # Panel e interfaces del Docente
│   ├── forgot-password.php # Recuperación de contraseñas
│   ├── index.php       # Pantalla de selección de rol
│   ├── login.php       # Formulario de inicio de sesión
│   └── logo.png        # Identidad visual de la plataforma
```

---

## 5. Tecnologías Usadas
- **Frontend**: HTML5 Semántico, CSS3 Vanilla (sin frameworks CSS para control total del diseño), JavaScript (ES6+).
- **Backend**: PHP 8.x nativo con arquitectura modular.
- **Base de Datos**: MySQL 8.0 utilizando la extensión **PDO** de PHP para consultas preparadas seguras.
- **Servidor y Despliegue**: Docker y Docker Compose para levantar los contenedores de la aplicación y la base de datos de manera uniforme.
- **Gestión de Correo**: **PHPMailer** para comunicación SMTP directa.

---

## 6. Reglas para Contraseñas
Las contraseñas de los usuarios deben cumplir estrictamente con los siguientes requisitos de complejidad validados por el sistema:
1. Longitud mínima de **8 caracteres**.
2. Incluir al menos una letra **mayúscula** (`A-Z`).
3. Incluir al menos un **número** (`0-9`).
4. Incluir al menos un **carácter especial** o símbolo no alfanumérico (ej. `@`, `$`, `*`, `.`, etc.).

---

## 7. Envío de Recuperación de Contraseña
- **Método**: Se realiza un envío de correo electrónico a través del protocolo SMTP seguro TLS.
- **Configuración SMTP**:
  - Servidor: `smtp.gmail.com`
  - Puerto: `587`
  - Encriptación: `STARTTLS`
  - Cuenta remitente: `jesusmutul15@gmail.com`
  - Contraseña de aplicación: `wypqbrdraohjqsat` (Contraseña de aplicación generada por Google).

---

## 8. Largo del Código de Verificación
- **Longitud**: El código enviado es de **6 dígitos numéricos** (ej. `492015`).
- **Expiración**: Tiene un tiempo de validez de **15 minutos** desde su generación.
- **Seguridad**: El flujo de recuperación está ligado a la sesión del navegador. Si el usuario cierra el navegador, recarga la página o hace clic en el enlace "Cancelar", la sesión de recuperación se destruye y el código queda invalidado, obligando al usuario a iniciar una nueva solicitud.

---

## 9. 15 Preguntas Vitales sobre el Proyecto (Simulación Desarrollador vs Cliente)

A continuación se presenta una entrevista simulada entre el **Desarrollador** (quien pregunta para relevar los requerimientos del sistema antes de construirlo) y el **Cliente** (quien responde detallando cómo desea que funcione la plataforma):

### **Pregunta 1**
**Desarrollador:** ¿El proyecto tendrá algún nombre oficial e identidad gráfica definida?
**Cliente:** Sí, queremos que se llame **SIGEA** (Sistema de Generación y Evaluación Automática) y ya tenemos un logo oficial (`logo.png`) listo para que lo incorporen en la plataforma.

### **Pregunta 2**
**Desarrollador:** ¿De qué manera ingresarán los usuarios a su rol correspondiente en la pantalla de bienvenida?
**Cliente:** Queremos que la pantalla de inicio presente accesos visuales claros para Estudiante y Docente, y una opción de texto más discreta para Administrador, permitiendo direccionar a cada uno a su respectivo formulario de acceso.

### **Pregunta 3**
**Desarrollador:** ¿De qué manera imaginan el flujo del docente para armar un examen a partir de sus preguntas?
**Cliente:** Queremos que el docente pueda seleccionar la materia y la unidad, ver la lista de sus preguntas guardadas, marcar con casillas cuáles desea incluir y asignarle un título. Con esto, el examen debería guardarse y estar disponible para sus alumnos.

### **Pregunta 4**
**Desarrollador:** Para el inicio de sesión, ¿qué debería pasar si un usuario introduce una contraseña incorrecta?
**Cliente:** Por comodidad del usuario, queremos que si falla el inicio de sesión, el correo electrónico ingresado no se borre del campo de texto. Así no tendrá que volver a escribirlo completo si se equivoca en la contraseña.

### **Pregunta 5**
**Desarrollador:** ¿Qué tipos de preguntas debería permitir gestionar el sistema en las evaluaciones?
**Cliente:** El sistema debe soportar dos tipos: preguntas de **opción múltiple** (con 4 opciones de respuesta donde solo una sea correcta) y preguntas de **verdadero/falso**.

### **Pregunta 6**
**Desarrollador:** En la recuperación de contraseña por código, ¿qué pasaría si el usuario recarga la página o intenta saltarse el paso del código?
**Cliente:** Queremos que la seguridad sea estricta. Si el usuario intenta refrescar la página, salirse o cerrar el proceso a mitad de la validación del código de 6 dígitos, el sistema debe borrar la sesión de recuperación inmediatamente para obligarlo a empezar de nuevo.

### **Pregunta 7**
**Desarrollador:** ¿Cómo debería ser la experiencia del estudiante al momento de realizar una evaluación asignada?
**Cliente:** El estudiante debería poder entrar a su panel, ver qué exámenes tiene pendientes y hacer clic en "Realizar examen". Las preguntas deben mostrarse de forma muy clara e interactiva y, al terminar, el estudiante debe poder enviar sus respuestas y ver su calificación al instante.

### **Pregunta 8**
**Desarrollador:** ¿Cómo gestionará el docente su banco de preguntas para cada materia?
**Cliente:** El docente deberá tener una pantalla donde seleccione la materia y la unidad, permitiéndole ingresar preguntas manualmente, clasificar si son de opción múltiple o de verdadero/falso, y definir sus respectivas respuestas.

### **Pregunta 9**
**Desarrollador:** ¿Los alumnos tendrán alguna forma de revisar sus notas e historial de exámenes anteriores?
**Cliente:** Sí, los estudiantes deben tener una sección de "Resultados" donde puedan consultar en cualquier momento su historial de exámenes resueltos y la calificación exacta que obtuvieron.

### **Pregunta 10**
**Desarrollador:** ¿Los profesores necesitarán aplicar estos exámenes de forma física en papel en algún momento?
**Cliente:** Sí, por ello queremos que el docente tenga la opción de descargar una versión limpia del examen en formato PDF de manera automática, lista para imprimirse.

### **Pregunta 11**
**Desarrollador:** ¿Cómo debería vincularse a los alumnos con sus respectivos profesores en la plataforma?
**Cliente:** Deseamos que el Administrador sea quien realice estas vinculaciones de manera visual desde su panel de control. De esta manera, cada profesor solo podrá evaluar y ver el progreso de los alumnos que tiene asignados.

### **Pregunta 12**
**Desarrollador:** Para la recuperación de cuentas, ¿el envío del código de verificación al correo se hará de forma automática?
**Cliente:** Sí, queremos que el sistema se conecte directamente a un servicio de correo electrónico y envíe el código de 6 dígitos al instante de forma automática al usuario, utilizando una cuenta Gmail institucional que les proporcionaremos.

### **Pregunta 13**
**Desarrollador:** ¿Qué tipo de información o estadísticas debería poder consultar el docente después de que sus alumnos hagan un examen?
**Cliente:** Queremos que el docente tenga una pantalla de resultados donde pueda ver estadísticas del examen, el promedio general obtenido por el grupo y una lista detallada con la nota de cada estudiante.

### **Pregunta 14**
**Desarrollador:** ¿Cómo les gustaría que los usuarios modifiquen sus datos personales o cierren su sesión?
**Cliente:** No queremos una página de perfil tradicional en el menú principal. Preferimos que la información del usuario se muestre al fondo del sidebar y, al hacer clic sobre ella, despliegue un popover o menú flotante con las opciones de "Editar perfil" y "Cerrar sesión".

### **Pregunta 15**
**Desarrollador:** ¿El usuario debe saber de inmediato si la contraseña que está ingresando cumple con las reglas de seguridad al momento de crear una nueva?
**Cliente:** Sí, queremos que la pantalla le muestre advertencias interactivas y dinámicas mientras escribe para indicarle si le faltan letras mayúsculas, números o signos/caracteres especiales, además de tener al menos 8 caracteres de longitud, y no debe dejarle enviar el formulario si no cumple con estas reglas.
