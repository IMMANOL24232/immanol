<?php
require 'config.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT nombre, email, direccion, telefono FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener registros de reciclaje del usuario
$stmt = $pdo->prepare("
    SELECT r.*, c.nombre as centro_nombre 
    FROM registro_material r 
    LEFT JOIN centros_reciclaje c ON r.centro_id = c.id 
    WHERE r.usuario_id = ? 
    ORDER BY r.fecha DESC
");
$stmt->execute([$_SESSION['user_id']]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el total reciclado
$stmt = $pdo->prepare("SELECT SUM(cantidad) as total FROM registro_material WHERE usuario_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total = $stmt->fetch(PDO::FETCH_ASSOC);
$totalReciclado = $total['total'] ?? 0;

// Procesar nuevo registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $cantidad = $_POST['cantidad'] ?? 0;
    $centro_id = $_POST['centro_id'] ?? null;
    
    if ($cantidad > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO registro_material (usuario_id, cantidad, centro_id) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $cantidad, $centro_id]);
            
            header("Location: dashboard.php?success=registro");
            exit();
        } catch (PDOException $e) {
            $error = "Error al registrar el material: " . $e->getMessage();
        }
    } else {
        $error = "La cantidad debe ser mayor a cero.";
    }
}

// Obtener centros de reciclaje
$centros = $pdo->query("SELECT * FROM centros_reciclaje")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Reciclaje PET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card-counter {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .card-counter:hover {
            transform: translateY(-5px);
        }
    </style>
    

<style>
        /* Agregar nuevos estilos */
        .info-card {
            transition: all 0.3s ease;
            border-left: 4px solid #198754;
        }
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .pet-process-image {
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .impact-icon {
            font-size: 2rem;
            color: #198754;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #198754;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #198754;
        }
    </style>
</head>
<body>











<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Reciclaje PET</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#registros">Mis Registros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#centros">Centros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#estadisticas">Estadísticas</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3 d-none d-md-block">Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person me-2"></i> Mi perfil</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container my-5">






























    <!-- [El navbar permanece igual...] -->

    <div class="container my-5">
        <!-- Nueva sección de información sobre PET -->
        <section id="pet-info" class="mb-5">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body p-5 text-center">
                            <h2 class="mb-4"><i class="bi bi-recycle impact-icon me-2"></i>Todo sobre el PET</h2>
                            <p class="lead">Conoce más sobre este material y por qué es importante reciclarlo</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card info-card h-100">
                        <div class="card-body">
                            <h3 class="card-title text-success"><i class="bi bi-question-circle me-2"></i>¿Qué es el PET?</h3>
                            <p>El PET (Polietileno Tereftalato) es un tipo de plástico derivado del petróleo, ampliamente utilizado en envases de bebidas, alimentos y productos cosméticos por sus características:</p>
                            <ul>
                                <li><strong>Transparencia:</strong> Permite ver el contenido del envase.</li>
                                <li><strong>Resistencia:</strong> Soporta impactos y temperaturas moderadas.</li>
                                <li><strong>Ligereza:</strong> Facilita el transporte y manejo.</li>
                                <li><strong>Barrera a gases:</strong> Protege el contenido de oxígeno y CO₂.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card info-card h-100">
                        <div class="card-body">
                            <h3 class="card-title text-success"><i class="bi bi-clock-history me-2"></i>Ciclo de vida del PET</h3>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <h5>Producción</h5>
                                    <p>Se fabrica a partir de petróleo o gas natural mediante procesos químicos.</p>
                                </div>
                                <div class="timeline-item">
                                    <h5>Primer uso</h5>
                                    <p>Se convierte en botellas, envases o fibras textiles.</p>
                                </div>
                                <div class="timeline-item">
                                    <h5>Post-consumo</h5>
                                    <p>Puede ser reciclado hasta 7 veces o terminar en vertederos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-6">
                                <img src="reciclado-mecanico-de-materiales-plasticos-proceso-y-beneficios.png" 
                                alt="Proceso industrial de reciclaje de PET: botellas siendo clasificadas, trituradas y convertidas en pellets" 
                                class="pet-process-image w-100 h-100">
                            </div>
                            <div class="col-md-6">
                                <div class="card-body">
                                    <h3 class="card-title text-success">Proceso de reciclaje</h3>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item border-0"><strong>Recolección:</strong> Los consumidores depositan las botellas en centros de acopio.</li>
                                        <li class="list-group-item border-0"><strong>Clasificación:</strong> Se separan por color y tipo de plástico.</li>
                                        <li class="list-group-item border-0"><strong>Trituración:</strong> Las botellas se lavan y trituran en pequeñas hojuelas.</li>
                                        <li class="list-group-item border-0"><strong>Extrusión:</strong> Las hojuelas se funden y forman pellets para nuevos productos.</li>
                                    </ol>
                                    <div class="alert alert-info mt-3">
                                        <i class="bi bi-lightbulb"></i> <strong>Sabías que:</strong> Reciclar 1 tonelada de PET ahorra aproximadamente 3.8 barriles de petróleo.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card h-100 info-card">
                        <div class="card-body text-center">
                            <i class="bi bi-arrow-repeat impact-icon mb-3"></i>
                            <h4>Productos reciclados</h4>
                            <p>El PET reciclado se transforma en:</p>
                            <ul class="text-start">
                                <li>Nuevas botellas</li>
                                <li>Fibras para ropa</li>
                                <li>Muebles y alfombras</li>
                                <li>Materiales de construcción</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 info-card">
                        <div class="card-body text-center">
                            <i class="bi bi-globe2 impact-icon mb-3"></i>
                            <h4>Impacto ambiental</h4>
                            <p>Reciclar PET ayuda a:</p>
                            <ul class="text-start">
                                <li>Reducir residuos en vertederos</li>
                                <li>Disminuir emisiones de CO₂</li>
                                <li>Ahorrar energía y agua</li>
                                <li>Conservar recursos naturales</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 info-card">
                        <div class="card-body text-center">
                            <i class="bi bi-coin impact-icon mb-3"></i>
                            <h4>Economía circular</h4>
                            <p>El reciclaje de PET genera:</p>
                            <ul class="text-start">
                                <li>Empleos en la industria verde</li>
                                <li>Ahorro para empresas</li>
                                <li>Opción de ingresos para recolectores</li>
                                <li>Productos más económicos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card bg-success text-white mb-5">
                <div class="card-body">
                    <h3><i class="bi bi-check-circle me-2"></i>¿Cómo identificar el PET?</h3>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p>Busca el símbolo de reciclaje ♻ con el número <span class="badge bg-white text-dark">1</span> en la base de los envases.</p>
                            <p>Ejemplos comunes:</p>
                            <ul>
                                <li>Botellas de agua y refrescos</li>
                                <li>Envases de jugos</li>
                                <li>Contenedores de aderezos</li>
                            </ul>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="d-inline-block p-3 bg-white rounded">
                                <h5 class="text-dark">Código de identificación</h5>
                                <div style="font-size: 4rem;">♻ <span class="badge bg-success">1</span></div>
                                <p class="text-muted mb-0">PET o PETE</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>



























    
        <!-- Encabezado y tarjetas resumen -->
        <section id="dashboard" class="mb-5">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>Panel de Control</h2>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoRegistroModal">
                            <i class="bi bi-plus-circle me-1"></i> Registrar Reciclaje
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-counter bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-recycle me-2"></i>Total Reciclado</h5>
                            <h2 class="card-text"><?php echo number_format($totalReciclado, 2); ?> kg</h2>
                            <p class="small">Contribución ambiental total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-counter bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-list-check me-2"></i>Registros</h5>
                            <h2 class="card-text"><?php echo count($registros); ?></h2>
                            <p class="small">Entradas registradas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-counter bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-tree me-2"></i>Impacto</h5>
                            <h2 class="card-text"><?php echo number_format($totalReciclado * 3, 2); ?> m³</h2>
                            <p class="small">Ahorro en vertederos</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección de registros -->
        <section id="registros" class="mb-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="bi bi-recycle me-2"></i>Mis Registros de Reciclaje</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['success']) && $_GET['success'] === 'registro'): ?>
                        <div class="alert alert-success mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i> Registro de reciclaje añadido exitosamente.
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($registros)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i> Todavía no has agregado ningún registro de reciclaje.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cantidad (kg)</th>
                                        <th>Centro</th>
                                        <th>Impacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros as $registro): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($registro['fecha'])); ?></td>
                                            <td><?php echo $registro['cantidad']; ?></td>
                                            <td><?php echo $registro['centro_nombre'] ?? 'No especificado'; ?></td>
                                            <td><?php echo number_format($registro['cantidad'] * 3, 2); ?> m³</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#detalleRegistroModal" onclick="cargarDetalle(<?php echo $registro['id']; ?>)">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>











        <!-- Sección de centros de reciclaje -->
        <section id="centros" class="mb-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Centros de Reciclaje</h3>
                </div>
                <div class="card-body">
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php foreach ($centros as $centro): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($centro['nombre']); ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><strong><i class="bi bi-geo-fill text-success me-2"></i>Dirección:</strong> <?php echo htmlspecialchars($centro['direccion']) . ', ' . htmlspecialchars($centro['ciudad']); ?></p>
                                        <p class="card-text"><strong><i class="bi bi-clock text-success me-2"></i>Horario:</strong> <?php echo htmlspecialchars($centro['horario']); ?></p>
                                        <p class="card-text"><strong><i class="bi bi-telephone text-success me-2"></i>Teléfono:</strong> <?php echo htmlspecialchars($centro['telefono']); ?></p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <a href="#" class="btn btn-sm btn-success" onclick="seleccionarCentro(<?php echo $centro['id']; ?>, '<?php echo htmlspecialchars($centro['nombre']); ?>')">
                                            <i class="bi bi-check-circle me-1"></i> Seleccionar para reciclar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>












    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        #map {
            width: 100%;
            height: 100vh;
        }
        .info {
            padding: 6px 8px;
            background: white;
            background: rgba(255,255,255,0.8);
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        .info h4 {
            margin: 0 0 5px;
            color: #555;
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    
    <script>
        // Inicializar el mapa centrado en Madrid
        const map = L.map('map').setView([40.4165, -3.70256], 13);

        // Añadir capa de OpenStreetMap
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Añadir marcador con popup
        const marker = L.marker([40.4165, -3.70256]).addTo(map)
            .bindPopup('Madrid, España');

        // Añadir círculo
        const circle = L.circle([40.4165, -3.71256], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 500
        }).addTo(map).bindPopup('Área de interés');

        // Añadir polígono
        const polygon = L.polygon([
            [40.4265, -3.70256],
            [40.4265, -3.69256],
            [40.4165, -3.69256]
        ]).addTo(map).bindPopup('Zona restringida');

        // Control de escala
        L.control.scale().addTo(map);

        // Control de capas
        const baseMaps = {
            "OpenStreetMap": L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            })
        };

        const overlayMaps = {
            "Marcador": marker,
            "Círculo": circle,
            "Polígono": polygon
        };

        L.control.layers(baseMaps, overlayMaps).addTo(map);
    </script>
