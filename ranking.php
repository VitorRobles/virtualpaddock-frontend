
<?php
require 'config.php';
$u = need_login(); $titulo = 'Ranking';
$lista = [];
foreach (db()['carreira'] as $id => $c) if (isset(db()['usuarios'][$id])) $lista[] = ['id' => $id, 'nome' => db()['usuarios'][$id]['nome'], 'regiao' => db()['usuarios'][$id]['regiao']] + $c;
usort($lista, fn($a, $b) => [$b['rating'], $b['vit']] <=> [$a['rating'], $a['vit']]);
include 'partials_header.php';
?>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php">Carreira</a> / Ranking</span>
  <h1>Ranking global</h1><p>Os pilotos ordenados por rating. Em caso de empate, vence quem tem mais vitórias.</p>
</div>
<div class="container page-content">
  <div class="toolbar"><label class="search"><?= ico('search') ?><input data-busca type="search" placeholder="Buscar piloto" aria-label="Buscar piloto"></label></div>
  <div class="result-count" id="contagem"><?= count($lista) ?> resultados</div>
  <?php if ($lista): ?>
  <div class="rows">
    <?php foreach ($lista as $i => $p): ?>
    <div class="row-item <?= $p['id'] == $u['id'] ? 'me' : '' ?>" data-item="<?= e($p['nome'] . ' ' . $p['regiao']) ?>" data-status="">
      <span class="row-pos <?= $i < 3 ? 'p' . ($i + 1) : '' ?>"><?= $i + 1 ?></span>
      <div class="row-main"><strong><a href="perfil_piloto.php?id=<?= (int)$p['id'] ?>" aria-label="Ver perfil de <?= e($p['nome']) ?>"><?= e($p['nome']) ?></a><?= $p['id'] == $u['id'] ? ' (você)' : '' ?></strong><small><?= e($p['regiao']) ?> · <?= (int)$p['vit'] ?> vitórias · <?= (int)$p['podios'] ?> pódios</small></div>
      <div class="row-end"><?= (int)$p['rating'] ?><small>rating</small></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty"><h3>Ainda não há pilotos no ranking</h3><p>Crie uma conta de piloto e entre em um campeonato para pontuar.</p></div>
  <?php endif; ?>
</div>
<?php include 'partials_footer.php'; ?>
