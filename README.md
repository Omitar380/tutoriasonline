## Base de datos

El proyecto utiliza MySQL para gestionar la información de TutoresOnLine.
-- 1. Tabla de Usuarios (Estudiantes y Tutores)
CREATE TABLE Usuarios(
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('Estudiante', 'Tutor') NOT NULL,
    ciudad VARCHAR(100),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- 2. Tabla de Perfiles de Tutores
CREATE TABLE Perfiles_Tutores(
    id_tutor INT PRIMARY KEY,
    biografia TEXT,
    tarifa_por_hora DECIMAL(10, 2) NOT NULL,
    calificacion_promedio DECIMAL(3, 2) DEFAULT 0.00,
    modalidad_preferida ENUM('Virtual', 'Presencial', 'Ambas') DEFAULT 'Ambas',
    FOREIGN KEY(id_tutor) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);
-- 3. Tabla de Materias
CREATE TABLE Materias(
    id_materia INT AUTO_INCREMENT PRIMARY KEY,
    nombre_materia VARCHAR(100) UNIQUE NOT NULL,
    categoria VARCHAR(100)
);
-- 4. Tabla Intermedia: Tutores y Materias (Relación Muchos a Muchos)
CREATE TABLE Tutores_Materias(
    id_tutor INT,
    id_materia INT,
    PRIMARY KEY(id_tutor, id_materia),
    FOREIGN KEY(id_tutor) REFERENCES Perfiles_Tutores(id_tutor) ON DELETE CASCADE,
    FOREIGN KEY(id_materia) REFERENCES Materias(id_materia) ON DELETE CASCADE
);
-- 5. Tabla de Horarios Disponibles del Tutor
CREATE TABLE Horarios_Disponibles(
    id_horario INT AUTO_INCREMENT PRIMARY KEY,
    id_tutor INT NOT NULL,
    dia_semana ENUM(
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo'
    ) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY(id_tutor) REFERENCES Perfiles_Tutores(id_tutor) ON DELETE CASCADE
);
-- 6. Tabla de Reservas de Sesiones
CREATE TABLE Reservas_Sesiones(
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_tutor INT NOT NULL,
    id_materia INT NOT NULL,
    fecha_sesion DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    modalidad ENUM('Virtual', 'Presencial') NOT NULL,
    enlace_videoconferencia VARCHAR(255),
    estado ENUM(
        'Pendiente',
        'Aceptada',
        'Rechazada',
        'Completada',
        'Cancelada'
    ) DEFAULT 'Pendiente',
    FOREIGN KEY(id_estudiante) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY(id_tutor) REFERENCES Perfiles_Tutores(id_tutor),
    FOREIGN KEY(id_materia) REFERENCES Materias(id_materia)
);
-- 7. Tabla de Reseñas y Calificaciones
CREATE TABLE Resenas_Calificaciones(
    id_resena INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT UNIQUE NOT NULL,
    puntuacion INT CHECK
        (puntuacion BETWEEN 1 AND 5),
        comentario TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(id_reserva) REFERENCES Reservas_Sesiones(id_reserva)
);
