<?php
require 'config.php';
$u = need_login();
$titulo = $u['tipo'] === 'Organizador' ? 'Central de campeonatos' : 'Campeonatos';
$eOrganizador = $u['tipo'] === 'Organizador';
$lista = array_reverse(array_filter(db()['campeonatos'], fn($c) => $c['status'] !== 'Rascunho' || (int)$c['org'] === (int)$u['id']));
$meus = array_values(array_filter(db()['campeonatos'], fn($c) => (int)$c['org'] === (int)$u['id']));
$rascunhos = count(array_filter($meus, fn($c) => $c['status'] === 'Rascunho'));
$publicados = count(array_filter($meus, fn($c) => !in_array($c['status'], ['Rascunho', 'Finalizado'], true)));
include 'partials_header.php';
?>
<style>
.org-center{background:var(--ink);color:#fff;border-radius:12px;padding:25px 28px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap}.org-center h2{font-size:32px;margin:0 0 6px}.org-center p{color:#c3cfdc;margin:0}.org-metrics{display:flex;gap:22px}.org-metric strong{display:block;font:700 32px/1 var(--display);color:#ffb15e}.org-metric span{font-size:11px;color:#c3cfdc}.camp-card.owned{box-shadow:inset 0 3px 0 var(--orange)}.owner-label{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;color:#a44a00;background:#fff2e3;border-radius:20px;padding:5px 9px;margin-top:8px}
</style>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php"><?= $eOrganizador ? 'Painel do organizador' : 'Carreira' ?></a> / Campeonatos</span>
  <?php if ($eOrganizador): ?><h1>Central de campeonatos</h1><p>Cuide das suas publicações e acompanhe o que os pilotos veem.</p>
  <?php else: ?><h1>Campeonatos</h1><p>Encontre uma competição, confira as regras e entre no grid.</p><?php endif; ?>
</div>
<div class="container page-content">
  <?php if ($eOrganizador): ?>
  <section class="org-center" aria-label="Resumo do organizador"><div><span class="creator-kicker">Área do organizador</span><h2>Seu grid, sob controle.</h2><p>Rascunhos ficam privados; publicar coloca o campeonato na lista dos pilotos.</p></div><div class="org-metrics"><div class="org-metric"><strong><?= count($meus) ?></strong><span>criados por você</span></div><div class="org-metric"><strong><?= $publicados ?></strong><span>publicados ou ativos</span></div><div class="org-metric"><strong><?= $rascunhos ?></strong><span>rascunhos privados</span></div></div><a class="button" href="criar_campeonato.php">Criar campeonato <?= ico('arrow') ?></a></section>
  <?php endif; ?>
  <div class="toolbar">
    <label class="search"><?= ico('search') ?><input data-busca type="search" placeholder="Buscar por nome, categoria ou simulador" aria-label="Buscar campeonato"></label>
    <?php if (!$eOrganizador): ?><span class="creator-hint">Mostrando apenas campeonatos publicados.</span><?php endif; ?>
  </div>
  <div class="chips" role="group" aria-label="Filtrar por status">
    <button class="chip" data-status="" aria-pressed="true">Todos</button>
    <button class="chip" data-status="Inscrições Abertas" aria-pressed="false">Inscrições abertas</button>
    <button class="chip" data-status="Em Andamento" aria-pressed="false">Em andamento</button>
    <?php if ($eOrganizador && $rascunhos): ?><button class="chip" data-status="Rascunho" aria-pressed="false">Meus rascunhos</button><?php endif; ?>
  </div>
  <div class="result-count" id="contagem"><?= count($lista) ?> campeonatos</div>
  <?php if ($lista): ?>
  <div class="camp-grid">
    <?php foreach ($lista as $c): $n = inscritos($c['id']); $meu = $eOrganizador && (int)$c['org'] === (int)$u['id']; $cls = $c['status'] === 'Inscrições Abertas' ? 'ok' : ($c['status'] === 'Rascunho' ? 'warn' : ''); ?>
    <article class="camp-card <?= $meu ? 'owned' : '' ?>" data-item="<?= e($c['nome'] . ' ' . $c['categoria'] . ' ' . $c['plataforma']) ?>" data-status="<?= e($c['status']) ?>">
      <div class="camp-top"><div class="camp-avatar"><?= ico($meu ? 'trophy' : 'flag') ?></div><div><h3><?= e($c['nome']) ?></h3><small>Organização: <?= e(db()['usuarios'][$c['org']]['nome'] ?? '—') ?></small><?php if ($meu): ?><span class="owner-label">Seu campeonato</span><?php endif; ?></div></div>
      <div><span class="tag <?= $cls ?>"><?= e($c['status']) ?></span><span class="tag"><?= e($c['categoria']) ?></span><span class="tag"><?= e($c['plataforma']) ?></span></div>
      <div class="bar"><i style="width:<?= min(100, round($n / max(1, $c['vagas']) * 100)) ?>%"></i></div><small><?= $n ?> de <?= (int)$c['vagas'] ?> vagas ocupadas</small>
      <div class="card-bottom"><a class="plain-button" href="campeonato.php?id=<?= (int)$c['id'] ?>"><?= $meu ? 'Abrir painel' : 'Ver campeonato' ?> <?= ico('arrow') ?></a></div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty"><h3><?= $eOrganizador ? 'Você ainda não criou campeonatos' : 'Nenhum campeonato publicado' ?></h3><p><?= $eOrganizador ? 'Crie uma competição ou salve um rascunho para começar a montar o grid.' : 'Quando uma organização publicar um campeonato, ele aparecerá aqui.' ?></p><?php if ($eOrganizador): ?><a class="button dark" href="criar_campeonato.php">Criar primeiro campeonato</a><?php endif; ?></div>
  <?php endif; ?>
</div>
<?php include 'partials_footer.php'; ?>