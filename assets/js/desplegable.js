// Función para alternar (mostrar/ocultar) el menú desplegable
function desplegar() {
  document.getElementById("myDropdown").classList.toggle("show");
}

// Cierre automático del dropdown si se hace clic fuera de él
window.onclick = function(event) {
  // Verifica que el clic no sea sobre el botón que despliega el menú
  if (!event.target.matches('.dropbtn')) {
    // Obtiene todos los elementos con la clase 'right-btn-content'
    var dropdowns = document.getElementsByClassName("right-btn-content");
    for (var i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      // Si el dropdown está abierto, lo cierra
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}