<?php
// Salve este arquivo na raiz do projeto VirtualPaddock, junto de config.php.
require_once __DIR__ . '/config.php';
$u = need_login();
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$u['id'];
$perfilPublico = $id !== (int)$u['id'];
$alvo = db()['usuarios'][$id] ?? null;
if (!$alvo || ($alvo['tipo'] ?? '') !== 'Piloto') {
    http_response_code(404);
    $titulo = 'Perfil não encontrado';
    include __DIR__ . '/partials_header.php';
    echo '<div class="container page-content"><div class="empty"><h3>Perfil não encontrado</h3><p>Este perfil de piloto não está disponível.</p><a class="button light" href="ranking.php">Voltar ao ranking</a></div></div>';
    include __DIR__ . '/partials_footer.php';
    exit;
}

if (!isset($_SESSION['db']['perfis'])) $_SESSION['db']['perfis'] = [];
if (!isset($_SESSION['perfil_csrf'])) $_SESSION['perfil_csrf'] = bin2hex(random_bytes(32));
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$perfilPublico) {
    if (!hash_equals($_SESSION['perfil_csrf'], (string)($_POST['csrf'] ?? ''))) {
        $erros[] = 'Não foi possível validar o formulário. Atualize a página e tente novamente.';
    } else {
        $limparFoto = isset($_POST['remover_foto']);
        $perfil = $_SESSION['db']['perfis'][$id] ?? [];
        $perfil['descricao'] = trim((string)($_POST['descricao'] ?? ''));
        $perfil['equipe'] = trim((string)($_POST['equipe'] ?? ''));
        $perfil['numero'] = trim((string)($_POST['numero'] ?? ''));
        $perfil['carro'] = trim((string)($_POST['carro'] ?? ''));
        $perfil['plataforma'] = trim((string)($_POST['plataforma'] ?? ''));
        $perfil['pista_favorita'] = trim((string)($_POST['pista_favorita'] ?? ''));
        $perfil['redes'] = [];
        foreach (['instagram', 'youtube', 'twitch', 'tiktok', 'discord'] as $rede) {
            $url = trim((string)($_POST['rede_' . $rede] ?? ''));
            if ($url === '') continue;
            if (strlen($url) > 500 || !filter_var($url, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)) {
                $erros[] = 'Confira o endereço informado para ' . ucfirst($rede) . '. Use um link completo iniciado por https://.';
                continue;
            }
            $perfil['redes'][$rede] = $url;
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $arquivo = $_FILES['foto'];
            if ($arquivo['error'] !== UPLOAD_ERR_OK) {
                $erros[] = 'Não foi possível receber a foto. Tente outro arquivo.';
            } elseif ($arquivo['size'] > 3 * 1024 * 1024) {
                $erros[] = 'A foto deve ter no máximo 3 MB.';
            } else {
                $info = @getimagesize($arquivo['tmp_name']);
                $mime = $info['mime'] ?? '';
                $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
                if (!$info || !isset($tipos[$mime])) {
                    $erros[] = 'Use uma imagem JPG, PNG, WEBP ou GIF.';
                } else {
                    $pasta = __DIR__ . '/uploads/perfis';
                    if (!is_dir($pasta) && !@mkdir($pasta, 0755, true) && !is_dir($pasta)) {
                        $erros[] = 'Não foi possível preparar a pasta de fotos. Crie uploads/perfis no projeto e tente novamente.';
                    } else {
                        $nomeArquivo = 'piloto-' . $id . '-' . bin2hex(random_bytes(8)) . '.' . $tipos[$mime];
                        if (!move_uploaded_file($arquivo['tmp_name'], $pasta . '/' . $nomeArquivo)) {
                            $erros[] = 'Não foi possível salvar a foto. Verifique a permissão de escrita da pasta uploads/perfis.';
                        } else {
                            $perfil['foto'] = 'uploads/perfis/' . $nomeArquivo;
                        }
                    }
                }
            }
        }
        if ($limparFoto) unset($perfil['foto']);
        if (!$erros) {
            $_SESSION['db']['perfis'][$id] = $perfil;
            flash('Perfil atualizado.');
            header('Location: perfil_piloto.php');
            exit;
        }
        $_SESSION['db']['perfis'][$id] = $perfil;
    }
}

