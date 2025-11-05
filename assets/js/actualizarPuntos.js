// Función para actualizar los puntos del usuario en tiempo real
function actualizarPuntos() {
  // Realiza una petición fetch al script PHP que devuelve los puntos del usuario en formato JSON
  fetch(window.BASE_URL + '/assets/actualizarPuntos.php')
    .then(res => res.json()) // Convierte la respuesta en JSON
    .then(data => {
      if (!data) return; // Si no hay datos, no hace nada

      // Selecciona todos los elementos que muestran los puntos del usuario
      const puntos = document.querySelectorAll('.puntosUsuario p');

      // Si existe al menos un elemento, actualiza su contenido con los puntos obtenidos
      if (puntos.length >= 1) {
        puntos[0].textContent = `✦ ${data.puntosCambio}`;
      }
    })
    .catch(err => console.error('Error al obtener puntos:', err)); // Manejo de errores
}