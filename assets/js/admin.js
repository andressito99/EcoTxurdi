(function() {
    const container = document.getElementById('solicitudes-block');
    if (!container) return;

    container.addEventListener('click', function(e) {
      const btn = e.target.closest('.toggle');
      if (!btn) return;

      const item = btn.closest('.solicitud');
      if (!item) return;

      const id = item.getAttribute('data-id');
      const detalle = document.getElementById('detalle-solicitud-' + id);
      if (!detalle) return;

      const isOpen = btn.getAttribute('aria-expanded') === 'true';
      if (isOpen) {
        detalle.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
        btn.textContent = '⬇';
      } else {
        detalle.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        btn.textContent = '⬆';
      }
    });
  })();

  (function() {
    const container = document.getElementById('misiones-block');
    if (!container) return;

    container.addEventListener('click', function(e) {
      const btn = e.target.closest('.toggle');
      if (!btn) return;

      const item = btn.closest('.mision');
      if (!item) return;

      const id = item.getAttribute('data-id');
      const detalle = document.getElementById('detalle-mision-' + id);
      if (!detalle) return;

      const isOpen = btn.getAttribute('aria-expanded') === 'true';
      if (isOpen) {
        detalle.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
        btn.textContent = '⬇';
      } else {
        detalle.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        btn.textContent = '⬆';
      }
    });
  })();

  (function() {
  const container = document.getElementById('usuarios-block');
  if (container) {
    container.addEventListener('click', function(e) {
      const btn = e.target.closest('.toggle');
      if (!btn) return;

      const item = btn.closest('.usuario');
      if (!item) return;

      const id = item.getAttribute('data-id');
      const detalle = document.getElementById('detalle-usuario-' + id);
      if (!detalle) return;

      const isOpen = btn.getAttribute('aria-expanded') === 'true';
      if (isOpen) {
        detalle.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
        btn.textContent = '⬇';
      } else {
        detalle.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        btn.textContent = '⬆';
      }
    });
  }
})();

(function() {
  const recompensasBlock = document.getElementById('recompensas-block');
  if (recompensasBlock) {
    recompensasBlock.addEventListener('click', function(e) {
      const btn = e.target.closest('.toggle');
      if (!btn) return;

      const item = btn.closest('.recompensa');
      if (!item) return;

      const id = item.getAttribute('data-id');
      const detalle = document.getElementById('detalle-recompensa-' + id);
      if (!detalle) return;

      const isOpen = btn.getAttribute('aria-expanded') === 'true';
      if (isOpen) {
        detalle.style.display = 'none';
        btn.setAttribute('aria-expanded', 'false');
        btn.textContent = '⬇';
      } else {
        detalle.style.display = 'block';
        btn.setAttribute('aria-expanded', 'true');
        btn.textContent = '⬆';
      }
    });
  }
})();


  document.addEventListener('click', function(e) {
    const aceptar = e.target.closest('.aceptar');
    const denegar = e.target.closest('.denegar');
    const borrar = e.target.closest('.borrar');

    if (aceptar && !confirm('¿Estás seguro de que quieres ACEPTAR esta misión?')) {
      e.preventDefault();
    }
    if (denegar && !confirm('¿Estás seguro de que quieres DENEGAR esta misión?')) {
      e.preventDefault();
    }
    if (borrar && !confirm('¿Estás seguro de que quieres BORRARLO?')) {
      e.preventDefault();
    }
  });