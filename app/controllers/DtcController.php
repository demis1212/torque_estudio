<?php
namespace App\Controllers;

class DtcController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $basePath = dirname($_SERVER['SCRIPT_NAME']);
            if ($basePath === '/' || $basePath === '\\') $basePath = '';
            redirect($basePath . '/login');
        }
    }

    public function index() {
        $this->checkAuth();
        
        $code = $_GET['code'] ?? '';
        $results = [];
        
        // Get complete DTC database
        $allDtcs = $this->getDtcDatabase();
        
        if ($code) {
            $results = $this->searchDtcFromDatabase($code, $allDtcs);
        }
        
        // Get common DTCs from the full database
        $commonCodes = ['P0101', 'P0300', 'P0420', 'P0442', 'P0507', 'P0171', 'P0128', 'P0401', 'P0440', 'P0455'];
        $commonDtcs = [];
        foreach ($commonCodes as $commonCode) {
            if (isset($allDtcs[$commonCode])) {
                $commonDtcs[$commonCode] = $allDtcs[$commonCode];
            }
        }
        
        view('dtc/index', [
            'code' => $code,
            'results' => $results,
            'common_dtcs' => $commonDtcs,
            'all_dtcs' => $allDtcs,
            'is_search' => !empty($code),
            'search_query' => $code
        ]);
    }

    public function search() {
        $this->checkAuth();
        
        $code = strtoupper(trim($_POST['code'] ?? ''));
        
        if (empty($code)) {
            redirect('/dtc');
            return;
        }
        
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') $basePath = '';
        redirect($basePath . '/dtc?code=' . urlencode($code));
    }

    private function searchDtc($code) {
        $database = $this->getDtcDatabase();
        return $this->searchDtcFromDatabase($code, $database);
    }
    
    private function searchDtcFromDatabase($code, $database) {
        $results = [];
        $upperCode = strtoupper($code);
        
        foreach ($database as $dtcCode => $info) {
            if (strpos($dtcCode, $upperCode) !== false || 
                stripos($info['description'], $code) !== false) {
                $results[$dtcCode] = $info;
            }
        }
        
        return $results;
    }

    private function getCommonDtcs() {
        return [
            'P0101' => [
                'description' => 'Circuito MAF Rango/Desempeño',
                'system' => 'Motor - Entrada de Aire',
                'severity' => 'Media',
                'causes' => ['Sensor MAF sucio o defectuoso', 'Fuga en admisión de aire', 'Filtro de aire obstruido'],
                'solution' => 'Limpiar o reemplazar sensor MAF, revisar ductos de aire'
            ],
            'P0300' => [
                'description' => 'Fallas Aleatorias Múltiples Cilindros',
                'system' => 'Motor - Ignición',
                'severity' => 'Alta',
                'causes' => ['Bujías gastadas', 'Bobinas defectuosas', 'Inyectores sucios', 'Baja compresión'],
                'solution' => 'Revisar bujías, bobinas y realizar limpieza de inyectores'
            ],
            'P0420' => [
                'description' => 'Eficiencia Catalizador Debajo Umbral',
                'system' => 'Emisiones - Catalizador',
                'severity' => 'Media',
                'causes' => ['Catalizador deteriorado', 'Sensor O2 defectuoso', 'Fugas en escape'],
                'solution' => 'Revisar catalizador y sensores de oxígeno'
            ],
            'P0442' => [
                'description' => 'Fuga Pequeña Sistema EVAP Detectada',
                'system' => 'Emisiones - EVAP',
                'severity' => 'Baja',
                'causes' => ['Tapa de gasolina floja', 'Fugas en líneas EVAP', 'Válvula purge defectuosa'],
                'solution' => 'Verificar tapa de gasolina, revisar sistema EVAP'
            ],
            'P0507' => [
                'description' => 'Sistema Ralentí RPM Mayor al Esperado',
                'system' => 'Motor - Ralentí',
                'severity' => 'Baja',
                'causes' => ['Válvula IAC sucia', 'Fuga de vacío', 'Throttle body sucio'],
                'solution' => 'Limpiar throttle body y válvula IAC'
            ],
            'P0171' => [
                'description' => 'Sistema Muy Pobre (Banco 1)',
                'system' => 'Motor - Combustible',
                'severity' => 'Alta',
                'causes' => ['Fuga en admisión', 'Sensor O2 defectuoso', 'Baja presión de combustible', 'Inyectores obstruidos'],
                'solution' => 'Revisar fugas, presión de combustible y sensores'
            ],
            'P0128' => [
                'description' => 'Temperatura Termostato Refrigerante Bajo Regulación',
                'system' => 'Enfriamiento',
                'severity' => 'Baja',
                'causes' => ['Termostato atascado abierto', 'Sensor ECT defectuoso'],
                'solution' => 'Reemplazar termostato'
            ],
            'P0401' => [
                'description' => 'Flujo Recirculación Gases Escape Insuficiente',
                'system' => 'Emisiones - EGR',
                'severity' => 'Media',
                'causes' => ['Válvula EGR obstruida', 'Sensor DPFE defectuoso'],
                'solution' => 'Limpiar o reemplazar válvula EGR'
            ],
            'P0440' => [
                'description' => 'Fallo Sistema Control Emisiones Evaporativas',
                'system' => 'Emisiones - EVAP',
                'severity' => 'Media',
                'causes' => ['Fugas en sistema EVAP', 'Tapa de gasolina defectuosa'],
                'solution' => 'Revisar sistema EVAP completo'
            ],
            'P0455' => [
                'description' => 'Fuga Grande Sistema EVAP Detectada',
                'system' => 'Emisiones - EVAP',
                'severity' => 'Media',
                'causes' => ['Tapa de gasolina floja o faltante', 'Fuga grande en sistema EVAP'],
                'solution' => 'Verificar tapa de gasolina, revisar fugas EVAP'
            ]
        ];
    }

    private function getDtcDatabase() {
        // Extensive OBD2 DTC database with technical details
        $dtcs = [
            // P0XXX - Powertrain - Sensores MAF/MAP
            'P0001' => ['description' => 'Circuito Regulador Volumen Combustible Abierto', 'system' => 'Motor - Combustible', 'severity' => 'Alta', 'voltage' => '12V referencia, 0-5V señal', 'signal' => 'PWM/Analógico', 'causes' => ['Regulador de presión defectuoso', 'Cableado abierto o cortado', 'Conector corroído', 'ECM defectuoso'], 'solution' => 'Verificar continuidad en cableado. Medir resistencia del regulador (normal 5-15Ω). Si es correcto, revisar señal PWM con osciloscopio.'],
            'P0002' => ['description' => 'Circuito Regulador Volumen Combustible Rango/Desempeño', 'system' => 'Motor - Combustible', 'severity' => 'Alta', 'voltage' => '0-5V análogo', 'signal' => 'Variable', 'causes' => ['Desviación de presión de combustible', 'Sensor de presión defectuoso', 'Obstrucción en línea de retorno'], 'solution' => 'Verificar presión de combustible con manómetro (especificación típica 58-62 PSI). Reemplazar filtro de combustible.'],
            
            // Sensores MAF/MAP completos
            'P0100' => ['description' => 'Fallo Circuito Sensor MAF', 'system' => 'Motor - Entrada de Aire', 'severity' => 'Alta', 'voltage' => '0-5V', 'signal' => 'Analógico frecuencia variable', 'causes' => ['Sensor MAF dañado', 'Cableado dañado', 'Filtro de aire saturado', 'Ducto de admisión roto'], 'solution' => 'Limpiar sensor MAF con limpiador específico. Verificar continuidad en cables. Reemplazar si resistencia fuera de rango (2.5-4.5kΩ).', 'related_codes' => ['P0101', 'P0102', 'P0103', 'P0171', 'P0172']],
            'P0101' => ['description' => 'Circuito MAF Rango/Desempeño', 'system' => 'Motor - Entrada de Aire', 'severity' => 'Media', 'voltage' => '0.5-4.5V', 'signal' => 'Analógico variable con flujo', 'causes' => ['Sensor MAF desviado', 'Filtro de aire obstruido', 'Fuga post-MAF', 'Conexión suelta'], 'solution' => 'Limpiar MAF con limpiador de componentes electrónicos. Verificar sellos del ducto de aire. Lectura típica en ralentí: 0.5-1.5V.'],
            'P0102' => ['description' => 'Circuito MAF Voltaje Bajo', 'system' => 'Motor - Entrada de Aire', 'severity' => 'Alta', 'voltage' => '<0.2V', 'signal' => 'Cortocircuito a masa', 'causes' => ['Cable a masa', 'Sensor MAF interno en corto', 'Conector mojado', 'Referencia de 5V perdida'], 'solution' => 'Verificar voltaje de referencia 5V en conector. Revisar resistencia entre señal y masa (>10MΩ). Reemplazar MAF si es necesario.'],
            'P0103' => ['description' => 'Circuito MAF Voltaje Alto', 'system' => 'Motor - Entrada de Aire', 'severity' => 'Alta', 'voltage' => '>4.8V', 'signal' => 'Cortocircuito a voltaje', 'causes' => ['Cable cortado tocando 12V', 'Sensor interno dañado', 'Circuito de referencia en corto'], 'solution' => 'Desconectar MAF y medir voltaje en terminal de señal. Si sigue alto, revisar cableado. Normal debe ser <0.2V desconectado.'],
            'P0107' => ['description' => 'Circuito MAP Voltaje Bajo', 'system' => 'Motor - Admisión', 'severity' => 'Alta', 'voltage' => '<0.2V', 'signal' => 'Analógico corto a masa', 'causes' => ['Sensor MAP dañado', 'Vacío excesivo', 'Cable de señal a masa', 'Toma de vacío obstruida'], 'solution' => 'Verificar conexión de vacío al MAP. Medir voltaje: debe subir al quitar vacío (0V=alto vacío, 4.5V=sin vacío). Reemplazar sensor si no responde.'],
            'P0108' => ['description' => 'Circuito MAP Voltaje Alto', 'system' => 'Motor - Admisión', 'severity' => 'Alta', 'voltage' => '>4.8V', 'signal' => 'Analógico abierto o corto a 5V', 'causes' => ['Sensor MAP dañado', 'Línea de vacío rota', 'Cable de referencia en corto', 'Sensor desconectado'], 'solution' => 'Verificar línea de vacío del MAP. Con motor en ralentí, voltaje típico: 0.8-1.5V. En WOT debe subir a 4.5V.'],
            
            // Temperatura
            'P0110' => ['description' => 'Circuito Sensor Temperatura Aire Admisión', 'system' => 'Motor - Entrada', 'severity' => 'Baja', 'voltage' => '0-5V variable', 'signal' => 'NTC - resistencia variable', 'causes' => ['Sensor IAT defectuoso', 'Conexión suelta', 'Termistor desviado'], 'solution' => 'Medir resistencia del sensor. A 20°C: ~2500Ω. A 80°C: ~300Ω. Verificar con tabla de resistencia vs temperatura del fabricante.'],
            'P0112' => ['description' => 'Circuito IAT Voltaje Bajo', 'system' => 'Motor - Entrada', 'severity' => 'Baja', 'voltage' => '<0.2V', 'signal' => 'Corto a masa', 'causes' => ['Cableado en corto a masa', 'Sensor interno en corto', 'Terminal de señal tocando chasis'], 'solution' => 'Desconectar sensor. Si voltaje sube, sensor defectuoso. Si permanece bajo, revisar cableado entre sensor y ECM.'],
            'P0113' => ['description' => 'Circuito IAT Voltaje Alto', 'system' => 'Motor - Entrada', 'severity' => 'Baja', 'voltage' => '>4.8V', 'signal' => 'Circuito abierto', 'causes' => ['Sensor desconectado', 'Cable de señal roto', 'Conector dañado', 'Sensor termistor abierto'], 'solution' => 'Verificar resistencia del sensor. Lectura infinita = sensor abierto. Verificar continuidad en cableado de señal.'],
            'P0115' => ['description' => 'Circuito Sensor Temperatura Refrigerante', 'system' => 'Enfriamiento', 'severity' => 'Media', 'voltage' => 'Variable 0-5V', 'signal' => 'NTC - negativo', 'causes' => ['Sensor ECT defectuoso', 'Conexión corroída', 'Bajo nivel de refrigerante'], 'solution' => 'Verificar resistencia del sensor. Frío (20°C): ~2500Ω. Caliente (90°C): ~250Ω. Reemplazar termostato si no llega a temperatura.'],
            'P0117' => ['description' => 'Circuito ECT Voltaje Bajo', 'system' => 'Enfriamiento', 'severity' => 'Media', 'voltage' => '<0.2V', 'signal' => 'Corto a masa', 'causes' => ['Sensor ECT en corto', 'Cableado a masa', 'Refriado pérdida constante'], 'solution' => 'Desconectar ECT. Voltaje debe subir a 5V. Si no sube, revisar cableado. Sensor en corto mostrará temperatura excesivamente alta.'],
            'P0118' => ['description' => 'Circuito ECT Voltaje Alto', 'system' => 'Enfriamiento', 'severity' => 'Media', 'voltage' => '>4.8V', 'signal' => 'Circuito abierto', 'causes' => ['Sensor desconectado', 'Sensor termistor abierto', 'Cable roto'], 'solution' => 'Medir resistencia del sensor. Lectura infinita indica sensor abierto. ECU leerá temperatura extremadamente baja (-40°C).'],
            
            // TPS - Throttle Position Sensor
            'P0120' => ['description' => 'Circuito Sensor Posición Acelerador', 'system' => 'Motor - Acelerador', 'severity' => 'Alta', 'voltage' => '0-5V', 'signal' => 'Potenciómetro variable', 'causes' => ['TPS desgastado', 'Cuerpo de acelerador sucio', 'Cable suelto', 'Sensor de pedales desalineado'], 'solution' => 'Limpiar cuerpo de acelerador. Verificar voltaje TPS: cerrado 0.5V, WOT 4.5V. Verificar continuidad suave al abrir/cerrar. Sin saltos.'],
            'P0121' => ['description' => 'Circuito TPS Rango/Desempeño', 'system' => 'Motor - Acelerador', 'severity' => 'Alta', 'voltage' => '0.5-4.5V', 'signal' => 'Variable no lineal', 'causes' => ['Desgaste en pistas del potenciómetro', 'Zona muerta en sensor', 'Cableado intermitente'], 'solution' => 'Con multímetro en escala continuidad, verificar variación suave desde 0.5V hasta 4.5V. Si hay saltos o zonas sin lectura, reemplazar TPS.'],
            'P0122' => ['description' => 'Circuito TPS Voltaje Bajo', 'system' => 'Motor - Acelerador', 'severity' => 'Alta', 'voltage' => '<0.2V', 'signal' => 'Corto a masa', 'causes' => ['Pista del potenciómetro rota', 'Cable a masa', 'Conector cortado'], 'solution' => 'Desconectar TPS. Si voltaje sube a 5V en circuito de referencia, el sensor está en corto. Reemplazar TPS y verificar cuerpo de acelerador.'],
            'P0123' => ['description' => 'Circuito TPS Voltaje Alto', 'system' => 'Motor - Acelerador', 'severity' => 'Alta', 'voltage' => '>4.8V', 'signal' => 'Circuito abierto o corto a 5V', 'causes' => ['Sensor desconectado', 'Resistencia interna abierta', 'Cable de referencia en corto'], 'solution' => 'Verificar continuidad entre ECM y sensor. Referencia debe ser 5V estable. Señal debe variar 0.5-4.5V. Reemplazar si no responde.'],
            
            // Sistema de enfriamiento
            'P0128' => ['description' => 'Temperatura Termostato Refrigerante Bajo Regulación', 'system' => 'Enfriamiento', 'severity' => 'Baja', 'voltage' => 'N/A mecánico', 'signal' => 'Temperatura no alcanza mínimo', 'causes' => ['Termostato atascado abierto', 'Sensor ECT desviado', 'Fuga de refrigerante', 'Ventilador siempre activo'], 'solution' => 'Verificar termostato: Motor debe alcanzar 70-90°C en 10 minutos. Si se queda frío, reemplazar termostato. Verificar resistencia sensor ECT.'],
            
            // Sensores O2 - Oxígeno
            'P0130' => ['description' => 'Circuito Sensor O2 Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '0.1-0.9V', 'signal' => 'Analógico variable rápido', 'causes' => ['Sensor O2 desgastado', 'Fuga en escape', 'Sensor enriquecido de carbono', 'Cable dañado'], 'solution' => 'Sensor debe oscilar 0.1-0.9V cada 1-2 segundos. Si es lento (>5 seg) o constante, reemplazar. Verificar fugas en escape antes.', 'related_codes' => ['P0131', 'P0132', 'P0133', 'P0135', 'P0140', 'P0141', 'P0171']],
            'P0131' => ['description' => 'Circuito O2 Voltaje Bajo Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '<0.1V constante', 'signal' => 'Corto a masa o mezcla pobre', 'causes' => ['Sensor en corto', 'Fuga de vacío grande', 'Inyectores obstruidos', 'Presión de combustible baja'], 'solution' => 'Verificar fugas en admisión con detector de humo. Revisar presión de combustible (debe ser 58-62 PSI). Verificar señal O2 con osciloscopio.'],
            'P0132' => ['description' => 'Circuito O2 Voltaje Alto Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '>0.9V constante', 'signal' => 'Corto a voltaje o mezcla rica', 'causes' => ['Sensor en corto a 12V', 'Inyectores goteando', 'Presión de combustible alta', 'Sensor de MAP defectuoso'], 'solution' => 'Desconectar O2. Si voltaje sigue alto, revisar cableado. Si baja, reemplazar sensor. Verificar inyectores y presión de combustible.'],
            'P0133' => ['description' => 'O2 Respuesta Lenta Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '0.1-0.9V lento', 'signal' => 'Respuesta >5 segundos', 'causes' => ['Sensor O2 viejo', 'Contaminación de aceite o antifreeze', 'Silenciador catalítico obstruido'], 'solution' => 'Tiempo de respuesta debe ser <2 segundos. Si es lento, reemplazar sensor O2. Verificar catalizador no esté obstruyendo flujo.'],
            'P0135' => ['description' => 'Circuito Calentador O2 Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Resistencia calefactor', 'causes' => ['Resistencia calefactor abierta', 'Cable de calentador roto', 'Fusible quemado'], 'solution' => 'Medir resistencia del calefactor: 3-15Ω es normal. Verificar voltaje en calentador con motor frío: debe ser 12V. Reemplazar sensor si resistencia infinita.'],
            'P0140' => ['description' => 'Circuito Sensor O2 Banco 1 Sensor 2', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '0.1-0.9V', 'signal' => 'Analógico (sensor downstream)', 'causes' => ['Sensor O2 trasero defectuoso', 'Catalizador dañado', 'Fuga en escape post-catalizador'], 'solution' => 'Sensor downstream debe mantenerse estable 0.45V con catalizador bueno. Si oscila como el upstream, catalizador está dañado.'],
            'P0141' => ['description' => 'Circuito Calentador O2 Banco 1 Sensor 2', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'PWM o constante', 'causes' => ['Calefactor O2 dañado', 'Cableado corroído', 'Conector quemado'], 'solution' => 'Igual que P0135. Medir resistencia calefactor (3-15Ω). Verificar voltaje de alimentación. Reemplazar sensor si no calienta.'],
            
            // Mezcla combustible/aire
            'P0171' => ['description' => 'Sistema Muy Pobre Banco 1', 'system' => 'Motor - Combustible', 'severity' => 'Alta', 'voltage' => 'O2 <0.45V constante', 'signal' => 'Adaptación de combustible máxima +', 'causes' => ['Fuga en admisión post-MAF', 'Sensor MAF desviado', 'Presión de combustible baja', 'Inyectores obstruidos', 'Fuga en válvula PCV'], 'solution' => 'Verificar fugas con detector de humo. Limpiar MAF. Medir presión combustible (58-62 PSI). Revisar trim de combustible: si es +25%, buscar fuga de aire.', 'related_codes' => ['P0172', 'P0174', 'P0175', 'P0101', 'P0131']],
            'P0172' => ['description' => 'Sistema Muy Rico Banco 1', 'system' => 'Motor - Combustible', 'severity' => 'Alta', 'voltage' => 'O2 >0.45V constante', 'signal' => 'Adaptación de combustible máxima -', 'causes' => ['Sensor MAF reportando más aire del real', 'Inyectores goteando', 'Presión de combustible alta', 'Termostato frío', 'Sensor ECT defectuoso'], 'solution' => 'Revisar trim de combustible: si es -25%, verificar presión combustible. Si es alta (>65 PSI), revisar regulador. Limpiar inyectores.', 'related_codes' => ['P0171', 'P0175', 'P0174', 'P0132']],
            'P0174' => ['description' => 'Sistema Muy Pobre Banco 2', 'system' => 'Motor - Combustible', 'severity' => 'Alta', 'voltage' => 'O2 <0.45V', 'signal' => 'Igual P0171', 'causes' => ['Fuga específica bank 2', 'Inyectores bank 2 obstruidos', 'Fuga en junta de admisión bank 2'], 'solution' => 'Comparar trims entre banks. Si solo bank 2 es lean, revisar inyectores de ese bank. Usar estetoscopio para detectar fugas de vacío.'],
            'P0175' => ['description' => 'Sistema Muy Rico Banco 2', 'system' => 'Motor - Combustible', 'severity' => 'Alta', 'voltage' => 'O2 >0.45V', 'signal' => 'Igual P0172', 'causes' => ['Inyectores bank 2 goteando', 'Presión de combustible alta', 'Obstrucción en bank 2 admisión'], 'solution' => 'Aislar el problema al bank 2. Revisar inyectores específicos. Realizar prueba de balance de cilindros.'],
            
            // Inyectores
            'P0200' => ['description' => 'Fallo Circuito Inyector', 'system' => 'Motor - Inyectores', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Pulsos de inyección', 'causes' => ['Circuito de inyectores abierto', 'ECM driver de inyectores dañado', 'Cableado general inyectores'], 'solution' => 'Verificar resistencia de todos los inyectores (12-16Ω típico). Revisar voltaje en común de inyectores: debe ser 12V con ignición ON.'],
            'P0201' => ['description' => 'Circuito Inyector Cilindro 1', 'system' => 'Motor - Inyectores', 'severity' => 'Alta', 'voltage' => '12V pulso', 'signal' => 'PWM 1-10ms', 'causes' => ['Inyector #1 dañado', 'Cable a inyector #1 roto', 'Conector corroído'], 'solution' => 'Desconectar inyector #1. Medir resistencia: 12-16Ω normal. Verificar continuidad entre ECM y inyector. Reemplazar inyector si resistencia fuera de rango.'],
            'P0202' => ['description' => 'Circuito Inyector Cilindro 2', 'system' => 'Motor - Inyectores', 'severity' => 'Alta', 'voltage' => '12V pulso', 'signal' => 'PWM', 'causes' => ['Inyector #2 dañado', 'Cable roto', 'Bobina de inyector abierta'], 'solution' => 'Igual P0201 pero para cilindro 2. Verificar resistencia y continuidad.'],
            'P0203' => ['description' => 'Circuito Inyector Cilindro 3', 'system' => 'Motor - Inyectores', 'severity' => 'Alta', 'voltage' => '12V pulso', 'signal' => 'PWM', 'causes' => ['Inyector #3 dañado', 'Problema de cableado'], 'solution' => 'Igual P0201 pero para cilindro 3.'],
            'P0204' => ['description' => 'Circuito Inyector Cilindro 4', 'system' => 'Motor - Inyectores', 'severity' => 'Alta', 'voltage' => '12V pulso', 'signal' => 'PWM', 'causes' => ['Inyector #4 dañado', 'Problema de cableado'], 'solution' => 'Igual P0201 pero para cilindro 4.'],
            
            // Fallas de cilindro
            'P0300' => ['description' => 'Fallas Aleatorias Múltiples Cilindros', 'system' => 'Motor - Ignición', 'severity' => 'Crítica', 'voltage' => 'N/A', 'signal' => 'Variación de RPM', 'causes' => ['Bujías gastadas', 'Bobinas débiles', 'Inyectores sucios', 'Baja compresión', 'Fuga de vacío'], 'solution' => 'Realizar prueba de compresión (>100 PSI). Verificar chispa con probador. Revisar inyectores con estetoscopio. Escuchar si hacen clic.', 'related_codes' => ['P0301', 'P0302', 'P0303', 'P0304', 'P0305', 'P0306', 'P0200']],
            'P0301' => ['description' => 'Falla Detectada Cilindro 1', 'system' => 'Motor - Ignición', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Variación RPM cilindro 1', 'causes' => ['Bujía #1 dañada', 'Bobina #1 defectuosa', 'Inyector #1 obstruido', 'Válvula quemada'], 'solution' => 'Intercambiar bujía/bobina #1 con otro cilindro. Si falla sigue, revisar compresión y válvulas. Si se mueve, reemplazar componente.', 'related_codes' => ['P0300', 'P0201', 'P0302', 'P0303', 'P0304']],
            'P0302' => ['description' => 'Falla Detectada Cilindro 2', 'system' => 'Motor - Ignición', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Variación RPM cilindro 2', 'causes' => ['Bujía #2', 'Bobina #2', 'Inyector #2', 'Válvula'], 'solution' => 'Igual que P0301 pero para cilindro 2.', 'related_codes' => ['P0300', 'P0202', 'P0301', 'P0303', 'P0304']],
            'P0303' => ['description' => 'Falla Detectada Cilindro 3', 'system' => 'Motor - Ignición', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Variación RPM cilindro 3', 'causes' => ['Bujía #3', 'Bobina #3', 'Inyector #3'], 'solution' => 'Igual que P0301 pero para cilindro 3.', 'related_codes' => ['P0300', 'P0203', 'P0301', 'P0302', 'P0304']],
            'P0304' => ['description' => 'Falla Detectada Cilindro 4', 'system' => 'Motor - Ignición', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Variación RPM cilindro 4', 'causes' => ['Bujía #4', 'Bobina #4', 'Inyector #4'], 'solution' => 'Igual que P0301 pero para cilindro 4.', 'related_codes' => ['P0300', 'P0204', 'P0301', 'P0302', 'P0303']],
            'P0305' => ['description' => 'Falla Detectada Cilindro 5', 'system' => 'Motor - Ignición', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Variación RPM cilindro 5', 'causes' => ['Bujía #5', 'Bobina #5', 'Inyector #5'], 'solution' => 'Igual que P0301 pero para cilindro 5.'],
            'P0306' => ['description' => 'Falla Detectada Cilindro 6', 'system' => 'Motor - Ignición', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Variación RPM cilindro 6', 'causes' => ['Bujía #6', 'Bobina #6', 'Inyector #6'], 'solution' => 'Igual que P0301 pero para cilindro 6.'],
            
            // Sensores de posición
            'P0325' => ['description' => 'Circuito Sensor Detonación Banco 1', 'system' => 'Motor - Detonación', 'severity' => 'Media', 'voltage' => '0-5V AC', 'signal' => 'Señal AC de vibración', 'causes' => ['Sensor de detonación suelto', 'Cableado dañado', 'Ruido mecánico excesivo'], 'solution' => 'Sensor genera AC cuando detecta detonación. Verificar torque de instalación (16-20 Nm). No debe haber 12V en cables.'],
            'P0335' => ['description' => 'Circuito Sensor Posición Cigüeñal', 'system' => 'Motor - CKP', 'severity' => 'Crítica', 'voltage' => '0-5V pulsos', 'signal' => 'Hall o reluctancia variable', 'causes' => ['Sensor CKP dañado', 'Anillo de dientes sucio', 'Cableado roto', 'Sensor desajustado'], 'solution' => 'Sensor CKP es crítico. Verificar resistencia: 500-1500Ω típico. Verificar voltaje AC durante arranque (>200mV). Limpiar anillo de dientes.', 'related_codes' => ['P0336', 'P0340', 'P0016', 'P0017', 'P0325']],
            'P0336' => ['description' => 'Circuito CKP Rango/Desempeño', 'system' => 'Motor - CKP', 'severity' => 'Crítica', 'voltage' => 'Variable', 'signal' => 'Pulsos irregulares', 'causes' => ['Anillo de dientes dañado', 'Sensor desalineado', 'Interferencia electromagnética'], 'solution' => 'Inspeccionar anillo de dientes en cigüeñal. No debe tener daños ni dientes faltantes. Verificar distancia sensor: 0.5-1.5mm típico.'],
            'P0340' => ['description' => 'Circuito Sensor Posición Árbol Levas', 'system' => 'Motor - CMP', 'severity' => 'Alta', 'voltage' => '0-5V pulsos', 'signal' => 'Digital o analógico', 'causes' => ['Sensor CMP dañado', 'Sincronización de cadena/correa', 'Cableado'], 'solution' => 'Sensor CMP para sincronización de inyección. Verificar resistencia. Con osciloscopio verificar onda cuadrada estable durante rotación.', 'related_codes' => ['P0016', 'P0017', 'P0335', 'P0336', 'P0010']],
            
            // EGR - Recirculación de gases escape
            'P0400' => ['description' => 'Flujo Recirculación Gases Escape', 'system' => 'Emisiones - EGR', 'severity' => 'Media', 'voltage' => '0-5V posición', 'signal' => 'Realimentación posición', 'causes' => ['Válvula EGR atascada', 'Sensor DPFE defectuoso', 'Conductos de EGR obstruidos'], 'solution' => 'Verificar operación de válvula EGR con scanner. Debe abrirse 0-100%. Limpiar carbonización. Sensor DPFE debe variar 0.5-4.5V con presión.', 'related_codes' => ['P0401', 'P0402', 'P0420']],
            'P0401' => ['description' => 'Flujo EGR Insuficiente', 'system' => 'Emisiones - EGR', 'severity' => 'Media', 'voltage' => 'DPFE bajo', 'signal' => 'Sin cambio de presión', 'causes' => ['Válvula EGR obstruida cerrada', 'Conductos bloqueados', 'Diafragma roto'], 'solution' => 'Aplicar vacío manual a válvula EGR: debe abrirse. Si no, reemplazar. Limpiar conductos con limpiador de carbón. Verificar DPFE responda a presión.', 'related_codes' => ['P0400', 'P0402', 'P0420']],
            'P0402' => ['description' => 'Flujo EGR Excesivo', 'system' => 'Emisiones - EGR', 'severity' => 'Media', 'voltage' => 'DPFE alto', 'signal' => 'Demasiado flujo', 'causes' => ['Válvula EGR atascada abierta', 'Ralentí inestable', 'Stalling'], 'solution' => 'Motor puede tener ralentí irregular o apagarse. Desconectar EGR eléctricamente. Si mejora, válvula está abierta. Reemplazar válvula EGR.'],
            'P0410' => ['description' => 'Sistema Inyección Aire Secundario', 'system' => 'Emisiones - Aire', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Bomba de aire secundario', 'causes' => ['Bomba de aire quemada', 'Válvula de control dañada', 'Filtro de aire obstruido'], 'solution' => 'Sistema solo opera en frío. Verificar bomba funcione cuando se arranca frío. Debe inyectar aire al catalizador por 1-2 minutos. Reemplazar bomba si no funciona.'],
            
            // Catalizador
            'P0420' => ['description' => 'Eficiencia Catalizador Debajo Umbral', 'system' => 'Emisiones - Catalizador', 'severity' => 'Media', 'voltage' => 'O2 similar', 'signal' => 'Downstream = Upstream', 'causes' => ['Catalizador deteriorado', 'Sensor O2 trasero defectuoso', 'Fugas en escape'], 'solution' => 'Catalizador debe reducir emisiones. O2 downstream debe ser estable ~0.45V. Si oscila como upstream, catalizador está dañado. Verificar fugas antes de reemplazar catalizador.', 'related_codes' => ['P0430', 'P0140', 'P0141', 'P0130']],
            'P0430' => ['description' => 'Eficiencia Catalizador Bajo Umbral Banco 2', 'system' => 'Emisiones - Catalizador', 'severity' => 'Media', 'voltage' => 'O2 similar', 'signal' => 'Igual P0420', 'causes' => ['Catalizador bank 2 dañado', 'Fuga específica bank 2'], 'solution' => 'Igual diagnóstico que P0420 pero específico para bank 2 del motor.'],
            
            // Sistema EVAP
            'P0440' => ['description' => 'Sistema Control Emisiones Evaporativas', 'system' => 'Emisiones - EVAP', 'severity' => 'Media', 'voltage' => '0-5V presión', 'signal' => 'Sensor de presión EVAP', 'causes' => ['Fuga en sistema EVAP', 'Tapa de gasolina defectuosa', 'Válvula purge dañada'], 'solution' => 'Verificar tapa de gasolina cierra bien. Revisar sistema con detector de humo. Válvula purge debe estar cerrada en ralentí. Reemplaar si gotea.', 'related_codes' => ['P0441', 'P0442', 'P0446', 'P0455']],
            'P0441' => ['description' => 'Flujo Purga EVAP Incorrecto', 'system' => 'Emisiones - EVAP', 'severity' => 'Media', 'voltage' => 'PWM', 'signal' => 'Control de purga', 'causes' => ['Válvula purge atascada abierta', 'Fuga en línea de purga', 'Vacío constante'], 'solution' => 'Válvula purge no debe dejar pasar vacío cuando está cerrada. Aplicar vacío manual: debe sostenerlo. Si no, reemplazar válvula.'],
            'P0442' => ['description' => 'Fuga Pequeña Sistema EVAP Detectada', 'system' => 'Emisiones - EVAP', 'severity' => 'Baja', 'voltage' => 'Presión cae', 'signal' => 'Test de presión falla', 'causes' => ['Tapa de gasolina floja', 'Fuga pequeña en líneas', 'Sensor de presión EVAP'], 'solution' => 'Asegurar tapa de gasolina hasta el clic. Revisar todas las líneas de vapor. Fuga pequeña es difícil de encontrar sin detector de humo.'],
            'P0446' => ['description' => 'Circuito Control Ventilación EVAP', 'system' => 'Emisiones - EVAP', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Solenoide de ventilación', 'causes' => ['Solenoide de vent dañado', 'Cableado', 'Filtro de vent obstruido'], 'solution' => 'Solenoide de vent debe abrirse durante test EVAP. Verificar resistencia: 20-50Ω típico. Reemplazar si está abierto o en corto.'],
            'P0455' => ['description' => 'Fuga Grande Sistema EVAP Detectada', 'system' => 'Emisiones - EVAP', 'severity' => 'Media', 'voltage' => 'Presión no se mantiene', 'signal' => 'Fuga grande', 'causes' => ['Tapa de gasolina faltante', 'Línea de vapor rota', 'Tanque dañado'], 'solution' => 'Verificar tapa de gasolina presente y apretada. Inspeccionar líneas de vapor visualmente. Fuga grande suele ser obvia al inspeccionar.'],
            
            // Transmisión y velocidad
            'P0500' => ['description' => 'Sensor Velocidad Vehículo', 'system' => 'Transmisión', 'severity' => 'Media', 'voltage' => '0-12V pulsos', 'signal' => 'Inductivo o Hall', 'causes' => ['Sensor VSS dañado', 'Engranaje excitador roto', 'Cableado'], 'solution' => 'Sensor en caja de transmisión. Verificar resistencia: 500-1500Ω. Con ruedas levantadas, verificar señal AC al girar (>100mV).'],
            'P0505' => ['description' => 'Sistema Control Ralentí', 'system' => 'Motor - Ralentí', 'severity' => 'Media', 'voltage' => '0-12V', 'signal' => 'Control válvula IAC', 'causes' => ['Válvula IAC sucia', 'Fuga de vacío', 'Throttle body sucio'], 'solution' => 'Limpiar throttle body y válvula IAC con limpiador de carbón. Reajustar aprendizaje de ralentí con scanner.'],
            'P0507' => ['description' => 'Sistema Ralentí RPM Alto', 'system' => 'Motor - Ralentí', 'severity' => 'Baja', 'voltage' => 'N/A', 'signal' => 'RPM > especificación', 'causes' => ['Fuga de vacío', 'IAC atascada abierta', 'Acelerador atascado', 'TPS desajustado'], 'solution' => 'RPM alto indica aire extra. Buscar fugas con carb cleaner. Verificar cable de acelerador no esté ajustado muy alto.'],
            
            // Sistema eléctrico
            'P0560' => ['description' => 'Voltaje del Sistema', 'system' => 'Eléctrico', 'severity' => 'Media', 'voltage' => '9-16V', 'signal' => 'Voltaje de batería', 'causes' => ['Batería débil', 'Alternador defectuoso', 'Conexiones sueltas'], 'solution' => 'Verificar voltaje de batería: arranque >10V, marcha 13.5-14.5V. Si bajo, revisar alternador y batería.'],
            'P0562' => ['description' => 'Voltaje Sistema Bajo', 'system' => 'Eléctrico', 'severity' => 'Media', 'voltage' => '<10.5V', 'signal' => 'Voltaje bajo ECM', 'causes' => ['Batería descargada', 'Cable de batería oxidado', 'Alternador no carga'], 'solution' => 'Cargar o reemplazar batería. Verificar bornes limpios. Con motor encendido: debe haber 13.5-14.5V. Si no, reemplazar alternador.'],
            'P0563' => ['description' => 'Voltaje Sistema Alto', 'system' => 'Eléctrico', 'severity' => 'Media', 'voltage' => '>16V', 'signal' => 'Sobrevoltaje', 'causes' => ['Regulador de alternador dañado', 'Conexión suelta', 'Batería desconectada mientras corre'], 'solution' => 'Voltaje >16V daña electrónica. Verificar regulador de alternador inmediatamente. Desconectar alternador si el voltaje sigue subiendo.'],
            
            // PCM
            'P0600' => ['description' => 'Enlace Comunicación Serial', 'system' => 'PCM', 'severity' => 'Crítica', 'voltage' => '5V datos', 'signal' => 'Comunicación serial', 'causes' => ['ECM dañado', 'Línea de datos cortada', 'Módulo incompatible'], 'solution' => 'Problema de comunicación interna del ECM. Verificar alimentación y masa del ECM. Si persiste, ECM puede necesitar reprogramación o reemplazo.'],
            'P0602' => ['description' => 'Error Programación Módulo Control', 'system' => 'PCM', 'severity' => 'Crítica', 'voltage' => 'N/A', 'signal' => 'Memoria flash', 'causes' => ['ECM sin programar', 'Memoria corrupta', 'Actualización fallida'], 'solution' => 'ECM necesita reprogramación con herramienta de dealer. No intentar reparar sin equipo especializado.'],
            'P0605' => ['description' => 'Memoria ROM Módulo Control', 'system' => 'PCM', 'severity' => 'Crítica', 'voltage' => 'N/A', 'signal' => 'ROM interna', 'causes' => ['Memoria ROM dañada', 'ECM defectuoso'], 'solution' => 'Fallo interno del ECM. Requiere reemplazo del módulo y reprogramación.'],
            
            // Transmisión
            'P0700' => ['description' => 'Sistema Control Transmisión', 'system' => 'Transmisión', 'severity' => 'Alta', 'voltage' => 'N/A', 'signal' => 'Código general TCM', 'causes' => ['Códigos específicos presentes', 'TCM detectó fallo', 'Requiere escanear más'], 'solution' => 'Código general. Escanear códigos específicos de transmisión (P07xx). Revisar nivel y condición del fluido ATF.'],
            'P0705' => ['description' => 'Circuito Sensor Rango Transmisión', 'system' => 'Transmisión', 'severity' => 'Media', 'voltage' => '0-5V o resistencia', 'signal' => 'Posición selector', 'causes' => ['Sensor de rango dañado', 'Cable desconectado', 'Selector desajustado'], 'solution' => 'Sensor indica posición P, R, N, D. Verificar voltaje/resistencia cambia con selector. Reemplazar sensor si no responde.'],
            'P0715' => ['description' => 'Circuito Sensor Velocidad Entrada/Turbina', 'system' => 'Transmisión', 'severity' => 'Alta', 'voltage' => '0-5V pulsos', 'signal' => 'Velocidad de entrada', 'causes' => ['Sensor de velocidad dañado', 'Cableado', 'Problema interno transmisión'], 'solution' => 'Sensor monitorea RPM de entrada de transmisión. Comparar con RPM de motor. Si no coinciden, deslizamiento del convertidor o sensor malo.'],
            'P0720' => ['description' => 'Circuito Sensor Velocidad Salida', 'system' => 'Transmisión', 'severity' => 'Alta', 'voltage' => '0-5V pulsos', 'signal' => 'Velocidad de salida', 'causes' => ['Sensor OSS dañado', 'Engranaje excitador dañado', 'Cableado'], 'solution' => 'Sensor monitorea velocidad de salida. Usado para cálculos de engrane. Verificar resistencia y señal. Comparar con velocidad de vehículo.'],
            'P0740' => ['description' => 'Circuito Embrague Convertidor Par', 'system' => 'Transmisión', 'severity' => 'Media', 'voltage' => '12V PWM', 'signal' => 'Control TCC', 'causes' => ['Solenoide TCC dañado', 'Convertidor bloqueado', 'Circuito eléctrico'], 'solution' => 'TCC bloquea convertidor en marcha directa. Verificar solenoide responda a comando del TCM. Si TCC atascado cerrado, vehículo muere en paradas.'],
            'P0750' => ['description' => 'Fallo Solenoide Cambio A', 'system' => 'Transmisión', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Control solenoide', 'causes' => ['Solenoide A dañado', 'Cuerpo de válvulas', 'Presión de línea'], 'solution' => 'Solenoide controla cambios de engrane. Verificar resistencia: 10-25Ω típico. Verificar aterrizaje. Si intermitente, reemplazar cuerpo de válvulas.'],
            'P0755' => ['description' => 'Fallo Solenoide Cambio B', 'system' => 'Transmisión', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Control solenoide', 'causes' => ['Solenoide B dañado', 'Obstrucción de válvula'], 'solution' => 'Igual que P0750 pero para solenoide B.'],
            'P0760' => ['description' => 'Fallo Solenoide Cambio C', 'system' => 'Transmisión', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Control solenoide', 'causes' => ['Solenoide C dañado'], 'solution' => 'Igual que P0750 pero para solenoide C.'],
            
            // VVT - Variable Valve Timing
            'P0010' => ['description' => 'Circuito Actuador Árbol Levas Admisión Abierto', 'system' => 'Motor - VVT', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Control VVT', 'causes' => ['Actuador VVT dañado', 'Cableado abierto', 'Válvula OCV defectuosa'], 'solution' => 'Verificar resistencia del solenoide OCV: 7-15Ω típico. Verificar comando PWM del ECM. Con 12V aplicado, válvula debe hacer clic.', 'related_codes' => ['P0011', 'P0012', 'P0013', 'P0014', 'P0016']],
            'P0011' => ['description' => 'Sincronización Árbol Levas Admisión Sobreavanzada', 'system' => 'Motor - VVT', 'severity' => 'Alta', 'voltage' => 'Realimentación CMP', 'signal' => 'Desviación de sincronización', 'causes' => ['Válvula OCV atascada', 'Cadena de tiempo estirada', 'Actuador dañado'], 'solution' => 'Verificar sincronización de cadena/correa. Con scanner, verificar grado de avance del árbol. Debe variar 0-30°. Reemplazar actuador si no responde.'],
            'P0012' => ['description' => 'Sincronización Árbol Levas Admisión Retrasada', 'system' => 'Motor - VVT', 'severity' => 'Alta', 'voltage' => 'Realimentación CMP', 'signal' => 'Retraso excesivo', 'causes' => ['Válvula OCV bloqueada', 'Presión de aceite baja', 'Filtro de OCV obstruido'], 'solution' => 'Verificar presión de aceite del motor (hot idle >10 PSI). Revisar filtro de válvula OCV. Limpiar o reemplazar válvula OCV.'],
            'P0013' => ['description' => 'Circuito Actuador Árbol Levas Escape Abierto', 'system' => 'Motor - VVT', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Control VVT escape', 'causes' => ['Solenoide de escape dañado', 'Cableado'], 'solution' => 'Igual que P0010 pero para árbol de escape.'],
            'P0014' => ['description' => 'Sincronización Árbol Levas Escape Sobreavanzada', 'system' => 'Motor - VVT', 'severity' => 'Alta', 'voltage' => 'Realimentación', 'signal' => 'Sincronización escape', 'causes' => ['Actuador de escape atascado'], 'solution' => 'Igual que P0011 pero para árbol de escape.'],
            'P0016' => ['description' => 'Correlación Posición Cigüeñal - Árbol Levas', 'system' => 'Motor - Sincronización', 'severity' => 'Crítica', 'voltage' => 'CKP vs CMP', 'signal' => 'Correlación fallida', 'causes' => ['Cadena/correa de tiempo saltada', 'Tensores dañados', 'Sincronización incorrecta'], 'solution' => '¡CRÍTICO! Apagar motor inmediatamente. Verificar alineación de marcas de sincronización. Si la cadena saltó, puede haber daño de válvulas. No intentar arrancar.'],
            'P0017' => ['description' => 'Correlación CKP-CMP Banco 1 Sensor B', 'system' => 'Motor - Sincronización', 'severity' => 'Crítica', 'voltage' => 'CKP vs CMP', 'signal' => 'Igual P0016', 'causes' => ['Cadena de escape desfasada', 'Actuador de escape dañado'], 'solution' => 'Igual que P0016 pero específico para árbol de escape. Verificar sincronización del segundo árbol.'],
            
            // Calefacción O2
            'P0030' => ['description' => 'Circuito Control Calentador O2 Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Resistencia calefactor', 'causes' => ['Calefactor O2 dañado', 'Cable de calentador'], 'solution' => 'Verificar resistencia calefactor: 3-15Ω. Si infinita, reemplazar sensor. Verificar voltaje 12V en calentador con motor frío.'],
            'P0031' => ['description' => 'Circuito Calentador O2 Bajo Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '<4V', 'signal' => 'Corto a masa', 'causes' => ['Cable de calentador a masa', 'Sensor interno en corto'], 'solution' => 'Desconectar sensor. Si voltaje sube a 12V en circuito, sensor defectuoso. Si sigue bajo, cableado en corto.'],
            'P0032' => ['description' => 'Circuito Calentador O2 Alto Banco 1 Sensor 1', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '12V constante', 'signal' => 'Corto a voltaje', 'causes' => ['Cable de control en corto a 12V', 'ECM driver dañado'], 'solution' => 'Verificar que ECM esté controlando calentador (debe ser PWM). Si siempre 12V, revisar cableado o ECM.'],
            'P0036' => ['description' => 'Circuito Control Calentador O2 Banco 1 Sensor 2', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Calefactor O2 downstream', 'causes' => ['Calefactor O2 #2 dañado'], 'solution' => 'Igual que P0030 pero para sensor 2 (tras catalizador).'],
            'P0037' => ['description' => 'Circuito Calentador O2 Bajo Banco 1 Sensor 2', 'system' => 'Emisiones - O2', 'severity' => 'Media', 'voltage' => '<4V', 'signal' => 'Corto a masa', 'causes' => ['Igual P0031'], 'solution' => 'Igual P0031 para sensor 2.'],
            
            // Chassis - ABS
            'C0035' => ['description' => 'Sensor Velocidad Rueda Delantera Izquierda', 'system' => 'ABS', 'severity' => 'Media', 'voltage' => '0-5V AC', 'signal' => 'Señal de velocidad', 'causes' => ['Sensor ABS LF dañado', 'Anillo reluctor roto', 'Cableado'], 'solution' => 'Sensor genera AC al girar. Verificar resistencia: 1000-2500Ω. Verificar voltaje AC >100mV a 1 rev/seg. Reemplazar si no genera señal.'],
            'C0040' => ['description' => 'Sensor Velocidad Rueda Delantera Derecha', 'system' => 'ABS', 'severity' => 'Media', 'voltage' => '0-5V AC', 'signal' => 'Señal velocidad RF', 'causes' => ['Sensor RF dañado'], 'solution' => 'Igual C0035 pero rueda delantera derecha.'],
            'C0045' => ['description' => 'Sensor Velocidad Rueda Trasera Izquierda', 'system' => 'ABS', 'severity' => 'Media', 'voltage' => '0-5V AC', 'signal' => 'Señal velocidad LR', 'causes' => ['Sensor LR dañado'], 'solution' => 'Igual C0035 pero rueda trasera izquierda.'],
            'C0050' => ['description' => 'Sensor Velocidad Rueda Trasera Derecha', 'system' => 'ABS', 'severity' => 'Media', 'voltage' => '0-5V AC', 'signal' => 'Señal velocidad RR', 'causes' => ['Sensor RR dañado'], 'solution' => 'Igual C0035 pero rueda trasera derecha.'],
            'C0060' => ['description' => 'Solenoide ABS Delantera Izquierda', 'system' => 'ABS', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Control de válvula ABS', 'causes' => ['Válvula ABS LF dañada', 'Módulo HCU defectuoso'], 'solution' => 'Problema en módulo hidráulico ABS. Verificar resistencia de solenoides: 2-8Ω. Requiere escáner ABS para diagnóstico detallado.'],
            'C0065' => ['description' => 'Solenoide ABS Delantera Derecha', 'system' => 'ABS', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Válvula RF', 'causes' => ['Válvula ABS RF dañada'], 'solution' => 'Igual C0060 para rueda RF.'],
            'C0075' => ['description' => 'Módulo Control ABS', 'system' => 'ABS', 'severity' => 'Crítica', 'voltage' => '12V', 'signal' => 'Módulo ABS', 'causes' => ['Módulo ABS dañado', 'Alimentación insuficiente', 'Comunicación CAN'], 'solution' => 'Verificar alimentación y masa del módulo ABS. Si correctas y sigue fallando, reemplazar módulo. Requiere programación.'],
            'C0121' => ['description' => 'Circuito Relé Válvula', 'system' => 'ABS', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Relé de bomba', 'causes' => ['Relé de bomba ABS dañado', 'Fusible quemado'], 'solution' => 'Verificar relé de bomba ABS. Cuando ABS activa, bomba debe funcionar 2-4 segundos. Si no, reemplazar relé o revisar bomba.'],
            
            // Airbag
            'B0001' => ['description' => 'Control Despliegue Conductor Etapa 1', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => 'Alto voltaje', 'signal' => 'Circuito de ignición', 'causes' => ['Conector de airbag suelto', 'Resistencia del airbag alta', 'Cable del reloj dañado'], 'solution' => '¡PRECAUCIÓN! Desconectar batería antes de trabajar. Resistencia airbag: 2-3Ω. Si >5Ω, revisar conector debajo del asiento y cable del reloj.'],
            'B0010' => ['description' => 'Control Despliegue Pasajero Etapa 1', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => 'Alto voltaje', 'signal' => 'Circuito pasajero', 'causes' => ['Airbag pasajero desconectado', 'Resistencia alta'], 'solution' => 'Igual B0001 pero para airbag pasajero. Verificar conector amarillo debajo del dash.'],
            'B0020' => ['description' => 'Control Despliegue Rodillas Conductor', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => 'Alto voltaje', 'signal' => 'Airbag de rodillas', 'causes' => ['Airbag de rodillas desconectado'], 'solution' => 'Algunos vehículos tienen airbag de protección de rodillas. Verificar conector.'],
            'B0100' => ['description' => 'Circuito Sensor Impacto Airbag', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => '5V', 'signal' => 'Sensor de impacto', 'causes' => ['Sensor de impacto dañado', 'Cableado'], 'solution' => 'Sensores de impacto generalmente en parte frontal. Verificar resistencia según especificación.'],
            'B0103' => ['description' => 'Circuito Sensor Impacto Voltaje Alto', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => '>5V', 'signal' => 'Corto a voltaje', 'causes' => ['Cableado en corto a 12V'], 'solution' => 'Revisar aislamiento de cables de sensor de impacto.'],
            'B0104' => ['description' => 'Circuito Sensor Impacto Voltaje Bajo', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => '<1V', 'signal' => 'Corto a masa', 'causes' => ['Cableado en corto a masa'], 'solution' => 'Revisar si cable de señal toca chasis.'],
            'B0134' => ['description' => 'Circuito Sensor Impacto Delantero Izquierdo', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => '5V', 'signal' => 'Sensor lateral', 'causes' => ['Sensor de puerta o pilar dañado'], 'solution' => 'Sensores de impacto lateral en puertas o pilares B.'],
            'B0144' => ['description' => 'Circuito Sensor Impacto Delantero Derecho', 'system' => 'Airbag', 'severity' => 'Crítica', 'voltage' => '5V', 'signal' => 'Sensor lateral', 'causes' => ['Sensor lateral derecho'], 'solution' => 'Igual B0134 lado derecho.'],
            'B0158' => ['description' => 'Circuito Indicador Despliegue Airbag', 'system' => 'Airbag', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Luz de airbag', 'causes' => ['Foco de luz quemado', 'Led dañado'], 'solution' => 'Problema de indicador visual, no crítico para operación pero debe repararse.'],
            'B0160' => ['description' => 'Circuito Indicador Desactivación Airbag Pasajero', 'system' => 'Airbag', 'severity' => 'Media', 'voltage' => '12V', 'signal' => 'Indicador OFF', 'causes' => ['Indicador de pasajero dañado'], 'solution' => 'Indicador de airbag pasajero desactivado.'],
            'B1200' => ['description' => 'Fallo Circuito Sensor Combustible', 'system' => 'Instrumentos', 'severity' => 'Baja', 'voltage' => '0-5V', 'signal' => 'Flotador de gasolina', 'causes' => ['Flotador dañado', 'Brazo del flotador oxidado'], 'solution' => 'Verificar resistencia del flotador: varía 10-250Ω según nivel. Si fija en un valor, reemplazar bomba de combustible completa.'],
            'B1317' => ['description' => 'Voltaje Batería Alto', 'system' => 'Eléctrico', 'severity' => 'Media', 'voltage' => '>15.5V', 'signal' => 'Sobrecarga', 'causes' => ['Alternador defectuoso'], 'solution' => 'Verificar regulador de alternador.'],
            'B1318' => ['description' => 'Voltaje Batería Bajo', 'system' => 'Eléctrico', 'severity' => 'Media', 'voltage' => '<10V', 'signal' => 'Descarga', 'causes' => ['Batería débil'], 'solution' => 'Reemplazar batería.'],
            'B1400' => ['description' => 'Fallo Circuito Odómetro', 'system' => 'Instrumentos', 'severity' => 'Baja', 'voltage' => '5V', 'signal' => 'Memoria odómetro', 'causes' => ['Fallo de memoria'], 'solution' => 'Problema en cluster de instrumentos.'],
            'B1401' => ['description' => 'Circuito Odómetro Abierto', 'system' => 'Instrumentos', 'severity' => 'Baja', 'voltage' => 'N/A', 'signal' => 'Circuito abierto', 'causes' => ['Conexión suelta'], 'solution' => 'Revisar conector del cluster.'],
            'B1402' => ['description' => 'Corto Circuito Odómetro', 'system' => 'Instrumentos', 'severity' => 'Baja', 'voltage' => 'N/A', 'signal' => 'Corto', 'causes' => ['Corto circuito'], 'solution' => 'Reparar cableado.'],
            'B1405' => ['description' => 'Fallo Circuito Indicador Cambio Aceite', 'system' => 'Instrumentos', 'severity' => 'Baja', 'voltage' => '12V', 'signal' => 'Indicador de aceite', 'causes' => ['Led o display dañado'], 'solution' => 'Indicador de cambio de aceite.'],
            
            // Red CAN
            'U0100' => ['description' => 'Pérdida Comunicación con ECM/PCM', 'system' => 'CAN Bus', 'severity' => 'Crítica', 'voltage' => '2.5V promedio', 'signal' => 'Diferencial CAN', 'causes' => ['ECM sin alimentación', 'Línea CAN rota', 'Terminación incorrecta'], 'solution' => 'Verificar alimentación del ECM. Medir resistencia entre CAN-H y CAN-L: debe ser 60Ω (120Ω en cada extremo). Voltaje: CAN-H 2.5-3.5V, CAN-L 2.5-1.5V.', 'related_codes' => ['U0101', 'U0140', 'U0151', 'U1073', 'U0401']],
            'U0101' => ['description' => 'Pérdida Comunicación con TCM', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['TCM sin comunicación', 'Alimentación TCM'], 'solution' => 'Igual U0100 pero para TCM. Verificar fusibles del TCM.'],
            'U0121' => ['description' => 'Pérdida Comunicación con Módulo ABS', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Módulo ABS sin comunicación'], 'solution' => 'Verificar alimentación del módulo ABS.'],
            'U0126' => ['description' => 'Pérdida Comunicación con Sensor Ángulo Dirección', 'system' => 'CAN Bus', 'severity' => 'Media', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Sensor de ángulo desconectado'], 'solution' => 'Sensor SAS necesario para ESP. Verificar conector en columna de dirección.'],
            'U0131' => ['description' => 'Pérdida Comunicación con Módulo Dirección Asistida', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Módulo de dirección asistida'], 'solution' => 'Verificar módulo de dirección eléctrica.'],
            'U0140' => ['description' => 'Pérdida Comunicación con Módulo Carrocería', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['BCM sin comunicación'], 'solution' => 'BCM controla muchas funciones. Verificar alimentación y masa del BCM.'],
            'U0151' => ['description' => 'Pérdida Comunicación con Módulo Restricciones', 'system' => 'CAN Bus', 'severity' => 'Crítica', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Módulo de airbags sin comunicación'], 'solution' => 'Módulo de airbags debe comunicarse. Verificar conector amarillo.'],
            'U0155' => ['description' => 'Pérdida Comunicación con Cluster Instrumentos', 'system' => 'CAN Bus', 'severity' => 'Media', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Cluster sin comunicación'], 'solution' => 'Verificar alimentación del cluster.'],
            'U0160' => ['description' => 'Pérdida Comunicación con Módulo Alertas Audibles', 'system' => 'CAN Bus', 'severity' => 'Baja', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Módulo de alarmas'], 'solution' => 'No crítico para operación.'],
            'U0164' => ['description' => 'Pérdida Comunicación con Módulo HVAC', 'system' => 'CAN Bus', 'severity' => 'Baja', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Módulo de aire acondicionado'], 'solution' => 'Verificar módulo de HVAC.'],
            'U0232' => ['description' => 'Pérdida Comunicación con Módulo Punto Ciego', 'system' => 'CAN Bus', 'severity' => 'Media', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Radar de punto ciego'], 'solution' => 'Verificar sensores en parachoques trasero.'],
            'U0401' => ['description' => 'Datos Inválidos Recibidos de ECM/PCM', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN corrupto', 'causes' => ['ECM enviando datos incorrectos', 'Interferencia'], 'solution' => 'Comunicación presente pero datos erróneos. Revisar integridad de mensajes CAN con escáner avanzado.'],
            'U0415' => ['description' => 'Datos Inválidos Recibidos de Módulo ABS', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Datos ABS incorrectos'], 'solution' => 'Igual U0401 pero para módulo ABS.'],
            'U0443' => ['description' => 'Datos Inválidos Recibidos de Módulo Carrocería', 'system' => 'CAN Bus', 'severity' => 'Media', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Datos BCM incorrectos'], 'solution' => 'Igual U0401 pero para BCM.'],
            'U1000' => ['description' => 'Fallo Comunicación Class 2', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '7V', 'signal' => 'Class 2 GM', 'causes' => ['Fallo comunicación GM Class 2'], 'solution' => 'Protocolo específico GM. Verificar resistencia de bus 108Ω.'],
            'U1064' => ['description' => 'Pérdida Comunicación con Módulo Carrocería', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Sin comunicación BCM'], 'solution' => 'Igual U0140.'],
            'U1073' => ['description' => 'Bus Comunicación ECM Desconectado', 'system' => 'CAN Bus', 'severity' => 'Crítica', 'voltage' => '0V', 'signal' => 'Desconectado', 'causes' => ['ECM no responde', 'Bus CAN dañado'], 'solution' => 'ECM no está en el bus. Verificar alimentación principal del ECM.'],
            'U1096' => ['description' => 'Pérdida Comunicación con Cluster Instrumentos', 'system' => 'CAN Bus', 'severity' => 'Media', 'voltage' => '2.5V', 'signal' => 'CAN', 'causes' => ['Cluster desconectado'], 'solution' => 'Igual U0155.'],
            'U1300' => ['description' => 'Corto Class 2 a Masa', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '0V', 'signal' => 'Corto', 'causes' => ['Bus Class 2 a masa'], 'solution' => 'Bus GM Class 2 en corto a masa. Desconectar módulos uno por uno para aislar.'],
            'U1301' => ['description' => 'Corto Class 2 a Batería', 'system' => 'CAN Bus', 'severity' => 'Alta', 'voltage' => '12V', 'signal' => 'Corto', 'causes' => ['Bus Class 2 a 12V'], 'solution' => 'Bus GM Class 2 en corto a batería.'],
        ];
        
        // Add severity and default values to all
        foreach ($dtcs as $code => &$info) {
            if (!isset($info['severity'])) {
                $info['severity'] = 'Media';
            }
            if (!isset($info['causes'])) {
                $info['causes'] = ['Revisar sensor o componente relacionado', 'Verificar cableado', 'Revisar conexiones eléctricas'];
            }
            if (!isset($info['solution'])) {
                $info['solution'] = 'Diagnosticar con scanner profesional y verificar componente específico';
            }
        }
        
        return $dtcs;
    }
}
