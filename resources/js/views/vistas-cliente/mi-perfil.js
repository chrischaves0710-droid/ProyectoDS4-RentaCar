import { ApiClient } from '../../api/ApiClient.js';

// Se instancia el cliente para realizar las peticiones a la API
const api = new ApiClient('/api');

// Se ejecuta la lógica una vez que el documento HTML ha sido cargado completamente
document.addEventListener('DOMContentLoaded', async () => {
    
    // Se seleccionan los campos de texto de la interfaz
    const inputIdentificacion = document.getElementById('identificacion');
    const inputNombre = document.getElementById('nombre');
    const inputCorreo = document.getElementById('correo');
    const inputTelefono = document.getElementById('telefono');

    try {
        // Se obtienen los datos del usuario autenticado desde el servidor
        const usuario = await api.get('/user');
        
        if (usuario && usuario.data) {
            const datos = usuario.data;
            
            // Se rellenan los campos con la información recuperada, asignando un valor por defecto si no existe
            if (inputIdentificacion) inputIdentificacion.value = datos.identificacion || 'N/A';
            if (inputNombre) inputNombre.value = `${datos.nombre || ''} ${datos.apellidos || ''}`.trim() || 'N/A';
            if (inputCorreo) inputCorreo.value = datos.email || datos.correo || 'N/A';
            if (inputTelefono) inputTelefono.value = datos.telefono || 'N/A';
        }
    } catch (error) {
        // Se capturan los errores de red o servidor, exceptuando el 401 (exclusión de sesión)
        if (error.status !== 401) {
            alert('No se pudo cargar la información del perfil.');
            
            // Se actualizan los placeholders para reflejar visualmente el error en la interfaz
            if (inputIdentificacion) inputIdentificacion.placeholder = 'Error al cargar';
            if (inputNombre) inputNombre.placeholder = 'Error al cargar';
            if (inputCorreo) inputCorreo.placeholder = 'Error al cargar';
            if (inputTelefono) inputTelefono.placeholder = 'Error al cargar';
        }
    }
});