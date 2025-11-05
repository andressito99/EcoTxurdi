/* ===========================
   TOGGLE DE DETALLES (GENÉRICO)
   =========================== */

(function setupToggles() {
  // Función reutilizable para cualquier bloque
  function addToggleListener(blockId, itemClass) {
    const container = document.getElementById(blockId);
    if (!container) return;

    container.addEventListener("click", function (e) {
      const btn = e.target.closest(".toggle");
      if (!btn) return;

      const item = btn.closest("." + itemClass);
      if (!item) return;

      const id = item.getAttribute("data-id");
      const detalle = document.getElementById("detalle-" + id);
      if (!detalle) return;

      const isOpen = btn.getAttribute("aria-expanded") === "true";

      // Mostrar / Ocultar detalle
      detalle.style.display = isOpen ? "none" : "block";
      btn.setAttribute("aria-expanded", isOpen ? "false" : "true");
      btn.textContent = isOpen ? "⬇" : "⬆";
    });
  }

  // Aplica el mismo comportamiento a cada sección
  addToggleListener("solicitudes-block", "solicitud");
  addToggleListener("misiones-block", "mision");
  addToggleListener("usuarios-block", "usuario");
  addToggleListener("recompensas-block", "recompensa");
})();

/* ===========================
   CONFIRMACIONES DE ACCIÓN
   =========================== */

document.addEventListener("click", function (e) {
  const aceptar = e.target.closest(".aceptar");
  const denegar = e.target.closest(".denegar");
  const borrar = e.target.closest(".borrar");

  if (aceptar && !confirm("¿Estás seguro de que quieres ACEPTAR esta misión?")) {
    e.preventDefault();
  }

  if (denegar && !confirm("¿Estás seguro de que quieres DENEGAR esta misión?")) {
    e.preventDefault();
  }

  if (borrar && !confirm("¿Estás seguro de que quieres BORRARLO?")) {
    e.preventDefault();
  }
});
