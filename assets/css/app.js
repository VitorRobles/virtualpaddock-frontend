document.addEventListener('DOMContentLoaded', () => {
  const t = document.getElementById('toast');
  if (t && t.dataset.msg) { t.textContent = t.dataset.msg; t.classList.add('visible'); setTimeout(() => t.classList.remove('visible'), 3800); }
  document.querySelectorAll('[data-open]').forEach(b => b.addEventListener('click', () => document.getElementById(b.dataset.open).showModal()));
  document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog').close()));
  const busca = document.querySelector('[data-busca]'), chips = document.querySelectorAll('.chip[data-status]');
  const itens = [...document.querySelectorAll('[data-item]')], cont = document.getElementById('contagem');
  let status = '';
  const filtrar = () => {
    const q = (busca ? busca.value : '').toLowerCase().trim(); let n = 0;
    itens.forEach(i => { const ok = i.dataset.item.toLowerCase().includes(q) && (!status || i.dataset.status === status); i.hidden = !ok; if (ok) n++; });
    if (cont) cont.textContent = n + (n === 1 ? ' resultado' : ' resultados');
  };
  if (busca) busca.addEventListener('input', filtrar);
  chips.forEach(c => c.addEventListener('click', () => {
    chips.forEach(x => x.setAttribute('aria-pressed', 'false')); c.setAttribute('aria-pressed', 'true'); status = c.dataset.status; filtrar();
  }));
});
