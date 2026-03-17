function showAlert(
  type,
  message,
  position = "center",
  urlTicket = null,
  urlCarta = null,
  urlCompleto = null,
) {
  const titles = {
    success: "¡Éxito!",
    error: "¡Error!",
    warning: "¡Advertencia!",
    info: "¡Información!",
  };

  // Colores de los botones según el tipo
  const buttonColors = {
    success: "#4caf50", // Verde
    error: "#e74c3c", // Rojo
    warning: "#f39c12", // Amarillo
    info: "#3498db", // Azul
  };

  // Configuración base para estilos personalizados
  const customClass = {
    popup: `custom-popup swal2-${type}`,
    title: "custom-title",
    htmlContainer: "custom-html",
    confirmButton: "custom-confirm-button",
  };

  // Si hay código y URL, mostrar contenido especial
  if (position === "center" && urlTicket && urlCarta && urlCompleto) {
    const htmlContent = `
        <p class="text-muted mb-3">${message}</p>
        <hr>
        <p class="fw-semibold mb-3">Selecciona el formato de impresión:</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="${urlTicket}" target="_blank" class="btn btn-secondary">
                <i class="fas fa-receipt me-1"></i> Ticket
            </a>
            <a href="${urlCarta}" target="_blank" class="btn btn-primary">
                <i class="fas fa-file-alt me-1"></i> Carta
            </a>
            <a href="${urlCompleto}" target="_blank" class="btn btn-success">
                <i class="fas fa-file-pdf me-1"></i> Completo
            </a>
        </div>
    `;
    Swal.fire({
      title:
        '<span class="fs-5 fw-bold text-success"><i class="fas fa-check-circle me-2"></i>¡Orden Creada!</span>',
      icon: type,
      html: htmlContent,
      showCloseButton: true,
      showConfirmButton: false,
      customClass: {
        popup: "rounded-4 shadow",
        htmlContainer: "text-center",
      },
      background: "#f8fff9",
      color: "#212529",
    });
    return;
  }

  // Si no hay código o es otra posición, usar comportamiento clásico
  if (position === "center") {
    Swal.fire({
      title: titles[type] || "Notificación", // Título en español
      icon: type,
      html: `<div>${message}</div>`,
      showCloseButton: true,
      confirmButtonText: "Entendido", // Botón genérico
      focusConfirm: true,
      customClass,
      background: type === "success" ? "#f3fdf7" : "#ffffff", // Fondo dinámico
      color: "#333", // Texto oscuro
      confirmButtonColor: buttonColors[type] || "#4caf50", // Color dinámico del botón
    });
  }
  // Notificaciones tipo toast
  else {
    Swal.fire({
      icon: type,
      title: message,
      toast: true,
      position: position, // Posición dinámica
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      customClass: {
        popup: `custom-toast-popup swal2-${type}`,
        title: "custom-toast-title",
      },
      background: "#fdfdfd", // Fondo neutro
      color: "#333", // Texto oscuro
    });
  }
}
