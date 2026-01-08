function showAlert(type, message, position = 'center', codigo = null, urlBaseConsulta = null, cedula = null) {
    const titles = {
        success: "¡Éxito!",
        error: "¡Error!",
        warning: "¡Advertencia!",
        info: "¡Información!",
    };

    // Colores de los botones según el tipo
    const buttonColors = {
        success: '#4caf50', // Verde
        error: '#e74c3c', // Rojo
        warning: '#f39c12', // Amarillo
        info: '#3498db', // Azul
    };

    // Configuración base para estilos personalizados
    const customClass = {
        popup: `custom-popup swal2-${type}`,
        title: 'custom-title',
        htmlContainer: 'custom-html',
        confirmButton: 'custom-confirm-button',
    };

    // Si hay código y URL, mostrar contenido especial
    if (position === 'center' && codigo && urlBaseConsulta && cedula) {
        const urlCompleta = `${urlBaseConsulta}?codigo=${encodeURIComponent(codigo)}&cedula=${encodeURIComponent(cedula)}`;
        const htmlContent = `
            <div>${message}</div>
            <hr style="margin: 10px 0;">
            <div><strong>Código del examen:</strong> <span style="font-size: 1.2em;">${codigo}</span></div>
            <div><strong>Cédula del paciente:</strong> <span style="font-size: 1.2em;">${cedula}</span></div>
            <div style="margin-top:5px;">Consulta el informe: <a href="${urlCompleta}" target="_blank">${urlCompleta}</a></div>
            <div id="qr-code-modal" style="margin-top:10px; display: flex; justify-content: center;"></div>
            <div style="margin-top:15px; text-align: center;">
                <button onclick="printExamCode('${codigo}', '${urlCompleta}', '${cedula}')" class="swal2-confirm swal2-styled" style="background-color: #3498db;">
                    Imprimir Código
                </button>
            </div>
        `;

        Swal.fire({
            title: titles[type] || "Notificación",
            icon: type,
            html: htmlContent,
            showCloseButton: true,
            showConfirmButton: false,
            customClass,
            background: type === 'success' ? '#f3fdf7' : '#ffffff',
            color: '#333',
            didOpen: () => {
                const qrText = urlCompleta;
                new QRCode(document.getElementById("qr-code-modal"), {
                    text: qrText,
                    width: 128,
                    height: 128,
                });
            }
        });
        return;
    }

    // Si no hay código o es otra posición, usar comportamiento clásico
    if (position === 'center') {
        Swal.fire({
            title: titles[type] || "Notificación", // Título en español
            icon: type,
            html: `<div>${message}</div>`,
            showCloseButton: true,
            confirmButtonText: 'Entendido', // Botón genérico
            focusConfirm: true,
            customClass,
            background: type === 'success' ? '#f3fdf7' : '#ffffff', // Fondo dinámico
            color: '#333', // Texto oscuro
            confirmButtonColor: buttonColors[type] || '#4caf50', // Color dinámico del botón
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
                title: 'custom-toast-title',
            },
            background: '#fdfdfd', // Fondo neutro
            color: '#333', // Texto oscuro
        });
    }
}

// Función para imprimir el código, QR y enlace
function printExamCode(codigo, urlCompleta, cedula = null) {
    const containerId = 'printable-exam-code';

    const backgroundUrl = `${window.location.origin}/assets/images/backgrounds/img-pdf.png`;

    const htmlContent = `
        <div id="${containerId}" style="
            position: relative;
            width: 100%;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 60px 40px;
            background: url('${backgroundUrl}') no-repeat center center;
            background-size: cover;
            box-sizing: border-box;
        ">
            <div style="text-align: center; padding-top: 80px;">
                <h1 style="color: #2c3e50; margin-bottom: 10px;">Informe del Examen</h1>
                <p style="font-size: 18px;">Código del Examen:</p>
                <div style="font-size: 28px; font-weight: bold; color: #2980b9; margin-bottom: 20px;">${codigo}</div>
                <div><strong>Cédula del paciente:</strong> <span style="font-size: 1.2em;">${cedula}</span></div>
                <div id="qr-code-print" style="display: flex; justify-content: center; margin: 20px auto;"></div>
                <p style="font-size: 16px; margin-top: 10px;">Consultar informe:</p>
                <a href="${urlCompleta}" target="_blank" style="font-size: 14px; color: #3498db;">${urlCompleta}</a>
            </div>
        </div>
    `;

    const printContainer = document.createElement('div');
    printContainer.innerHTML = htmlContent;
    document.body.appendChild(printContainer);

    // Generar QR en contenedor
    new QRCode(printContainer.querySelector('#qr-code-print'), {
        text: urlCompleta,
        width: 150,
        height: 150,
    });

    // Imprimir después de que se genere el QR
    setTimeout(() => {
        printJS({
            printable: containerId,
            type: 'html',
            targetStyles: ['*'],
            scanStyles: false,
            style: `
                @media print {
                    body {
                        margin: 0;
                        background: none;
                    }
                }
            `,
        });

        // Eliminar contenedor temporal
        setTimeout(() => document.body.removeChild(printContainer), 1000);
    }, 500);
}