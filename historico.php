<?php
require 'config.php';
$u = need_login(); $titulo = 'Histórico';
$d = db(); $lista = [];
foreach ($d['resultados'] as $r) {
    if ($r['uid'] != $u['id']) continue;
    foreach ($d['corridas'] as $co) if ($co['id'] == $r['corrida']) $lista[] = $r + ['co' => $co];
}
usort($lista, fn($a, $b) => strcmp($b['co']['data'], $a['co']['data']));
$vit = count(array_filter($lista, fn($r) => $r['pos'] == 1));
$pts = array_sum(array_column($lista, 'pontos'));
include 'partials_header.php';
?>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php">Carreira</a> / Histórico</span>
  <h1>Histórico de corridas</h1><p>Suas últimas performances, da mais recente para a mais antiga.</p>
</div>
<div class="container page-content">
  <div class="stats-row">
    <div class="stat-box"><strong><?= count($lista) ?></strong><span>Corridas disputadas</span></div>
    <div class="stat-box"><strong><?= $vit ?></strong><span>Vitórias</span></div>
    <div class="stat-box"><strong><?= (int)$pts ?></strong><span>Pontos somados</span></div>
  </div>
  <?php if ($lista): ?>
  <div class="rows">
    <?php foreach ($lista as $r): $co = $r['co']; ?>
    <div class="row-item">
      <span class="row-pos <?= $r['pos'] <= 3 ? 'p' . (int)$r['pos'] : '' ?>"><?= (int)$r['pos'] ?>º</span>
      <div class="row-main"><strong><?= e($d['pistas'][$co['pista']]) ?></strong><small><?= e($d['campeonatos'][$co['camp']]['nome']) ?> · Rodada <?= (int)$co['rodada'] ?> · <?= date('d/m/Y', strtotime($co['data'])) ?> · <?= e($r['status']) ?></small></div>
      <div class="row-end"><?= (int)$r['pontos'] ?><small>pontos</small></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty"><h3>Você ainda não correu</h3><p>Inscreva-se em um campeonato para começar a montar seu histórico.</p><a class="button" href="campeonatos.php">Ver campeonatos</a></div>
  <?php endif; ?>
</div>
<?php include 'partials_footer.php'; ?>
