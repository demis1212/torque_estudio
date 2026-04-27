<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/' || $basePath === '\\') $basePath = '';

$severityColors = [
    'Baja' => '#28a745',
    'Media' => '#ffc107',
    'Alta' => '#fd7e14',
    'Crítica' => '#dc3545'
];

$systemIcons = [
    'Motor' => '🔧',
    'Motor - Combustible' => '⛽',
    'Motor - Entrada de Aire' => '💨',
    'Motor - Entrada' => '💨',
    'Motor - Admisión' => '💨',
    'Motor - VVT' => '⚙️',
    'Motor - Sincronización' => '⏱️',
    'Motor - O2' => '📊',
    'Motor - Ignición' => '⚡',
    'Motor - Inyectores' => '⛽',
    'Motor - Acelerador' => '🏁',
    'Motor - CKP' => '📡',
    'Motor - CMP' => '📡',
    'Motor - Detonación' => '🔔',
    'Motor - Ralentí' => '📉',
    'Transmisión' => '⚙️',
    'ABS' => '🛑',
    'Airbag' => '�',
    'Emisiones' => '💨',
    'Emisiones - O2' => '📊',
    'Emisiones - Catalizador' => '🏭',
    'Emisiones - EGR' => '🔄',
    'Emisiones - EVAP' => '🛢️',
    'Emisiones - Aire' => '�️',
    'Enfriamiento' => '🌡️',
    'Eléctrico' => '⚡',
    'CAN Bus' => '🌐',
    'PCM' => '💻',
    'Instrumentos' => '📊'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTC Codes - Torque Studio ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --surface: #0a0c10;
            --surface-container: #11131a;
            --surface-container-high: #1a1d26;
            --on-surface: #e8eaf2;
            --on-surface-variant: #9aa3b2;
            --primary: #8ab4f8;
            --primary-container: #4d8eff;
            --outline: rgba(255,255,255,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--on-surface);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--surface-container) 0%, var(--surface) 100%);
            border-right: 1px solid var(--outline);
            padding: 20px 16px;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            padding: 24px;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 8px 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid var(--outline);
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-container) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .sidebar-header h2 {
            font-size: 18px;
            margin: 0;
            color: var(--on-surface);
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600;
        }
        .nav-section { margin-bottom: 8px; }
        .nav-section-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--on-surface-variant);
            padding: 16px 8px 8px;
            font-weight: 600;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--on-surface-variant);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: var(--on-surface);
        }
        .nav-item.active {
            background: linear-gradient(135deg, var(--primary-container) 0%, rgba(77,142,255,0.8) 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .nav-item i { width: 20px; text-align: center; }
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
        }
        .header {
            margin-bottom: 32px;
        }
        .header h1 { margin: 0; font-size: 32px; }
        .search-box {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .search-form {
            display: flex;
            gap: 12px;
        }
        .search-form input {
            flex: 1;
            padding: 14px;
            background-color: #0F1115;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--on-surface);
            font-size: 16px;
            text-transform: uppercase;
        }
        .search-form input:focus {
            outline: none;
            border-color: var(--primary-container);
        }
        .btn-primary {
            padding: 14px 32px;
            background-color: var(--primary-container);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .dtc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 16px;
        }
        .dtc-card {
            background-color: #1F2430;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .dtc-card:hover {
            transform: translateY(-2px);
            background-color: rgba(255, 255, 255, 0.02);
        }
        .dtc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .dtc-code {
            font-family: 'Space Grotesk', monospace;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }
        .severity-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .dtc-system {
            font-size: 13px;
            color: #888;
            margin-bottom: 8px;
        }
        .dtc-description {
            font-size: 14px;
            line-height: 1.5;
        }
        .common-codes {
            background-color: #1F2430;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 24px;
        }
        .common-codes h3 {
            margin: 0 0 16px 0;
        }
        .category-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            color: #c2c6d6;
            cursor: pointer;
            font-size: 13px;
        }
        .tab-btn:hover, .tab-btn.active {
            background-color: var(--primary-container);
            color: #fff;
        }
        .empty-state {
            text-align: center;
            padding: 48px;
            color: #666;
        }
        .legend {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
        }
        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: var(--surface-container);
            margin: 5% auto;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid var(--outline);
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        .modal-close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #888;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: var(--on-surface);
        }
    </style>
