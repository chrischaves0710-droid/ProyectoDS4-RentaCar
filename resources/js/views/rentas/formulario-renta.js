import { ApiClient } from '../../api/ApiClient.js';

const api = new ApiClient('/api');

// Se selecciona el formulario por su clase (sirve para nueva-renta y editar-renta)
const formRenta = document.querySelector('.formulario-renta') || document.querySelector('form');

// =====================================================================
// 1. REQUERIMIENTO: Restaurar datos tras expiración de Token (401)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const backupData = sessionStorage.getItem('form_backup_401');
    
    if (backupData && formRenta) {
        const data = JSON.parse(backupData);
        
        // Se rellenan los campos con los datos guardados
        Object.keys(data).forEach(key => {
            // Se manejan casos donde existen múltiples elementos (ej: checkboxes de accesorios[])
            const inputs = formRenta.querySelectorAll(`[name="${key}"]`);
            
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    // Si el dato guardado es un array (múltiples checkboxes seleccionados)
                    if (Array.isArray(data[key])) {
                        input.checked = data[key].includes(input.value);
                    } else {
                        input.checked = (input.value === data[key]);
                    }
                } else {
                    input.value = data[key];
                }
            });
        });

        // Se limpia el respaldo y se muestra un aviso
        sessionStorage.removeItem('form_backup_401');
        
        const alerta = document.createElement('div');
        alerta.style.cssText = 'background-color: #fef08a; color: #854d0e; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;';
        alerta.innerText = '💡 Se han restaurado los datos ingresados antes de la expiración de la sesión.';
        formRenta.prepend(alerta);
    }
});

// =====================================================================
// 2. REQUERIMIENTO: Envío asíncrono y Errores de Validación (422)
// =====================================================================
formRenta?.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    // Se establece el estado de carga en el botón
    const btnSubmit = formRenta.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.innerText;
    btnSubmit.innerText = 'Procesando...';
    btnSubmit.disabled = true;

    // Se recopilan los datos del formulario
    const formData = new FormData(formRenta);
    
    // Se extraen todos los valores, permitiendo arrays para campos como accesorios[]
    const payload = {};
    formData.forEach((value, key) => {
        if (key.endsWith('[]')) {
            const cleanKey = key.slice(0, -2);
            if (!payload[cleanKey]) {
                payload[cleanKey] = [];
            }
            payload[cleanKey].push(value);
        } else {
            payload[key] = value;
        }
    });
    
    // Se verifica si existe un ID en la URL para determinar si es Edición (PUT) o Creación (POST)
    const idRenta = new URLSearchParams(window.location.search).get('id') || payload.id;

    try {
        if (idRenta) {
            await api.put(`/rentas/${idRenta}`, payload);
            alert('Contrato de renta actualizado correctamente.');
        } else {
            await api.post('/rentas', payload);
            alert('Contrato de renta registrado exitosamente.');
        }
        
        // Se redirige al listado tras éxito
        window.location.href = '/lab8/gerente/rentas';

    } catch (error) {
        // Se capturan los errores de validación del backend
        if (error.status === 422 && error.errors) {
            mostrarErrores(error.errors);
        } else if (error.status !== 401) {
            alert('Ocurrió un error al procesar la renta: ' + (error.message || 'Intente nuevamente.'));
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
        // Si el backend devuelve error en un array (ej: accesorios.0), se limpia la clave
        const nombreCampo = campo.includes('.') ? campo.split('.')[0] + '[]' : campo;
        const input = formRenta.querySelector(`[name="${nombreCampo}"]`);
        
        const spanError = document.getElementById(`error-${campo.replace('.', '-')}`); 
        
        if (input) {
            input.style.borderColor = '#dc2626';
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
    
    // Se enfoca el primer campo con error
    const primerError = formRenta.querySelector('[aria-invalid="true"]');
    if (primerError) primerError.focus();
}

function limpiarErrores() {
    formRenta.querySelectorAll('.mensaje-error, [id^="error-"]').forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
    formRenta.querySelectorAll('input, select, textarea').forEach(input => {
        input.style.borderColor = ''; 
        input.removeAttribute('aria-invalid');
    });
}