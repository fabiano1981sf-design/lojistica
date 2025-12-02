<?php
// File: public/qrcode_gerar.php
require '../config/database.php';
require '../vendor/autoload.php';
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$despacho_id = $_GET['id'] ?? 0;
global $pdo;
$stmt = $pdo->prepare("SELECT codigo_rastreio FROM despachos WHERE id = ?");
$stmt->execute([$despacho_id]);
$despacho = $stmt->fetch();

if (!$despacho) die('Despacho não encontrado');

$url = "http://seusite.com/rastreio_publico.php?codigo=" . $despacho['codigo_rastreio'];

$renderer = new ImageRenderer(
    new RendererStyle(300),
    new ImagickImageBackEnd()
);
$writer = new Writer($renderer);
header('Content-Type: image/png');
echo $writer->writeString($url);