</head>
<body>
    <?php $userRole = getUserRole(); ?>
    <!-- Sidebar Consistente -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🔧</div>
            <h2>Torque Studio</h2>
        </div>
        
        <nav class="nav-section">
            <div class="nav-section-title">Principal</div>
            <a href="<?= $basePath ?>/dashboard" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
            <a href="<?= $basePath ?>/clients" class="nav-item"><i class="fas fa-users"></i> Clientes</a>
            <a href="<?= $basePath ?>/vehicles" class="nav-item"><i class="fas fa-car"></i> Vehículos</a>
            <a href="<?= $basePath ?>/work-orders" class="nav-item"><i class="fas fa-clipboard-list"></i> Órdenes</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Operaciones</div>
            <a href="<?= $basePath ?>/services" class="nav-item"><i class="fas fa-wrench"></i> Servicios</a>
            <a href="<?= $basePath ?>/parts" class="nav-item"><i class="fas fa-boxes"></i> Inventario</a>
            <a href="<?= $basePath ?>/tools" class="nav-item"><i class="fas fa-tools"></i> Herramientas</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Herramientas</div>
            <a href="<?= $basePath ?>/manuals" class="nav-item"><i class="fas fa-book"></i> Manuales</a>
            <a href="<?= $basePath ?>/vin-decoder" class="nav-item"><i class="fas fa-search"></i> VIN Decoder</a>
            <a href="<?= $basePath ?>/dtc" class="nav-item active"><i class="fas fa-exclamation-triangle"></i> DTC Codes</a>
        </nav>
        
        <nav class="nav-section">
            <div class="nav-section-title">Administración</div>
            <a href="<?= $basePath ?>/reports" class="nav-item"><i class="fas fa-chart-bar"></i> Reportes</a>
            <?php if($userRole == 1): ?>
            <a href="<?= $basePath ?>/users" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
            <a href="<?= $basePath ?>/settings" class="nav-item"><i class="fas fa-cog"></i> Configuración</a>
            <?php endif; ?>
        </nav>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>🔧 DTC Codes - Códigos de Diagnóstico</h1>
        </div>

        <div class="search-box">
            <p style="color: #888; margin-bottom: 16px;">
                Busca códigos de error OBD2 por código (ej: P0300) o descripción.
            </p>
            <form method="POST" action="<?= $basePath ?>/dtc/search" class="search-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="text" name="code" placeholder="Ej: P0300, P0420, C0035..." value="<?= esc($code ?? '') ?>" maxlength="10">
                <button type="submit" class="btn-primary">Buscar Código</button>
            </form>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #28a745;"></div>
                <span>Baja</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #ffc107;"></div>
                <span>Media</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #fd7e14;"></div>
                <span>Alta</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background-color: #dc3545;"></div>
                <span>Crítica</span>
            </div>
        </div>

        <?php if (!empty($results)): ?>
        <h3>Resultados de Búsqueda (<?= count($results) ?>)</h3>
        <div class="dtc-grid">
            <?php foreach ($results as $code => $dtc): ?>
            <div class="dtc-card" onclick="showDtcDetails('<?= $code ?>')">
                <div class="dtc-header">
                    <span class="dtc-code"><?= $code ?></span>
                    <span class="severity-badge" style="background-color: <?= $severityColors[$dtc['severity']] ?? '#666' ?>20; color: <?= $severityColors[$dtc['severity']] ?? '#666' ?>">
                        <?= $dtc['severity'] ?>
                    </span>
                </div>
                <div class="dtc-system">
                    <?= $systemIcons[$dtc['system']] ?? '🔧' ?> <?= $dtc['system'] ?>
                </div>
                <div class="dtc-description">
                    <?= esc($dtc['description']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php elseif ($is_search): ?>
        <div class="empty-state">
            <p>No se encontraron códigos para "<?= esc($search_query) ?>"</p>
        </div>
        <?php endif; ?>

        <div class="common-codes">
            <h3>📚 Base de Datos DTC Completa (<?= count($all_dtcs ?? []) ?> códigos)</h3>
            <div class="category-tabs">
                <button class="tab-btn active" onclick="filterCodes('all')">Todos</button>
                <button class="tab-btn" onclick="filterCodes('Motor')">Motor</button>
                <button class="tab-btn" onclick="filterCodes('Transmisión')">Transmisión</button>
                <button class="tab-btn" onclick="filterCodes('Emisiones')">Emisiones</button>
                <button class="tab-btn" onclick="filterCodes('ABS')">ABS</button>
                <button class="tab-btn" onclick="filterCodes('Airbag')">Airbag</button>
                <button class="tab-btn" onclick="filterCodes('CAN')">CAN Bus</button>
            </div>
            <div class="dtc-grid">
                <?php foreach ($all_dtcs as $code => $dtc): ?>
                <div class="dtc-card" data-system="<?= $dtc['system'] ?>" onclick="showDtcDetails('<?= $code ?>')">
                    <div class="dtc-header">
                        <span class="dtc-code"><?= $code ?></span>
                        <span class="severity-badge" style="background-color: <?= $severityColors[$dtc['severity']] ?? '#666' ?>20; color: <?= $severityColors[$dtc['severity']] ?? '#666' ?>">
                            <?= $dtc['severity'] ?>
                        </span>
                    </div>
                    <div class="dtc-system">
                        <?= $systemIcons[$dtc['system']] ?? '🔧' ?> <?= $dtc['system'] ?>
                    </div>
                    <div class="dtc-description">
                        <?= esc($dtc['description']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles DTC -->
    <div id="dtcModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        // Datos de DTCs pasados desde PHP
        const dtcData = <?= json_encode($all_dtcs ?? []) ?>;

        function filterCodes(system) {
            const cards = document.querySelectorAll('.dtc-card');
            cards.forEach(card => {
                const cardSystem = card.dataset.system || '';
                if (system === 'all' || cardSystem.includes(system)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        function showDtcDetails(code) {
            const dtc = dtcData[code];
            if (!dtc) {
                alert('Código no encontrado en la base de datos');
                return;
            }
            
            const severityColors = {
                'Baja': '#28a745',
                'Media': '#ffc107',
                'Alta': '#fd7e14',
                'Crítica': '#dc3545'
            };
            
            const causesList = Array.isArray(dtc.causes) ? dtc.causes : ['No especificado'];
            const causesHtml = causesList.map(c => `<li style="margin-bottom: 8px; line-height: 1.4;">⚠️ ${c}</li>`).join('');
            
            // Determinar tipo de señal visual
            const signalType = dtc.signal ? dtc.signal.toLowerCase() : '';
            const isAnalog = signalType.includes('analógico') || signalType.includes('analog') || signalType.includes('variable') || signalType.includes('ac') || signalType.includes('voltaje');
            const isDigital = signalType.includes('digital') || signalType.includes('pwm') || signalType.includes('hall') || signalType.includes('cuadrada');
            
            const signalVisual = isAnalog ? `
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                    <div style="background: linear-gradient(90deg, #28a745, #20c997); padding: 10px 20px; border-radius: 25px; color: white; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <span>〰️</span> ANALÓGICA
                    </div>
                    <div style="display: flex; align-items: flex-end; gap: 3px; height: 40px;">
                        <div style="width: 8px; background: linear-gradient(to top, #28a745, #20c997); border-radius: 2px; height: 20px;"></div>
                        <div style="width: 8px; background: linear-gradient(to top, #28a745, #20c997); border-radius: 2px; height: 35px;"></div>
                        <div style="width: 8px; background: linear-gradient(to top, #28a745, #20c997); border-radius: 2px; height: 25px;"></div>
                        <div style="width: 8px; background: linear-gradient(to top, #28a745, #20c997); border-radius: 2px; height: 40px;"></div>
                        <div style="width: 8px; background: linear-gradient(to top, #28a745, #20c997); border-radius: 2px; height: 15px;"></div>
                        <div style="width: 8px; background: linear-gradient(to top, #28a745, #20c997); border-radius: 2px; height: 30px;"></div>
                    </div>
                    <span style="color: #28a745; font-size: 13px;">Señal continua variable</span>
                </div>
            ` : isDigital ? `
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                    <div style="background: linear-gradient(90deg, #dc3545, #fd7e14); padding: 10px 20px; border-radius: 25px; color: white; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <span>⬜⬛</span> DIGITAL
                    </div>
                    <div style="display: flex; align-items: center; gap: 2px;">
                        <div style="width: 15px; height: 20px; background: #dc3545; border-radius: 2px;"></div>
                        <div style="width: 15px; height: 20px; background: #333; border-radius: 2px;"></div>
                        <div style="width: 15px; height: 20px; background: #dc3545; border-radius: 2px;"></div>
                        <div style="width: 15px; height: 20px; background: #333; border-radius: 2px;"></div>
                        <div style="width: 15px; height: 20px; background: #dc3545; border-radius: 2px;"></div>
                    </div>
                    <span style="color: #dc3545; font-size: 13px;">Señal discreta on/off</span>
                </div>
            ` : `
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                    <div style="background: #6c757d; padding: 10px 20px; border-radius: 25px; color: white; font-weight: 600;">
                        📡 ${dtc.signal || 'No especificado'}
                    </div>
                </div>
            `;
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="modal-header" style="border-bottom: 3px solid ${severityColors[dtc.severity] || '#666'}; padding-bottom: 15px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-family: 'Space Grotesk', monospace; font-size: 32px; margin: 0; color: var(--primary);">${code}</h2>
                    <span style="background-color: ${severityColors[dtc.severity] || '#666'}20; color: ${severityColors[dtc.severity] || '#666'}; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; border: 2px solid ${severityColors[dtc.severity] || '#666'}40;">${dtc.severity}</span>
                </div>
                
                <!-- Tabla de Información General -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; background: rgba(255,255,255,0.02); border-radius: 12px; overflow: hidden;">
                    <tr style="background: rgba(138,180,248,0.1);">
                        <td style="padding: 12px 15px; font-weight: 600; color: var(--primary); width: 35%; border-bottom: 1px solid rgba(255,255,255,0.1);">📁 Sistema</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1);">${dtc.system || 'No especificado'}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; font-weight: 600; color: var(--primary); border-bottom: 1px solid rgba(255,255,255,0.1);">📝 Descripción</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1);">${dtc.description}</td>
                    </tr>
                    ${dtc.possible_failure ? `
                    <tr style="background: rgba(248,113,113,0.1);">
                        <td style="padding: 12px 15px; font-weight: 600; color: #f87171; width: 35%; border-bottom: 1px solid rgba(255,255,255,0.1);">⚠️ Posible Falla</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); color: #f87171;">${dtc.possible_failure}</td>
                    </tr>
                    ` : ''}
                </table>
                
                ${dtc.voltage ? `
                <!-- Tabla de Datos Técnicos -->
                <div style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <span style="font-size: 20px;">🔌</span>
                        <span style="color: var(--primary); font-size: 16px; font-weight: 600;">Datos Técnicos del Sensor</span>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; background: rgba(77,142,255,0.08); border-radius: 12px; overflow: hidden; border: 1px solid rgba(77,142,255,0.3);">
                        <tr>
                            <td style="padding: 15px; font-weight: 600; color: #8ab4f8; width: 35%;">⚡ Voltaje de Operación</td>
                            <td style="padding: 15px; font-family: monospace; font-size: 15px;">${dtc.voltage}</td>
                        </tr>
                        <tr style="background: rgba(77,142,255,0.05);">
                            <td style="padding: 15px; font-weight: 600; color: #8ab4f8;">📡 Tipo de Señal</td>
                            <td style="padding: 15px; font-family: monospace; font-size: 15px;">${dtc.signal}</td>
                        </tr>
                    </table>
                    ${signalVisual}
                </div>
                ` : ''}
                
                <!-- Tabla de Posibles Causas -->
                <div style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <span style="font-size: 20px;">⚠️</span>
                        <span style="color: #ffc107; font-size: 16px; font-weight: 600;">Posibles Causas</span>
                    </div>
                    <div style="background: rgba(255,193,7,0.08); border: 1px solid rgba(255,193,7,0.3); border-radius: 12px; padding: 20px;">
                        <ul style="margin: 0; padding-left: 25px; color: #ffc107;">
                            ${causesHtml}
                        </ul>
                    </div>
                </div>
                
                <!-- Tabla de Solución -->
                <div style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <span style="font-size: 20px;">🔧</span>
                        <span style="color: #28a745; font-size: 16px; font-weight: 600;">Procedimiento de Solución</span>
                    </div>
                    <div style="background: rgba(40,167,69,0.08); border: 1px solid rgba(40,167,69,0.3); border-radius: 12px; padding: 20px; line-height: 1.6; color: #28a745;">
                        ${dtc.solution || 'Diagnosticar con scanner profesional y verificar componente específico'}
                    </div>
                </div>
                
                <!-- Códigos Relacionados -->
                ${dtc.related_codes ? `
                <div style="background: rgba(138,180,248,0.08); border: 1px solid rgba(138,180,248,0.3); border-radius: 12px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <span style="font-size: 20px;">🔗</span>
                        <span style="color: #8ab4f8; font-size: 16px; font-weight: 600;">Códigos Relacionados / Similares</span>
                    </div>
                    <p style="color: #888; font-size: 13px; margin-bottom: 15px; margin-top: -10px;">
                        Estos códigos pueden estar entrelazados o presentar síntomas similares. Haz clic para ver detalles.
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        ${dtc.related_codes.map(relatedCode => {
                            const relatedDtc = dtcData[relatedCode];
                            const relatedSeverity = relatedDtc ? relatedDtc.severity : 'Media';
                            const severityColor = severityColors[relatedSeverity] || '#666';
                            return `
                                <button onclick="event.stopPropagation(); showDtcDetails('${relatedCode}')" 
                                        style="background: ${severityColor}20; border: 2px solid ${severityColor}40; color: ${severityColor}; 
                                               padding: 10px 18px; border-radius: 25px; font-family: 'Space Grotesk', monospace; 
                                               font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px;
                                               display: flex; align-items: center; gap: 8px;"
                                        onmouseover="this.style.background='${severityColor}40'; this.style.transform='scale(1.05)';"
                                        onmouseout="this.style.background='${severityColor}20'; this.style.transform='scale(1)';"
                                        title="${relatedDtc ? relatedDtc.description : 'Ver código relacionado'}">
                                    ${relatedCode}
                                    ${relatedDtc ? `<span style="font-size: 11px; opacity: 0.8;">●</span>` : ''}
                                </button>
                            `;
                        }).join('')}
                    </div>
                </div>
                ` : ''}
            `;
            
            document.getElementById('dtcModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('dtcModal').style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('dtcModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
