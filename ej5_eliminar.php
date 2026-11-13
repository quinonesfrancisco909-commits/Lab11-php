<?php
/**
 * ej5_eliminar.php — Ejercicio 5: Eliminar datos por umbral de stock
 */

require_once 'db_config.php';

$semilla = 155; // ← tu semilla

$computadora = gethostname();
$fechaHora   = date('Y-m-d H:i:s');

$umbral = ($semilla % 10) + 1;

try {
    $pdo = obtenerConexion();

    // Paso 1: qué se va a eliminar
    $sel = $pdo->prepare(
        "SELECT id, nombre, stock FROM productos
         WHERE semilla = :semilla AND stock < :umbral ORDER BY id"
    );
    $sel->execute([':semilla' => $semilla, ':umbral' => $umbral]);
    $aEliminar = $sel->fetchAll(PDO::FETCH_ASSOC);

    // Paso 2: eliminar (solo si hay algo)
    $totalEliminados = 0;
    if (!empty($aEliminar)) {
        $del = $pdo->prepare(
            "DELETE FROM productos WHERE semilla = :semilla AND stock < :umbral"
        );
        $del->execute([':semilla' => $semilla, ':umbral' => $umbral]);
        $totalEliminados = $del->rowCount();
    }

    // Paso 3: contar restantes
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE semilla = :semilla");
    $cnt->execute([':semilla' => $semilla]);
    $restantes = (int)$cnt->fetchColumn();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ej5 - DELETE</title>
        <style>
            body   { font-family: Arial, sans-serif; margin: 40px; }
            table  { border-collapse: collapse; width: 70%; }
            th, td { border: 1px solid #ccc; padding: 8px; }
            th     { background: #c62828; color: white; }
            .resumen { background: #fce4ec; padding: 15px; margin-top: 20px; border-radius: 6px; }
        </style>
    </head>
    <body>
        <h1>Ejercicio 5 — Eliminar Productos</h1>
        <p>Estudiante: quinones francisco — Semilla: <?= $semilla ?></p>
        <p>Umbral de stock: <strong><?= $umbral ?></strong></p>

        <?php if (empty($aEliminar)): ?>
            <p>No hay productos con stock menor a <?= $umbral ?></p>
        <?php else: ?>
            <h2>Productos eliminados</h2>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Stock</th></tr>
                <?php foreach ($aEliminar as $p): ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p>Total eliminados: <strong><?= $totalEliminados ?></strong></p>
        <?php endif; ?>

        <div class="resumen">
            <p>Productos restantes: <strong><?= $restantes ?></strong></p>
            <p>Ejecutado en: <strong><?= htmlspecialchars($computadora) ?></strong>
               — <?= htmlspecialchars($fechaHora) ?></p>
        </div>
    </body>
    </html>
    <?php
} catch (PDOException $e) {
    echo '<p style="color:red">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}