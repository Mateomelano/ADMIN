<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','0'); ini_set('log_errors','1'); error_reporting(E_ALL);

/* Cargar db.php */
$paths=[__DIR__.'/db.php', __DIR__.'/../db.php', dirname(__DIR__).'/db.php'];
$found=false; foreach($paths as $p){ if(file_exists($p)){ require_once $p; $found=true; break; } }
if(!$found){ http_response_code(500); echo json_encode(['ok'=>false,'message'=>'db.php no encontrado']); exit; }

/* Detectar conexión */
function is_pdo($db){ return isset($db) && $db instanceof PDO; }
function is_mysqli_conn($db){ return isset($db) && $db instanceof mysqli; }
$db=null; if(isset($pdo)&&$pdo instanceof PDO)$db=$pdo; if(!$db&&isset($conn)&&$conn instanceof mysqli)$db=$conn; if(!$db&&isset($conexion)&&$conexion instanceof mysqli)$db=$conexion;
if(!$db){ http_response_code(500); echo json_encode(['ok'=>false,'message'=>'sin conexión DB']); exit; }

/* Helpers */
function all_assoc($db,$sql,$types='',$params=[]){
  if($db instanceof PDO){ $st=$db->prepare($sql); $st->execute($params); return $st->fetchAll(PDO::FETCH_ASSOC)?:[]; }
  if($db instanceof mysqli){
    if($params){ $st=$db->prepare($sql); if(!$st)return[]; if($types)$st->bind_param($types,...$params); $st->execute(); $res=$st->get_result(); $out=$res?$res->fetch_all(MYSQLI_ASSOC):[]; $st->close(); return $out; }
    $res=$db->query($sql); return $res?$res->fetch_all(MYSQLI_ASSOC):[];
  } return [];
}
function all_col($db,$sql,$types='',$params=[]){
  $rows=all_assoc($db,$sql,$types,$params); $out=[]; foreach($rows as $r){ $out[]=array_values($r)[0]; } return $out;
}

try{
  /* Marcas desde config (headers) */
  $cfgMarcas = all_assoc($db, "SELECT marca, visible, orden FROM sidebar_config WHERE categoria IS NULL");

  /* Marcas desde productos */
  $prodMarcas = all_col($db, "SELECT DISTINCT marca FROM productos WHERE marca IS NOT NULL AND marca<>'' ORDER BY marca");

  /* Unir marcas (mapa) */
  $byMarca = [];
  foreach($prodMarcas as $m){
    if(!isset($byMarca[$m])) $byMarca[$m] = ['marca'=>$m,'visible'=>0,'orden'=>0];
  }
  foreach($cfgMarcas as $cm){
    $m = $cm['marca'];
    $byMarca[$m] = ['marca'=>$m,'visible'=>(int)$cm['visible'],'orden'=>(int)$cm['orden']];
  }

  /* Construir salida */
  $data = [];
  foreach($byMarca as $m=>$info){
    // cats desde config
    $cfgCats = all_assoc(
      $db,
      "SELECT categoria, visible, orden FROM sidebar_config WHERE marca=? AND categoria IS NOT NULL",
      is_pdo($db)?'':'s',
      [$m]
    );
    $mapCats = [];
    foreach($cfgCats as $c){ $mapCats[$c['categoria']] = ['categoria'=>$c['categoria'],'visible'=>(int)$c['visible'],'orden'=>(int)$c['orden']]; }

    // cats desde productos (complementa)
    $prodCats = all_col(
      $db,
      "SELECT DISTINCT categoria FROM productos WHERE marca=? AND categoria IS NOT NULL AND categoria<>''",
      is_pdo($db)?'':'s',
      [$m]
    );
    foreach($prodCats as $pc){
      if(!isset($mapCats[$pc])){
        $mapCats[$pc] = ['categoria'=>$pc,'visible'=>0,'orden'=>0];
      }
    }

    // ordenar por orden + nombre
    $cats = array_values($mapCats);
    usort($cats, fn($a,$b)=>[$a['orden'],$a['categoria']] <=> [$b['orden'],$b['categoria']]);

    $data[] = [
      'marca'=>$info['marca'],
      'visible'=>$info['visible'],
      'orden'=>$info['orden'],
      'categorias'=>$cats
    ];
  }

  // ordenar marcas por orden + nombre
  usort($data, fn($a,$b)=>[$a['orden'],$a['marca']] <=> [$b['orden'],$b['marca']]);

  echo json_encode(['ok'=>true,'marcas'=>$data], JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){
  error_log('[sidebar_get] '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['ok'=>false,'message'=>'server error']);
}