</body>
</html>

















        
        <!-- Sección de estadísticas -->
        <section id="estadisticas" class="mb-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Estadísticas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="chart-container" style="height: 300px;">
                                <canvas id="reciclajeChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>Tu contribución equivale a:</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-droplet text-success me-2"></i>Ahorro de agua</span>
                                    <span class="badge bg-success rounded-pill"><?php echo number_format($totalReciclado * 10, 0); ?> litros</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-lightning-charge text-success me-2"></i>Ahorro energético</span>
                                    <span class="badge bg-success rounded-pill"><?php echo number_format($totalReciclado * 15, 0); ?> kWh</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tree text-success me-2"></i>Árboles salvados</span>
                                    <span class="badge bg-success rounded-pill"><?php echo number_format($totalReciclado / 2, 1); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal para nuevo registro -->
    <div class="modal fade" id="nuevoRegistroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Registro de Reciclaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad (kg)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="cantidad" name="cantidad" required>
                        </div>
                        <div class="mb-3">
                            <label for="centro" class="form-label">Centro de Reciclaje</label>
                            <select class="form-select" id="centro" name="centro_id">
                                <option value="">Seleccione un centro...</option>
                                <?php foreach ($centros as $centro): ?>
                                    <option value="<?php echo $centro['id']; ?>"><?php echo htmlspecialchars($centro['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="registrar" class="btn btn-success">Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles de registro -->
    <div class="modal fade" id="detalleRegistroModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detalleRegistroContent">
                    <!-- Cargado dinámicamente con JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Reciclaje PET</h5>
                    <p>Contribuyendo a un futuro más sostenible.</p>
                </div>
                <div class="col-md-6 text-end">
                    <small>© <?php echo date('Y'); ?> Todos los derechos reservados</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
         function mostrarInfoAdicional(tipo) {
            const infos = document.querySelectorAll('.info-detallada');
            infos.forEach(info => info.classList.add('d-none'));
            
            const infoToShow = document.getElementById(`info-${tipo}`);
            infoToShow.classList.remove('d-none');
        }
        
        // Inicializar gráficas
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('reciclajeChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'PET Reciclado (kg)',
                        data: [12, 19, 3, 5, 2, 3],
                        backgroundColor: '#198754',
                        borderColor: '#198754',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Actualizar gráfica con datos reales si están disponibles
            <?php if (!empty($registros)): ?>
                updateChartWithRealData(chart);
            <?php endif; ?>
        });
        
        function updateChartWithRealData(chart) {
            // Aquí iría la lógica para obtener datos reales del usuario
            // Por ahora usaremos datos de ejemplo
            const newData = {
                labels: ['Última semana', 'Último mes', 'Últimos 3 meses', 'Total'],
                datasets: [{
                    label: 'Tu progreso',
                    data: [5, 15, 30, <?php echo $totalReciclado; ?>],
                    backgroundColor: '#198754'
                }]
            };
            chart.data = newData;
            chart.update();
        }
    </script>