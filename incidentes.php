<?php
require 'config.php';
$u = need_login(); $titulo = 'Incidentes';
$d = db();
$lista = array_reverse(array_filter($d['incidentes'], fn($i) => $i['acusado'] == $u['id'] || $i['autor'] == $u['id']));
$pen = $adv = 0;
foreach ($lista as $i) {
    $x = mb_strtolower((string)$i['decisao']);
    if ($i['status'] !== 'Julgado' || $x === '' || str_starts_with($x, 'sem')) continue;
    str_starts_with($x, 'advert') ? $adv++ : $pen++;
}
include 'partials_header.php';
?>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php">Carreira</a> / Incidentes</span>
  <h1>Incidentes</h1><p>Protestos em que você está envolvido e as decisões dos comissários.</p>
</div>
<div class="container page-content">
  <div class="stats-row">
    <div class="stat-box"><strong><?= count($lista) ?></strong><span>Total</span></div>
    <div class="stat-box"><strong><?= $pen ?></strong><span>Penalidades</span></div>
    <div class="stat-box"><strong><?= $adv ?></strong><span>Advertências</span></div>
  </div>
  <?php if ($lista): ?>
  <div class="rows">
    <?php foreach ($lista as $i):
      $pista = ''; foreach ($d['corridas'] as $co) if ($co['id'] == $i['corrida']) $pista = $d['pistas'][$co['pista']];
      $julg = $i['status'] === 'Julgado'; ?>
    <div class="row-item">
      <span class="row-pos"><?= ico('alert') ?></span>
      <div class="row-main"><strong><?= e($i['tipo']) ?> · volta <?= (int)$i['volta'] ?></strong>
        <small><?= e($pista) ?> · <?= $i['decisao'] ? 'Decisão: ' . e($i['decisao']) : 'Aguardando decisão' ?></small></div>
      <span class="tag <?= $julg ? 'ok' : 'warn' ?>" style="margin:0"><?= e($i['status']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty"><h3>Corrida limpa</h3><p>Você não tem incidentes registrados. Continue assim.</p></div>
  <?php endif; ?>
</div>
<?php include 'partials_footer.php'; ?>
