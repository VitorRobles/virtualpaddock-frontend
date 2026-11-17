<?php
require 'config.php';
$u = need_login();
$org = $u['tipo'] === 'Organizador';
$titulo = $org ? 'Painel do organizador' : 'Carreira';
$d = db();
$c = carreira($u['id']);
$pos = 1 + count(array_filter($d['carreira'], fn($x) => $x['rating'] > $c['rating']));
$pct = min(100, (int)round($c['rating'] / 3000 * 100));
$meus = array_values(array_filter($d['campeonatos'], fn($x) => (int)$x['org'] === (int)$u['id']));
$meusAtivos = count(array_filter($meus, fn($x) => !in_array($x['status'], ['Rascunho', 'Finalizado'], true)));
$meusRascunhos = count(array_filter($meus, fn($x) => $x['status'] === 'Rascunho'));
$abertos = $org ? [] : array_slice(array_filter($d['campeonatos'], fn($x) => $x['status'] === 'Inscrições Abertas'), 0, 2);
include 'partials_header.php';
?>
<style>
.org-dashboard .hero-label{color:#ffb15e}.org-dashboard .rating-card{border-top:4px solid var(--orange)}.org-shortcuts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.org-shortcut{background:#fff;border:1px solid var(--line);border-radius:10px;padding:22px;display:flex;align-items:center;justify-content:space-between;gap:18px}.org-shortcut h3{margin:0 0 6px}.org-shortcut p{font-size:13px;margin:0}.org-shortcut a{font-weight:800;color:var(--blue);white-space:nowrap}.org-dash-card{box-shadow:inset 0 3px 0 var(--orange)}@media(max-width:650px){.org-shortcuts{grid-template-columns:1fr}}
</style>
<section class="hero <?= $org ? 'org-dashboard' : '' ?>">
  <?= pista_svg() ?>
  <div class="container">
    <div>
      <div class="hero-label"><?= $org ? 'Área de organização' : 'Modo carreira' ?></div>
      <h1>Olá, <?= e(explode(' ', $u['nome'])[0]) ?>.</h1>
      <p><?= $org ? 'Publique campeonatos, divulgue seus canais oficiais e acompanhe cada grid pela sua central.' : 'Seu rating, suas vitórias e seu histórico estão atualizados. Veja onde você está e escolha a próxima corrida.' ?></p>
      <div class="actions">
        <?php if ($org): ?><a class="button" href="criar_campeonato.php">Criar campeonato <?= ico('arrow') ?></a><a class="button secondary" href="campeonatos.php">Abrir minha central</a>
        <?php else: ?><a class="button" href="campeonatos.php">Ver campeonatos <?= ico('arrow') ?></a><a class="button secondary" href="historico.php">Meu histórico</a><?php endif; ?>
      </div>
    </div>
    <?php if ($org): ?>
    <div class="rating-card"><small>Campeonatos publicados ou ativos</small><div class="rating-num"><?= $meusAtivos ?></div><div class="rating-pos">Até 2 no plano atual · <?= $meusRascunhos ?> rascunhos privados</div><div class="bar"><i style="width:<?= min(100, $meusAtivos * 50) ?>%"></i></div></div>
    <?php else: ?>
    <div class="rating-card"><small>Seu rating</small><div class="rating-num"><?= (int)$c['rating'] ?></div><div class="rating-pos">#<?= $pos ?> no ranking global</div><div class="bar" role="img" aria-label="Rating em relação ao teto de 3000"><i style="width:<?= $pct ?>%"></i></div><div class="rating-meta"><div><strong><?= (int)$c['vit'] ?></strong><span>Vitórias</span></div><div><strong><?= (int)$c['podios'] ?></strong><span>Pódios</span></div></div></div>
    <?php endif; ?>
  </div>
</section>
<?php if ($org): ?>
<section class="container section">
  <div class="section-heading"><div><h2>Central da organização</h2><p>Atalhos para publicar e consultar seus campeonatos.</p></div></div>
  <div class="org-shortcuts">
    <article class="org-shortcut"><div><h3>Meus campeonatos</h3><p>Consulte status, vagas e a página pública de cada competição.</p></div><a href="campeonatos.php">Abrir central <?= ico('arrow') ?></a></article>
    <article class="org-shortcut"><div><h3>Novo campeonato</h3><p>Cadastre datas, regras, capacidade e canais sociais oficiais.</p></div><a href="criar_campeonato.php">Começar <?= ico('arrow') ?></a></article>
  </div>
</section>
<?php if ($meus): ?>
<section class="container section" style="padding-top:0">
  <div class="section-heading"><div><h2>Publicações recentes</h2><p>Veja o estado dos campeonatos que pertencem à sua organização.</p></div><a class="text-link" href="campeonatos.php">Ver todos <?= ico('arrow') ?></a></div>
  <div class="camp-grid">
    <?php foreach (array_slice(array_reverse($meus), 0, 3) as $x): $n = inscritos($x['id']); $statusCls = $x['status'] === 'Inscrições Abertas' ? 'ok' : ($x['status'] === 'Rascunho' ? 'warn' : ''); ?>
    <article class="camp-card org-dash-card"><div class="camp-top"><div class="camp-avatar"><?= ico('trophy') ?></div><div><h3><?= e($x['nome']) ?></h3><small><?= e($x['categoria']) ?> · <?= e($x['plataforma']) ?></small></div></div><div><span class="tag <?= $statusCls ?>"><?= e($x['status']) ?></span></div><div class="bar"><i style="width:<?= min(100, round($n / max(1, $x['vagas']) * 100)) ?>%"></i></div><small><?= $n ?> de <?= (int)$x['vagas'] ?> vagas ocupadas</small><div class="card-bottom"><a class="plain-button" href="campeonato.php?id=<?= (int)$x['id'] ?>">Ver página <?= ico('arrow') ?></a></div></article>
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>
<section class="container section"><div class="empty"><h3>Seu primeiro campeonato começa aqui</h3><p>Crie uma competição, adicione seus canais e escolha entre publicar agora ou guardar um rascunho.</p><a class="button" href="criar_campeonato.php">Criar campeonato</a></div></section>
<?php endif; ?>
<?php else: ?>
<section class="container section">
  <div class="section-heading"><div><h2>Seu painel</h2><p>Tudo o que você precisa para a próxima etapa.</p></div></div>
  <div class="guide-grid">
    <?= card_painel('ranking.php', 'chart', '', 'Ranking', 'Posição global', 'Compare seu rating com o dos outros pilotos.', 'Abrir ranking') ?>
    <?= card_painel('historico.php', 'clock', 'orange', 'Histórico', 'Corridas e pontos', 'Revise cada corrida, posição de chegada e pontuação.', 'Ver histórico') ?>
    <?= card_painel('campeonatos.php', 'flag', 'slate', 'Campeonatos', 'Inscrições e calendário', 'Encontre campeonatos abertos e entre no grid.', 'Ver campeonatos') ?>
    <?= card_painel('incidentes.php', 'alert', 'red', 'Incidentes', 'Penalidades e avisos', 'Acompanhe protestos, decisões e advertências.', 'Ver incidentes') ?>
  </div>
</section>
<?php if ($abertos): ?>
<section class="container section" style="padding-top:0"><div class="section-heading"><div><h2>Inscrições abertas</h2><p>Campeonatos que aceitam pilotos agora.</p></div><a class="text-link" href="campeonatos.php">Ver todos <?= ico('arrow') ?></a></div><div class="camp-grid">
<?php foreach ($abertos as $x): $n = inscritos($x['id']); ?><article class="camp-card"><div class="camp-top"><div class="camp-avatar"><?= ico('flag') ?></div><div><h3><?= e($x['nome']) ?></h3><small><?= e($x['categoria']) ?> · <?= e($x['plataforma']) ?></small></div></div><div class="bar"><i style="width:<?= min(100, round($n / max(1, $x['vagas']) * 100)) ?>%"></i></div><small><?= $n ?> de <?= (int)$x['vagas'] ?> vagas ocupadas</small><div class="card-bottom"><a class="plain-button" href="campeonato.php?id=<?= (int)$x['id'] ?>">Ver campeonato <?= ico('arrow') ?></a></div></article><?php endforeach; ?>
</div></section>
<?php endif; ?>
<?php endif; ?>
<?php include 'partials_footer.php'; ?>