import { ApiClient } from '../../api/ApiClient.js';

const api = new ApiClient('/api');

// Se selecciona el formulario (sirve tanto para crear como para editar)
const formVehiculo = document.getElementById('form-vehiculo') || document.querySelector('form');

// =====================================================================
// 1. REQUERIMIENTO: Restaurar datos tras expiración de Token (401)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const backupData = sessionStorage.getItem('form_backup_401');
    
    if (backupData && formVehiculo) {
        const data = JSON.parse(backupData);
        
        // Se rellenan los campos con los datos guardados
        Object.keys(data).forEach(key => {
            const input = formVehiculo.elements[key];
            if (input) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = (input.value === data[key]);
                } else {
                    input.value = data[key];
                }
            }
        });

        // Se limpia el respaldo y se muestra un aviso
        sessionStorage.removeItem('form_backup_401');
        
        const alerta = document.createElement('div');
        alerta.style.cssText = 'background-color: #fef08a; color: #854d0e; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;';
        alerta.innerText = '💡 Se han restaurado los datos ingresados antes de la expiración de la sesión.';
        formVehiculo.prepend(alerta);
    }
});

// =====================================================================
// 2. REQUERIMIENTO: Envío asíncrono y Errores de Validación (422)
// =====================================================================
formVehiculo?.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    // Se establece el estado de carga en el botón
    const btnSubmit = formVehiculo.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.innerText;
    btnSubmit.innerText = 'Guardando...';
    btnSubmit.disabled = true;

    // Se recopilan los datos del formulario
    const formData = new FormData(formVehiculo);
    const payload = Object.fromEntries(formData.entries());
    
    // Se verifica si existe un ID (por URL o input oculto) para determinar si es Edición (PUT) o Creación (POST)
    const idVehiculo = new URLSearchParams(window.location.search).get('id') || payload.id;

    try {
        if (idVehiculo) {
            await api.put(`/vehiculos/${idVehiculo}`, payload);
            alert('Vehículo actualizado correctamente.');
        } else {
            await api.post('/vehiculos', payload);
            alert('Vehículo registrado correctamente.');
        }
        
        // Se redirige al listado tras éxito (ajustar la ruta según sea necesario)
        window.location.href = '/lab8/gerente/vehiculos';

    } catch (error) {
        // Se capturan los errores de validación del backend
        if (error.status === 422 && error.errors) {
            mostrarErrores(error.errors);
        } else if (error.status !== 401) {
            // Si el error no es 401 ni 422, se muestra un error general
            alert('Ocurrió un error al guardar: ' + (error.message || 'Intente nuevamente.'));
        }
    } finally {
        // Se restaura el estado original del botón
        btnSubmit.innerText = textoOriginal;
        btnSubmit.disabled = false;
    }
});

// =====================================================================
// 3. Funciones auxiliares para la interfaz de errores
// =====================================================================
function mostrarErrores(errores) {
    Object.keys(errores).forEach(campo => {
        const input = formVehiculo.querySelector(`[name="${campo}"]`);
        
        // Se busca el span correspondiente al error del campo (Ej: error-placa)
        const spanError = document.getElementById(`error-${campo}`); 
        
        if (input) {
            input.style.borderColor = '#dc2626'; // Borde rojo
            input.setAttribute('aria-invalid', 'true');
        }
        
        if (spanError) {
            spanError.textContent = errores[campo][0];
            spanError.style.display = 'block';
            spanError.style.color = '#dc2626';
            spanError.style.fontSize = '0.875rem';
            spanError.style.marginTop = '0.25rem';
        }
    });
    
    // Se enfoca el primer campo con error para accesibilidad
    const primerError = formVehiculo.querySelector('[aria-invalid="true"]');
    if (primerError) primerError.focus();
}

function limpiarErrores() {
    formVehiculo.querySelectorAll('.mensaje-error, [id^="error-"]').forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
    formVehiculo.querySelectorAll('input, select, textarea').forEach(input => {
        input.style.borderColor = ''; 
        input.removeAttribute('aria-invalid');
    });
}