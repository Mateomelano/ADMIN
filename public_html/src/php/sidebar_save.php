<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','0'); ini_set('log_errors','1'); error_reporting(E_ALL);

$DEBUG = isset($_GET['debug']) ? true : false;

/* db.php */
$paths=[__DIR__.'/db.php', __DIR__.'/../db.php', dirname(__DIR__).'/db.php'];
$found=false; foreach($paths as $p){ if(file_exists($p)){ require_once $p; $found=true; break; } }
if(!$found){ http_response_code(500); echo json_encode(['ok'=>false,'message'=>'db.php no encontrado']); exit; }

/* Conexión */
function is_pdo($db){ return isset($db) && $db instanceof PDO; }
function is_mysqli_conn($db){ return isset($db) && $db instanceof mysqli; }
$db=null; if(isset($pdo)&&$pdo instanceof PDO)$db=$pdo; if(!$db&&isset($conn)&&$conn instanceof mysqli)$db=$conn; if(!$db&&isset($conexion)&&$conexion instanceof mysqli)$db=$conexion;
if(!$db){ http_response_code(500); echo json_encode(['ok'=>false,'message'=>'sin conexión DB']); exit; }

/* Validación */
if($_SERVER['REQUEST_METHOD']!=='POST'){ http_response_code(405); echo json_encode(['ok'=>false,'message'=>'method not allowed']); exit; }
$raw=file_get_contents('php://input'); $payload=json_decode($raw,true);
if(json_last_error()!==JSON_ERROR_NONE){ http_response_code(400); echo json_encode(['ok'=>false,'message'=>'invalid json']); exit; }
if(!$payload || !isset($payload['marcas']) || !is_array($payload['marcas'])){ http_response_code(400); echo json_encode(['ok'=>false,'message'=>'bad payload']); exit; }
$toDelete = isset($payload['eliminar_marcas']) && is_array($payload['eliminar_marcas']) ? $payload['eliminar_marcas'] : [];

/* Descubrir si categoria admite NULL (para headers) */
$nullable = true;
try{
  if(is_pdo($db)){
    $st = $db->prepare("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
                         WHERE TABLE_SCHEMA = DATABASE()
                           AND TABLE_NAME = 'sidebar_config'
                           AND COLUMN_NAME = 'categoria'");
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if($row && strtoupper((string)$row['IS_NULLABLE']) !== 'YES') $nullable=false;
  } else {
    $sql = "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'sidebar_config'
                AND COLUMN_NAME = 'categoria'";
    $res = $db->query($sql);
    if($res){ $row = $res->fetch_assoc(); if($row && strtoupper((string)$row['IS_NULLABLE']) !== 'YES') $nullable=false; }
  }
}catch(Throwable $e){ /* si falla, asumimos nullable */ }

$useNullHeader = $nullable;

/* SQL según sea NULL o '' */
if($useNullHeader){
  $sqlDelHeader = "DELETE FROM sidebar_config WHERE marca=? AND categoria IS NULL";
  $sqlInsHeader = "INSERT INTO sidebar_config (marca, categoria, visible, orden) VALUES (?, NULL, ?, ?)";
  $sqlDelCats   = "DELETE FROM sidebar_config WHERE marca=? AND categoria IS NOT NULL";
}else{
  $sqlDelHeader = "DELETE FROM sidebar_config WHERE marca=? AND categoria=''";
  $sqlInsHeader = "INSERT INTO sidebar_config (marca, categoria, visible, orden) VALUES (?, '', ?, ?)";
  $sqlDelCats   = "DELETE FROM sidebar_config WHERE marca=? AND categoria<>''";
}
$sqlInsCat = "INSERT INTO sidebar_config (marca, categoria, visible, orden) VALUES (?, ?, ?, ?)";

try{
  // Tx
  if(is_pdo($db)){
    $db->beginTransaction();
    $stDelHeader = $db->prepare($sqlDelHeader);
    $stInsHeader = $db->prepare($sqlInsHeader);
    $stDelCats   = $db->prepare($sqlDelCats);
    $stInsCat    = $db->prepare($sqlInsCat);
  }else{
    $db->begin_transaction();
    $stDelHeader = $db->prepare($sqlDelHeader);
    $stInsHeader = $db->prepare($sqlInsHeader);
    $stDelCats   = $db->prepare($sqlDelCats);
    $stInsCat    = $db->prepare($sqlInsCat);
    if(!$stDelHeader||!$stInsHeader||!$stDelCats||!$stInsCat) throw new Exception('No se pudo preparar statement');
  }

  /* 1) Borrar marcas pedidas explícitamente */
  foreach($toDelete as $dm){
    $dm = trim((string)$dm);
    if($dm==='') continue;
    if(is_pdo($db)){
      $stDelHeader->execute([$dm]);
      $stDelCats->execute([$dm]);
    }else{
      $stDelHeader->bind_param('s',$dm); $stDelHeader->execute();
      $stDelCats->bind_param('s',$dm);   $stDelCats->execute();
    }
  }

  /* 2) Guardar/renombrar marcas restantes */
  foreach($payload['marcas'] as $m){
    $marca   = trim((string)($m['marca'] ?? ''));
    $orig    = trim((string)($m['original_marca'] ?? $marca));
    if($marca==='') continue;

    $visible = !empty($m['visible']) ? 1 : 0;
    $orden   = isset($m['orden']) ? (int)$m['orden'] : 0;

    // si renombró, limpiar nombre viejo
    if($orig!=='' && strcasecmp($orig,$marca)!==0){
      if(is_pdo($db)){ $stDelHeader->execute([$orig]); $stDelCats->execute([$orig]); }
      else { $stDelHeader->bind_param('s',$orig); $stDelHeader->execute(); $stDelCats->bind_param('s',$orig); $stDelCats->execute(); }
    }

    // limpiar e insertar header + limpiar cats del nombre nuevo
    if(is_pdo($db)){
      $stDelHeader->execute([$marca]);
      $stInsHeader->execute([$marca,$visible,$orden]);
      $stDelCats->execute([$marca]);
    }else{
      $stDelHeader->bind_param('s',$marca); $stDelHeader->execute();
      $stInsHeader->bind_param('sii',$marca,$visible,$orden); $stInsHeader->execute();
      $stDelCats->bind_param('s',$marca);   $stDelCats->execute();
    }

    // De-duplicar categorías dentro del payload (por si vienen repetidas)
    $seen = [];
    if(!empty($m['categorias']) && is_array($m['categorias'])){
      foreach($m['categorias'] as $c){
        $cat = trim((string)($c['categoria'] ?? ''));
        if($cat==='') continue;
        $key = mb_strtolower($cat);
        if(isset($seen[$key])) continue; // saltear duplicados en el payload
        $seen[$key] = true;

        $vis = !empty($c['visible']) ? 1 : 0;
        $ord = isset($c['orden']) ? (int)$c['orden'] : 0;

        if(is_pdo($db)){ $stInsCat->execute([$marca,$cat,$vis,$ord]); }
        else {
          $stInsCat->bind_param('ssii',$marca,$cat,$vis,$ord);
          if(!$stInsCat->execute()){
            throw new Exception('Fallo insert cat: '.$db->error);
          }
        }
      }
    }
  }

  if(is_pdo($db)) $db->commit(); else $db->commit();
  echo json_encode(['ok'=>true]);
}catch(Throwable $e){
  if(is_pdo($db) && $db->inTransaction()) $db->rollBack();
  if(is_mysqli_conn($db)) $db->rollback();
  error_log('[sidebar_save] '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false,'message'=>$DEBUG ? $e->getMessage() : 'server error']);
}