$perfil = $_SESSION['db']['perfis'][$id] ?? [];
if (empty($perfil['foto'])) {
  $fotos = glob(__DIR__ . '/uploads/perfis/piloto-' . (int)$id . '-*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
  sort($fotos);
  if ($fotos) $perfil['foto'] = 'uploads/perfis/' . basename(end($fotos));
}
$carreira = carreira($id);
$iniciais = mb_strtoupper(mb_substr($alvo['nome'], 0, 1));
$titulo = 'Perfil de ' . $alvo['nome'];
include __DIR__ . '/partials_header.php';
$redes = [
    'instagram' => ['Instagram', '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" class="fill"/></svg>'],
    'youtube' => ['YouTube', '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="4"/><path d="m10 9 5 3-5 3z" class="fill"/></svg>'],
    'twitch' => ['Twitch', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h17v12l-5 5h-4l-3 3v-3H5z"/><path d="M10 8v5M16 8v5"/></svg>'],
    'tiktok' => ['TikTok', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3v11.2a4.2 4.2 0 1 1-3.5-4.1M14 3c.5 3 2.5 4.7 5.5 5"/></svg>'],
    'discord' => ['Discord', '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 6c3-1.5 7-1.5 10 0l2 11c-2 1.5-3.5 2-5 2l-.7-1.3M7 6 5 17c2 1.5 3.5 2 5 2l.7-1.3M8.5 14h.1M15.5 14h.1"/><circle cx="9" cy="13" r="1" class="fill"/><circle cx="15" cy="13" r="1" class="fill"/></svg>'],
];
?>
<style>
.profile-wrap{padding-bottom:72px}.profile-hero{display:grid;grid-template-columns:160px 1fr auto;gap:28px;align-items:center;background:var(--ink);color:#fff;border-radius:14px;padding:30px;margin-bottom:24px;position:relative;overflow:hidden}.profile-hero:after{content:"";position:absolute;width:300px;height:300px;border:1px solid rgba(255,138,31,.28);border-radius:50%;right:-50px;top:-170px;pointer-events:none}.profile-photo{width:160px;height:160px;border-radius:14px;object-fit:cover;background:#29425a;border:2px solid rgba(255,255,255,.2)}.profile-placeholder{display:grid;place-items:center;font:800 72px var(--display);color:var(--orange)}.profile-hero h1{font-size:clamp(38px,5vw,60px);margin:0 0 10px}.profile-hero p{color:#c3cfdc;margin:0}.profile-chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.profile-chip{border:1px solid rgba(255,255,255,.22);border-radius:30px;padding:5px 11px;font-size:12px;color:#e1e8ef}.profile-rating{text-align:right;position:relative;z-index:1}.profile-rating strong{display:block;font:700 44px/1 var(--display)}.profile-rating small{color:#c3cfdc}.profile-layout{display:grid;grid-template-columns:1.2fr .8fr;gap:24px;align-items:start}.profile-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:26px;margin-bottom:20px}.profile-card h2{font-size:30px;margin-bottom:16px}.profile-bio{white-space:pre-wrap;color:#43576b;line-height:1.8;margin:0}.profile-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.profile-stat{background:#f4f6f9;border-radius:8px;padding:14px}.profile-stat strong{display:block;font:700 30px/1 var(--display)}.profile-stat span{font-size:12px;color:var(--muted)}.social-list{display:grid;gap:10px}.social-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--line);border-radius:8px;font-weight:700}.social-link:hover{border-color:var(--orange);background:#fff9f2}.social-link svg{width:24px;height:24px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.social-link .fill{fill:currentColor;stroke:none}.profile-fields{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}.profile-fields .wide{grid-column:1/-1}.profile-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:20px}.profile-edit{margin-top:26px}.profile-error{border-left:3px solid var(--red);background:#fdeaea;color:#8a2323;padding:12px 16px;margin-bottom:12px;border-radius:0 6px 6px 0}.profile-hint{font-size:12px;color:var(--muted);margin:5px 0 0}.profile-file{padding:9px}.profile-empty{color:var(--muted);font-size:14px}
@media(max-width:850px){.profile-hero{grid-template-columns:100px 1fr}.profile-photo{width:100px;height:100px}.profile-rating{grid-column:1/-1;text-align:left;display:flex;gap:10px;align-items:baseline}.profile-layout{grid-template-columns:1fr}}
@media(max-width:560px){.profile-hero{padding:20px;gap:16px}.profile-photo{width:78px;height:78px}.profile-hero h1{font-size:36px}.profile-card{padding:20px}.profile-fields{grid-template-columns:1fr}.profile-fields .wide{grid-column:auto}.profile-stats{gap:7px}.profile-stat{padding:10px}.profile-stat strong{font-size:25px}}
</style>
<div class="container page-heading">
  <span class="breadcrumb"><a href="ranking.php">Ranking</a> / Perfil do piloto</span>
  <p><?= $perfilPublico ? 'Perfil público do piloto' : 'Apresente sua trajetória e seus canais à comunidade.' ?></p>
</div>
<div class="container profile-wrap">
  <section class="profile-hero" aria-label="Resumo do perfil">
    <?php if (!empty($perfil['foto'])): ?><img class="profile-photo" src="<?= e($perfil['foto']) ?>" alt="Foto de <?= e($alvo['nome']) ?>">
    <?php else: ?><div class="profile-photo profile-placeholder" aria-hidden="true"><?= e($iniciais) ?></div><?php endif; ?>
    <div><h1><?= e($alvo['nome']) ?></h1><p><?= e($alvo['regiao'] ?? 'Região não informada') ?> · Piloto Virtual Paddock</p>
      <div class="profile-chips"><?php if (!empty($perfil['equipe'])): ?><span class="profile-chip">Equipe · <?= e($perfil['equipe']) ?></span><?php endif; ?><?php if (!empty($perfil['plataforma'])): ?><span class="profile-chip"><?= e($perfil['plataforma']) ?></span><?php endif; ?></div>
    </div>
    <div class="profile-rating"><strong><?= (int)$carreira['rating'] ?></strong><small>rating</small></div>
  </section>

  <div class="profile-layout">
    <div>
      <section class="profile-card"><h2>Sobre o piloto</h2>
        <p class="profile-bio"><?= !empty($perfil['descricao']) ? e($perfil['descricao']) : '<span class="profile-empty">Este piloto ainda não adicionou uma descrição.</span>' ?></p>
      </section>
      <section class="profile-card"><h2>Informações de corrida</h2>
        <div class="profile-stats"><div class="profile-stat"><strong><?= (int)$carreira['vit'] ?></strong><span>Vitórias</span></div><div class="profile-stat"><strong><?= (int)$carreira['podios'] ?></strong><span>Pódios</span></div><div class="profile-stat"><strong><?= e($perfil['numero'] ?? '—') ?></strong><span>Número</span></div></div>
        <div class="profile-chips" style="margin-top:18px"><?php foreach (['Equipe' => 'equipe', 'Carro' => 'carro', 'Plataforma' => 'plataforma', 'Pista favorita' => 'pista_favorita'] as $rotulo => $campo): if (!empty($perfil[$campo])): ?><span class="tag"><?= e($rotulo) ?>: <?= e($perfil[$campo]) ?></span><?php endif; endforeach; ?></div>
      </section>
    </div>
    <aside class="profile-card"><h2>Redes sociais</h2><div class="social-list">
      <?php $temRede = false; foreach ($redes as $chave => [$nome, $logo]): if (!empty($perfil['redes'][$chave])): $temRede = true; ?><a class="social-link" href="<?= e($perfil['redes'][$chave]) ?>" target="_blank" rel="noopener noreferrer"><?= $logo ?><span><?= e($nome) ?></span><span style="margin-left:auto;color:var(--muted);font-size:12px">Abrir ↗</span></a><?php endif; endforeach; ?>
      <?php if (!$temRede): ?><p class="profile-empty">Nenhum link social informado.</p><?php endif; ?>
    </div></aside>
  </div>

  <?php if (!$perfilPublico): ?>
    <?php if (isset($_GET['editar']) && $_GET['editar'] === '1'): ?>
  <section class="profile-card profile-edit" id="editar-perfil"><h2>Personalizar perfil</h2><p>Adicione uma apresentação, foto, informações de corrida e links dos seus canais.</p>
    <?php foreach ($erros as $erro): ?><div class="profile-error" role="alert"><?= e($erro) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['perfil_csrf']) ?>">
      <div class="profile-fields">
        <div class="wide"><label for="descricao">Descrição</label><textarea id="descricao" name="descricao" class="form-control" maxlength="1000" rows="4" placeholder="Conte sua trajetória, estilo de pilotagem e objetivos..."><?= e($perfil['descricao'] ?? '') ?></textarea></div>
        <div><label for="equipe">Equipe</label><input id="equipe" name="equipe" class="form-control" maxlength="80" value="<?= e($perfil['equipe'] ?? '') ?>"></div>
        <div><label for="numero">Número do carro</label><input id="numero" name="numero" class="form-control" maxlength="8" value="<?= e($perfil['numero'] ?? '') ?>"></div>
        <div><label for="carro">Carro / categoria</label><input id="carro" name="carro" class="form-control" maxlength="80" value="<?= e($perfil['carro'] ?? '') ?>" placeholder="Ex.: Porsche 911 GT3 R"></div>
        <div><label for="plataforma">Plataforma</label><input id="plataforma" name="plataforma" class="form-control" maxlength="60" value="<?= e($perfil['plataforma'] ?? '') ?>" placeholder="Ex.: iRacing, ACC"></div>
        <div class="wide"><label for="pista_favorita">Pista favorita</label><input id="pista_favorita" name="pista_favorita" class="form-control" maxlength="80" value="<?= e($perfil['pista_favorita'] ?? '') ?>"></div>
        <div class="wide"><label for="foto">Foto de perfil</label><input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="form-control profile-file"><p class="profile-hint">JPG, PNG, WEBP ou GIF · até 3 MB. A foto fica na pasta uploads/perfis do projeto.</p></div>
        <?php foreach ($redes as $chave => [$nome, $logo]): ?><div><label for="rede_<?= e($chave) ?>"><?= $nome ?></label><input id="rede_<?= e($chave) ?>" name="rede_<?= e($chave) ?>" type="url" class="form-control" maxlength="500" value="<?= e($perfil['redes'][$chave] ?? '') ?>" placeholder="https://..."></div><?php endforeach; ?>
      </div>
      <div class="profile-actions"><button class="button">Salvar perfil</button><?php if (!empty($perfil['foto'])): ?><label style="display:flex;align-items:center;gap:8px;margin:0;font-size:13px;font-weight:500"><input type="checkbox" name="remover_foto" value="1"> Remover a foto atual ao salvar</label><?php endif; ?></div>
    </form>
  </section>
    <?php else: ?>
  <section class="profile-card profile-edit">
    <h2>Meu perfil</h2>
    <p>Mostre suas informações aos outros pilotos e personalize sua apresentação.</p>
    <a class="button" href="perfil_piloto.php?editar=1#editar-perfil">Personalizar perfil</a>
  </section>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/partials_footer.php'; ?>
