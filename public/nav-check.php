<?php
// Análisis rápido de navegación y botones
error_reporting(E_ALL);
require_once __DIR__ . '/../config/database.php';

echo "<style>body{font-family:sans-serif;background:#0a0c10;color:#e8eaf2;padding:20px;}
.ok{color:#4ade80}.error{color:#f87171}.warning{color:#fbbf24}
h1{color:#8ab4f8}h2{color:#fbbf24;border-bottom:1px solid #333}
table{width:100%;border-collapse:collapse;margin:10px 0;font-size:13px}
th,td{padding:10px;border-bottom:1px solid #333;text-align:left}
th{color:#9aa3b2;background:rgba(255,255,255,0.03)}
.route{color:#8ab4f8;font-family:monospace}</style>";

echo "<h1>🧭 Análisis de Navegación y Botones</h1>";

// 1. RUTAS EN web.php
echo "<h2>1. Rutas en web.php</h2>";
$web = file_get_contents(__DIR__.'/../routes/web.php');
preg_match_all('/\$uri\s*===\s*[\'\"]([^\'\"]+)[\'\"]/', $web, $m1);
preg_match_all('/preg_match\([\'\"]#\^([^#]+)#[\'\"]/', $web, $m2);
$routes = array_merge($m1[1], $m2[1]);
echo "<p>Total rutas: <strong>".count($routes)."</strong></p>";
echo "<table><tr><th>Ruta</th><th>Tipo</th></tr>";
foreach(array_slice($routes,0,30) as $r){
    $type = strpos($r,'(')!==false?'regex':'simple';
    echo "<tr><td class='route'>$r</td><td>$type</td></tr>";
}
echo "</table>";

// 2. CONTROLADORES
echo "<h2>2. Controladores</h2>";
$controllers = glob(__DIR__.'/../app/controllers/*.php');
echo "<table><tr><th>Archivo</th><th>Métodos</th></tr>";
foreach($controllers as $c){
    $content = file_get_contents($c);
    preg_match_all('/public function (\w+)\(/', $content, $m);
    $methods = implode(', ', array_slice($m[1], 0, 5));
    if(count($m[1])>5) $methods.='...';
    echo "<tr><td>".basename($c)."</td><td class='route'>$methods</td></tr>";
}
echo "</table>";

// 3. ENLACES EN VISTAS
echo "<h2>3. Enlaces en Vistas (por archivo)</h2>";
$views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../app/views'));
echo "<table><tr><th>Vista</th><th>Enlaces</th><th>Botones</th><th>Estado</th></tr>";
foreach($views as $v){
    if($v->isFile() && $v->getExtension()=='php'){
        $content = file_get_contents($v->getPathname());
        preg_match_all('/href=[\'\"]([^\'\"]+)[\'\"]/', $content, $h);
        preg_match_all('/<button/', $content, $b);
        $links = count($h[1]);
        $buttons = count($b[1]);
        echo "<tr><td>".$v->getFilename()."</td><td>$links</td><td>$buttons</td><td class='ok'>OK</td></tr>";
    }
}
echo "</table>";

// 4. VERIFICAR BOTONES ESPECÍFICOS
echo "<h2>4. Botones Comunes a Verificar</h2>";
$commonButtons = ['Crear','Editar','Eliminar','Guardar','Cancelar','Volver','Nuevo','Ver'];
echo "<table><tr><th>Botón</th><th>Encontrado en</th></tr>";
foreach($commonButtons as $btn){
    $found = [];
    $views = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../app/views'));
    foreach($views as $v){
        if($v->isFile() && $v->getExtension()=='php'){
            if(stripos(file_get_contents($v->getPathname()), $btn)!==false){
                $found[] = $v->getFilename();
            }
        }
    }
    echo "<tr><td><strong>$btn</strong></td><td>".implode(', ', array_slice($found,0,5))."</td></tr>";
}
echo "</table>";

echo "<p><a href='/torque/dashboard' style='color:#8ab4f8'>← Volver</a></p>";
