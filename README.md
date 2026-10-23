video explicación: https://drive.google.com/file/d/16WO2qut31mJnKd2oPXL5ZReHeiavH3q1/view?usp=sharing
Enlace en youtube: https://youtu.be/7lYiIpEYpn8?si=STIAp7AZKr0ytJft

# 📚 TutoresOnLine

## 🚀 Historia y Proceso de Desarrollo

El proyecto **TutoresOnLine** nació como una solución integral para optimizar la conexión entre estudiantes que requieren refuerzo académico y tutores especializados. A continuación, se detalla la evolución de su creación:

### 1. Fase de Concepción y Diseño (Arquitectura y Base de Datos)
El desarrollo comenzó con la definición de los requerimientos funcionales y el diseño de la arquitectura relacional. Se estructuró un modelo de base de datos robusto compuesto por 7 tablas principales para garantizar la integridad referencial y la separación de roles:
- **`usuarios`**: Gestión centralizada de credenciales, roles diferenciados ("Estudiante" y "Tutor") y metadatos de registro.
- **`perfiles_tutores`**: Almacenamiento de biografía, tarifas por hora, calificaciones promedio y modalidades preferidas.
- **`materias`** y **`tutores_materias`**: Relación de asignaturas disponibles y su asignación directa a cada profesional.
- **`horarios_disponibles`**: Gestión de disponibilidad horaria para agendamiento.
- **`reservas_sesiones`**: Control de citas solicitadas, aceptadas o canceladas.
- **`resenas_calificaciones`**: Sistema de retroalimentación posterior a cada tutoría.

### 2. Implementación del Stack Tecnológico
Para el desarrollo se seleccionaron tecnologías estándar de la industria orientadas a entornos web dinámicos:
- **Backend y Conexión:** PHP con consultas seguras mediante PDO (PHP Data Objects) para prevenir inyecciones SQL y asegurar sesiones robustas.
- **Frontend y Estilos:** HTML5, JavaScript y hojas de estilo centralizadas (`CSS/estilos.css`) enfocadas en un diseño limpio y responsivo.
- **Entorno de Servidor:** XAMPP (Apache y MySQL) para el despliegue local.

### 3. Construcción de Módulos Funcionales
Se implementaron 8 módulos principales que cubren el ciclo de vida completo de la plataforma:
1. Autenticación y control de sesiones seguras por roles.
2. Panel de control personalizado para estudiantes (**Dashboard Estudiante**).
3. Panel de control e indicadores de solicitudes para tutores (**Dashboard Tutor**).
4. Módulo de configuración y actualización de perfiles profesionales.
5. Explorador y catálogo de materias y categorías.
6. Sistema de agendamiento de horarios y reservas de sesiones.
7. Módulo de gestión y visualización de archivos compartidos.
8. Sistema de calificaciones y reseñas.

### 4. Control de Versiones y Despliegue en Entornos Académicos
El código fuente fue centralizado y versionado mediante Git, configurando un repositorio público en GitHub (`https://github.com/Omitar380/tutoriasonline.git`). Posteriormente, se realizaron pruebas de integración e importación de la base de datos en los equipos de cómputo del aula universitaria, resolviendo incidencias de mapeo de llaves foráneas (`id_tutor`), configuración de esquemas relacionales y dependencias de ejecución en Apache/MySQL.

---

## 🛠️ Tecnologías Utilizadas
- **PHP** (con PDO)
- **MySQL / phpMyAdmin**
- **HTML5 / CSS3 / JavaScript**
- **XAMPP Server**
- **Git y GitHub**

## 👥 Autores y Colaboradores
Proyecto desarrollado para la entrega del segundo parcial de ingeniería de sistemas por:
- Omar Navarro
- Dilan Zapata
- Handt Torres
