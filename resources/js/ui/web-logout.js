
document.addEventListener('click', async (evento) => {

    const boton = evento.target.closest('.sesion button');

    if (!boton || boton.textContent.trim().toLowerCase() !== 'cerrar sesión') {
        return;
    }

    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.content;

    if (!csrfToken) {
        alert('No se pudo verificar la sesión. Recarga la página.');
        return;
    }

    boton.disabled = true;

    try {
        const token = localStorage.getItem('auth_token');

        const respuesta = await fetch('/web/logout', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(token
                    ? { 'Authorization': `Bearer ${token}` }
                    : {})
            }
        });

        if (
            !respuesta.ok ||
            respuesta.redirected ||
            !respuesta.headers.get('content-type')?.includes('application/json')
        ) {
            throw new Error('No se pudo cerrar la sesión.');
        }

        // El servidor ya invalidó la sesión y revocó el token.
        localStorage.removeItem('auth_token');

        sessionStorage.removeItem('return_url_401');
        sessionStorage.removeItem('form_backup_401');

        // Regresar al inicio de sesión
        window.location.replace('/login');

    } catch (error) {
        alert('No fue posible cerrar sesión. Inténtalo nuevamente.');
        boton.disabled = false;
    }
});
