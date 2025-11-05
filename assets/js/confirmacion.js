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