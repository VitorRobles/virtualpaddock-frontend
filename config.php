<?php
// Somente front-end: sem banco de dados. Tudo fica no cache temporário (sessão PHP).
session_start();
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash($m = null) {
    if ($m !== null) { $_SESSION['flash'] = $m; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}
function seed() {
    $h = password_hash('123456', PASSWORD_DEFAULT);
    $u = fn($id, $n, $m, $i, $r, $t) => ['id' => $id, 'nome' => $n, 'email' => $m, 'senha' => $h, 'idade' => $i, 'regiao' => $r, 'tipo' => $t];
    return [
        'prox' => ['usuario' => 6, 'camp' => 3],
        'usuarios' => [
            1 => $u(1, 'Marcus Louro', 'marcus@email.com', 24, 'Sul', 'Piloto'),
            2 => $u(2, 'Vanessa Keiser', 'vanessa@email.com', 27, 'Sudeste', 'Piloto'),
            3 => $u(3, 'Allison Tubarão', 'allison@email.com', 22, 'Sul', 'Piloto'),
            4 => $u(4, 'Pedro Rapidolo', 'pedro@email.com', 30, 'Nordeste', 'Piloto'),
            5 => $u(5, 'Race Week Org', 'org@email.com', 35, 'Sudeste', 'Organizador'),
        ],
        'carreira' => [1 => ['rating' => 2410, 'vit' => 12, 'podios' => 18], 2 => ['rating' => 2180, 'vit' => 9, 'podios' => 15],
                       3 => ['rating' => 1950, 'vit' => 5, 'podios' => 10], 4 => ['rating' => 1720, 'vit' => 2, 'podios' => 6]],
        'prefs' => [],
        'pistas' => [1 => 'Silverstone', 2 => 'Monza', 3 => 'Spa-Francorchamps', 4 => 'Red Bull Ring', 5 => 'Le Mans', 6 => 'Daytona'],
        'campeonatos' => [
            1 => ['id' => 1, 'org' => 5, 'nome' => 'Race Week SP', 'categoria' => 'GT3', 'plataforma' => 'iRacing', 'status' => 'Inscrições Abertas',
                  'inicio' => '2027-01-10', 'fim' => '2027-02-10', 'vagas' => 24,
                  'regras' => ['Duração: 3 horas', 'Mínima 2 pilotos por equipe', 'Pit stops obrigatórios', 'Multi-classe (LMP2 + GTE)']],
            2 => ['id' => 2, 'org' => 5, 'nome' => 'Corsa Cup', 'categoria' => 'GT3', 'plataforma' => 'ACC', 'status' => 'Em Andamento',
                  'inicio' => '2026-10-01', 'fim' => '2026-12-20', 'vagas' => 20, 'regras' => ['Largada lançada', 'Sem ajuda de tração']],
        ],
        'corridas' => [
            ['id' => 1, 'camp' => 1, 'pista' => 5, 'data' => '2027-01-10 20:00', 'voltas' => 40, 'rodada' => 1],
            ['id' => 2, 'camp' => 1, 'pista' => 6, 'data' => '2027-01-24 20:00', 'voltas' => 40, 'rodada' => 2],
            ['id' => 3, 'camp' => 1, 'pista' => 1, 'data' => '2027-02-07 20:00', 'voltas' => 40, 'rodada' => 3],
            ['id' => 4, 'camp' => 2, 'pista' => 1, 'data' => '2026-10-10 20:00', 'voltas' => 20, 'rodada' => 1],
            ['id' => 5, 'camp' => 2, 'pista' => 2, 'data' => '2026-10-24 20:00', 'voltas' => 20, 'rodada' => 2],
            ['id' => 6, 'camp' => 2, 'pista' => 3, 'data' => '2026-11-07 20:00', 'voltas' => 20, 'rodada' => 3],
            ['id' => 7, 'camp' => 2, 'pista' => 4, 'data' => '2026-11-14 20:00', 'voltas' => 20, 'rodada' => 4],
        ],
        'inscricoes' => [['uid' => 1, 'camp' => 2, 'status' => 'Aprovado'], ['uid' => 2, 'camp' => 2, 'status' => 'Aprovado'],
                         ['uid' => 3, 'camp' => 2, 'status' => 'Aprovado'], ['uid' => 4, 'camp' => 2, 'status' => 'Aprovado']],
        'resultados' => [
            ['corrida' => 4, 'uid' => 1, 'pos' => 1, 'pontos' => 25, 'status' => 'Terminou'],
            ['corrida' => 5, 'uid' => 1, 'pos' => 3, 'pontos' => 15, 'status' => 'Terminou'],
            ['corrida' => 6, 'uid' => 1, 'pos' => 2, 'pontos' => 18, 'status' => 'Terminou'],
            ['corrida' => 7, 'uid' => 1, 'pos' => 1, 'pontos' => 25, 'status' => 'Terminou'],
        ],
        'incidentes' => [
            ['corrida' => 5, 'autor' => 2, 'acusado' => 1, 'tipo' => 'Contato leve', 'volta' => 3, 'status' => 'Julgado', 'decisao' => 'Sem penalidade'],
            ['corrida' => 5, 'autor' => 1, 'acusado' => 3, 'tipo' => 'Saída de pista', 'volta' => 8, 'status' => 'Julgado', 'decisao' => 'Advertência'],
            ['corrida' => 7, 'autor' => 3, 'acusado' => 1, 'tipo' => 'Batida forte', 'volta' => 12, 'status' => 'Julgado', 'decisao' => 'Penalidade de 20 segundos'],
        ],
        'notif' => [],
    ];
}
if (!isset($_SESSION['db'])) $_SESSION['db'] = seed();
function db() { return $_SESSION['db']; }
function user() { return isset($_SESSION['uid']) ? (db()['usuarios'][$_SESSION['uid']] ?? null) : null; }
function need_login() { $u = user(); if (!$u) { header('Location: login.php'); exit; } return $u; }
function carreira($uid) { return db()['carreira'][$uid] ?? ['rating' => 1000, 'vit' => 0, 'podios' => 0]; }
function prefs($uid) { return db()['prefs'][$uid] ?? ['push' => 1, 'som' => 1, 'vib' => 1, 'idioma' => 'pt-BR']; }
function inscritos($camp) { return count(array_filter(db()['inscricoes'], fn($i) => $i['camp'] == $camp && $i['status'] !== 'Rejeitado')); }

// ---------- helpers de interface ----------
function ico($n, $cls = 'icon') {
    static $p = [
        'arrow' => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'gauge' => '<path d="M4 18a8 8 0 1 1 16 0"/><path d="M12 18l4-6"/>',
        'chart' => '<path d="M5 20v-7M12 20V5M19 20v-10"/>',
        'flag' => '<path d="M5 21V4M5 4h13l-2 4 2 4H5"/>',
        'alert' => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18h.01"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 15-5 16 0"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M19 5l-2 2M7 17l-2 2"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c1-5 13-5 14 0M16 5a3.5 3.5 0 0 1 0 7M18 15c3 .5 4 2.5 4 5"/>',
        'trophy' => '<path d="M7 4h10v6a5 5 0 0 1-10 0ZM7 6H3c0 4 2 5 4 5M17 6h4c0 4-2 5-4 5M12 15v4M8 21h8"/>',
    ];
    return '<svg class="' . $cls . '" viewBox="0 0 24 24" aria-hidden="true">' . ($p[$n] ?? $p['flag']) . '</svg>';
}
function nv($arqs, $txt, $atual) {
    $arqs = (array)$arqs;
    return '<a href="' . $arqs[0] . '"' . (in_array($atual, $arqs) ? ' class="active" aria-current="page"' : '') . '>' . $txt . '</a>';
}
function pista_svg() {
    return '<svg class="hero-track" viewBox="0 0 600 400" aria-hidden="true"><path d="M90 300c-50-30-40-110 20-130 40-14 60-50 110-80 60-36 150-30 190 20 30 38 10 70 50 100 50 38 80 100 20 130-60 28-150-10-210 10-60 20-120 10-180-20Z"/><path class="t2" d="M90 300c-50-30-40-110 20-130 40-14 60-50 110-80 60-36 150-30 190 20 30 38 10 70 50 100 50 38 80 100 20 130-60 28-150-10-210 10-60 20-120 10-180-20Z"/></svg>';
}
function card_painel($href, $art, $cor, $meta, $titulo, $desc, $cta) {
    return '<article class="guide-card"><div class="card-art ' . $cor . '">' . ico($art, 'big') . '</div><div class="card-content"><div class="card-meta">' . e($meta) . '</div><h3>' . e($titulo) . '</h3><p>' . e($desc) . '</p><div class="card-bottom"><a class="plain-button" href="' . $href . '">' . e($cta) . ico('arrow') . '</a></div></div></article>';
}
