<?php
$titulo_pagina = "Administración - Tienda WoW";

require_once 'includes/header.php';
require_once 'config/conexion.php';

// Solo admin entra aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// CONSULTAS PARA EL DASHBOARD
$stats_ingresos = $pdo->query("SELECT SUM(total) FROM pedidos")->fetchColumn() ?: 0;
$stats_pedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn() ?: 0;
$stats_estrella = $pdo->query("SELECT nombre_producto FROM pedidos_detalle GROUP BY nombre_producto ORDER BY SUM(cantidad) DESC LIMIT 1")->fetchColumn() ?: t('admin.no_ventas');

// CONSULTA DE PRODUCTOS
$productos = $pdo->query("SELECT * FROM productos ORDER BY id DESC")->fetchAll();

// CONSULTA DE USUARIOS
$usuarios = $pdo->query("SELECT id, username, rol, fecha_registro FROM usuarios ORDER BY id ASC")->fetchAll();

?>

<div class="contenedor-principal" style="max-width: 1200px; margin-bottom: 80px;">
    
    <h2 class="titulo-seccion" style="margin-top: 40px; margin-bottom: 30px; justify-content: center;"><?php echo t('admin.panel'); ?></h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 50px;">
        <div class="preferencias-card" style="margin: 0; text-align: center; border: 2px solid var(--color-primario);">
            <i class="fa-solid fa-sack-dollar" style="font-size: 2rem; color: #2ecc71; margin-bottom: 10px;"></i>
            <h3 style="color: var(--texto-secundario); font-size: 14px; text-transform: uppercase;"><?php echo t('admin.ingresos'); ?></h3>
            <p style="font-size: 28px; font-weight: 900; color: var(--texto-principal);"><?php echo number_format($stats_ingresos, 2); ?> €</p>
        </div>
        <div class="preferencias-card" style="margin: 0; text-align: center; border: 2px solid var(--color-primario);">
            <i class="fa-solid fa-scroll" style="font-size: 2rem; color: var(--color-primario); margin-bottom: 10px;"></i>
            <h3 style="color: var(--texto-secundario); font-size: 14px; text-transform: uppercase;"><?php echo t('admin.pedidos_totales'); ?></h3>
            <p style="font-size: 28px; font-weight: 900; color: var(--texto-principal);"><?php echo $stats_pedidos; ?></p>
        </div>
        <div class="preferencias-card" style="margin: 0; text-align: center; border: 2px solid var(--color-primario);">
            <i class="fa-solid fa-crown" style="font-size: 2rem; color: #f1c40f; margin-bottom: 10px;"></i>
            <h3 style="color: var(--texto-secundario); font-size: 14px; text-transform: uppercase;"><?php echo t('admin.producto_estrella'); ?></h3>
            <p style="font-size: 20px; font-weight: 900; color: var(--texto-principal);"><?php echo $stats_estrella; ?></p>
        </div>
    </div>

    <div class="header-seccion" style="padding: 0; margin-bottom: 20px; max-width: 100%; display: flex; align-items: center;">
        <h2 class="titulo-seccion"><?php echo t('admin.gestion_prods'); ?></h2>
        
        <div class="admin-search-wrapper">
            <div class="admin-search-container">
                <input type="text" class="admin-search-input" placeholder="<?php echo t('productos.buscar'); ?>" onkeyup="filtrarTablaAdmin('tbody-productos', 2, this.value)">
                <i class="fa-solid fa-magnifying-glass admin-search-icon" onclick="toggleAdminSearch(this)"></i>
            </div>
            <button class="btn-finalizar-premium" onclick="modalProducto()" style="padding: 10px 20px; font-size: 14px; margin: 0 !important;">+ <?php echo t('admin.nuevo_prod'); ?></button>
        </div>
    </div>

    <div class="preferencias-card" style="width: 100%; overflow-x: auto; margin: 0 0 50px 0; padding: 10px; border: 3px solid var(--color-primario);">
        <table style="width: 100%; border-collapse: collapse; color: var(--texto-principal);">
            <thead>
                <tr style="border-bottom: 2px solid var(--color-primario); text-align: left;">
                    <th class="sortable" onclick="sortTable('tbody-productos', 0, 'num', this)" style="padding: 15px;">ID</th>
                    <th><?php echo t('admin.img'); ?></th>
                    <th class="sortable" onclick="sortTable('tbody-productos', 2, 'str', this)"><?php echo t('admin.nombre'); ?></th>
                    
                    <th class="sortable" style="text-align: center;" onclick="sortTable('tbody-productos', 3, 'num', this)"><?php echo t('admin.precio'); ?></th>
                    <th class="sortable" style="text-align: center;" onclick="sortTable('tbody-productos', 4, 'num', this)"><?php echo t('admin.dcto'); ?></th>
                    
                    <th style="text-align: right; padding-right: 15px;"><?php echo t('admin.acciones'); ?></th>
                </tr>
            </thead>
            <tbody id="tbody-productos">
                <?php foreach ($productos as $p): ?>
                <tr style="border-bottom: 1px solid var(--borde-color);">
                    <td style="padding: 15px;"><?php echo $p['id']; ?></td>
                    <td><img src="<?php echo $p['imagen']; ?>" style="height: 35px; border-radius: 4px; border: 1px solid var(--borde-color);"></td>
                    
                    <td><?php echo ($idioma_actual === 'en' && !empty($p['nombre_en'])) ? $p['nombre_en'] : $p['nombre_es']; ?></td>
                    
                    <td style="text-align: center;"><?php echo $p['precio']; ?> €</td>
                    <td style="text-align: center;"><span style="color: #2ecc71; font-weight: bold;"><?php echo $p['descuento']; ?>%</span></td>
                    
                    <td style="text-align: right; padding: 15px;">
                        <button class="btn-favorito" onclick='modalProducto(<?php echo json_encode($p); ?>)' style="display: inline-flex; width: 35px; height: 35px;">
                            <i class="fa-solid fa-pen" style="color: white; font-size: 14px;"></i>
                        </button>
                        <button class="btn-eliminar-elegante" onclick="borrarAccion(<?php echo $p['id']; ?>, 'borrar')" style="width: 35px; height: 35px; display: inline-flex; margin: 0;">
                            <i class="fa-solid fa-trash" style="font-size: 14px;"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="header-seccion" style="padding: 0; margin-bottom: 20px; max-width: 100%; display: flex; align-items: center;">
        <h2 class="titulo-seccion"><?php echo t('admin.gestion_users'); ?></h2>
        
        <div class="admin-search-wrapper">
            <div class="admin-search-container">
                <input type="text" class="admin-search-input" placeholder="<?php echo t('admin.buscar_usuario'); ?>" onkeyup="filtrarTablaAdmin('tbody-usuarios', 1, this.value)">
                <i class="fa-solid fa-magnifying-glass admin-search-icon" onclick="toggleAdminSearch(this)"></i>
            </div>
        </div>
    </div>

    <div class="preferencias-card" style="width: 100%; overflow-x: auto; margin: 0; padding: 10px; border: 3px solid var(--color-primario);">
        <table style="width: 100%; border-collapse: collapse; color: var(--texto-principal);">
            <thead>
                <tr style="border-bottom: 2px solid var(--color-primario); text-align: left;">
                    <th class="sortable" onclick="sortTable('tbody-usuarios', 0, 'num', this)" style="padding: 15px;">ID</th>
                    <th class="sortable" onclick="sortTable('tbody-usuarios', 1, 'str', this)"><?php echo t('admin.usuario'); ?></th>
                    <th class="sortable" onclick="sortTable('tbody-usuarios', 2, 'str', this)"><?php echo t('admin.rol'); ?></th>
                    <th class="sortable" onclick="sortTable('tbody-usuarios', 3, 'date', this)"><?php echo t('admin.registro'); ?></th>
                    <th style="text-align: right; padding-right: 15px;"><?php echo t('admin.acciones'); ?></th>
                </tr>
            </thead>
            <tbody id="tbody-usuarios">
                <?php foreach ($usuarios as $u): ?>
                <tr style="border-bottom: 1px solid var(--borde-color);">
                    <td style="padding: 15px;"><?php echo $u['id']; ?></td>
                    <td style="font-weight: bold;"><?php echo $u['username']; ?></td>
                    <td>
                        <span style="padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; background: <?php echo $u['rol'] === 'admin' ? '#f1c40f' : 'var(--borde-color)'; ?>; color: <?php echo $u['rol'] === 'admin' ? '#000' : 'var(--texto-principal)'; ?>;">
                            <?php echo strtoupper($u['rol']); ?>
                        </span>
                    </td>
                    <td style="color: var(--texto-secundario); font-size: 14px;"><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                    <td style="text-align: right; padding: 15px;">
                        <button class="btn-comprar" onclick="ejecutarAccion({id: <?php echo $u['id']; ?>, accion: 'cambiar_rol'})" title="<?php echo t('admin.cambiar_rol'); ?>" style="display: inline-flex; width: 35px; height: 35px; background: #9b59b6;">
                            <i class="fa-solid fa-user-shield" style="color: white; font-size: 14px;"></i>
                        </button>
                        <?php if($u['username'] !== $_SESSION['usuario']): ?>
                        <button class="btn-eliminar-elegante" onclick="borrarAccion(<?php echo $u['id']; ?>, 'borrar_usuario')" style="width: 35px; height: 35px; display: inline-flex; margin: 0 0 0 5px;">
                            <i class="fa-solid fa-user-minus" style="font-size: 14px;"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>

