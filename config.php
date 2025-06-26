<?php
// config.php
$host = 'localhost';
$dbname = 'reciclaje_pet';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Crear tablas si no existen
$sql = "
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    direccion VARCHAR(255),
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS centros_reciclaje (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    horario VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS registro_material (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    centro_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (centro_id) REFERENCES centros_reciclaje(id)
);
";

try {
    $pdo->exec($sql);
    
    // Insertar datos de ejemplo si las tablas están vacías
    $stmt = $pdo->query("SELECT COUNT(*) FROM centros_reciclaje");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO centros_reciclaje (nombre, direccion, ciudad, telefono, horario) VALUES
            ('Centro de Reciclaje Norte', 'Calle Reforma 123', 'Ciudad de México', '5551234567', 'L-V 8am-6pm'),
            ('EcoRecicla Centro', 'Av. Juárez 456', 'Guadalajara', '3339876543', 'L-S 9am-5pm'),
            ('Verde Futuro', 'Boulevard López Mateos 789', 'Monterrey', '8187654321', 'L-D 7am-7pm');
        ");
    }
} catch (PDOException $e) {
    die("Error al crear tablas: " . $e->getMessage());
}
?>