// FUNCIONES DE BÚSQUEDA Y FILTRADO
function toggleAdminSearch(icono) {
    const contenedor = icono.parentElement;
    const input = contenedor.querySelector('.admin-search-input');
    
    contenedor.classList.toggle('active');
    
    if (contenedor.classList.contains('active')) {
        input.focus(); 
    } else {
        input.value = '';
        input.dispatchEvent(new Event('keyup'));
    }
}

function filtrarTablaAdmin(tbodyId, colIndex, valor) {
    const tbody = document.getElementById(tbodyId);
    const rows = tbody.querySelectorAll('tr');
    const textoBuscado = valor.toLowerCase().trim();

    rows.forEach(row => {
        const celdaTexto = row.children[colIndex].innerText.toLowerCase();
        
        if (celdaTexto.includes(textoBuscado)) {
            row.style.display = ''; 
        } else {
            row.style.display = 'none'; 
        }
    });
}

// FUNCIÓN PARA ORDENAR TABLAS (SORT)
function sortTable(tbodyId, colIndex, type, thElement) {
    const tbody = document.getElementById(tbodyId);
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const table = tbody.parentElement;
    const headers = table.querySelectorAll('th.sortable');
    headers.forEach(th => {
        if (th !== thElement) {
            th.classList.remove('asc', 'desc');
        }
    });

    const isAsc = thElement.classList.contains('asc');
    const direction = isAsc ? -1 : 1;
    
    if (isAsc) {
        thElement.classList.remove('asc');
        thElement.classList.add('desc');
    } else {
        thElement.classList.remove('desc');
        thElement.classList.add('asc');
    }

    rows.sort((a, b) => {
        let valA = a.children[colIndex].innerText.trim();
        let valB = b.children[colIndex].innerText.trim();

        if (type === 'num') {
            valA = parseFloat(valA.replace(/[^0-9.-]+/g, "")) || 0;
            valB = parseFloat(valB.replace(/[^0-9.-]+/g, "")) || 0;
            return (valA - valB) * direction;
        } 
        else if (type === 'date') {
            let partsA = valA.split('/');
            let partsB = valB.split('/');
            let dateA = new Date(partsA[2], partsA[1] - 1, partsA[0]).getTime() || 0;
            let dateB = new Date(partsB[2], partsB[1] - 1, partsB[0]).getTime() || 0;
            return (dateA - dateB) * direction;
        } 
        else {
            return valA.localeCompare(valB) * direction;
        }
    });

    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

// FUNCIONES DEL CRUD DE PRODUCTOS Y USUARIOS
async function modalProducto(prod = null) {
    const isEdit = prod !== null;
    const { value: fV } = await Swal.fire({
        title: isEdit ? '<?php echo t('admin.editar'); ?>' : '<?php echo t('admin.nuevo_prod'); ?>',
        background: 'var(--bg-nav)',
        color: 'var(--texto-principal)',
        html: `
            <input id="sw-nes" class="swal2-input" placeholder="<?php echo t('admin.ph_nombre_es'); ?>" value="${isEdit && prod.nombre_es ? prod.nombre_es : ''}">
            <input id="sw-nen" class="swal2-input" placeholder="<?php echo t('admin.ph_nombre_en'); ?>" value="${isEdit && prod.nombre_en ? prod.nombre_en : ''}">
            <input id="sw-p" type="number" step="0.01" class="swal2-input" placeholder="<?php echo t('admin.ph_precio'); ?>" value="${isEdit ? prod.precio : ''}">
            <input id="sw-d" type="number" class="swal2-input" placeholder="<?php echo t('admin.ph_dcto'); ?>" value="${isEdit ? prod.descuento : ''}">
            <input id="sw-i" class="swal2-input" placeholder="<?php echo t('admin.ph_img'); ?>" value="${isEdit ? prod.imagen : ''}">
        `,
        showCancelButton: true,
        confirmButtonColor: 'var(--color-primario)',
        preConfirm: () => {
            return {
                id: isEdit ? prod.id : null,
                nombre_es: document.getElementById('sw-nes').value,
                nombre_en: document.getElementById('sw-nen').value,
                precio: document.getElementById('sw-p').value,
                descuento: document.getElementById('sw-d').value,
                imagen: document.getElementById('sw-i').value,
                accion: isEdit ? 'editar' : 'crear'
            }
        }
    });
    if (fV) ejecutarAccion(fV);
}

async function borrarAccion(id, accion) {
    const result = await Swal.fire({
        title: '<?php echo t('admin.confirm_borrar'); ?>',
        text: '<?php echo t('admin.txt_borrar'); ?>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: '<?php echo t('admin.borrar'); ?>',
        background: 'var(--bg-nav)',
        color: 'var(--texto-principal)'
    });
    if (result.isConfirmed) ejecutarAccion({ id, accion });
}

async function ejecutarAccion(datos) {
    try {
        const res = await fetch('api_admin.php', { method: 'POST', body: JSON.stringify(datos) });
        const r = await res.json();
        if(r.success) window.location.reload();
        else Swal.fire('Error', r.error, 'error');
    } catch (e) { console.error(e); }
}
</script>

<?php require_once 'includes/footer.php'; ?>