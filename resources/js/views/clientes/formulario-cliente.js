import { ApiClient } from '../../api/ApiClient.js';

const api = new ApiClient('/api');

// Se selecciona el formulario principal (sirve tanto para crear como para editar)
const formCliente = document.querySelector('.secciones-formulario') || document.querySelector('form');

// =====================================================================
// 1. REQUERIMIENTO: Restaurar datos tras expiración de Token (401)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const backupData = sessionStorage.getItem('form_backup_401');
    
    if (backupData && formCliente) {
        const data = JSON.parse(backupData);
        
        // Se rellenan los campos con los datos guardados
        Object.keys(data).forEach(key => {
            const input = formCliente.elements[key];
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
        formCliente.prepend(alerta);
    }
});

// =====================================================================
// 2. REQUERIMIENTO: Envío asíncrono y Errores de Validación (422)
// =====================================================================
formCliente?.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    // Se establece el estado de carga en el botón
    const btnSubmit = formCliente.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.innerText;
    btnSubmit.innerText = 'Guardando...';
    btnSubmit.disabled = true;

    // Se recopilan los datos del formulario
    const formData = new FormData(formCliente);
    const payload = Object.fromEntries(formData.entries());
    
    // Se verifica si existe un ID en la URL para determinar si es Edición (PUT) o Creación (POST)
    const idCliente = new URLSearchParams(window.location.search).get('id') || payload.id;

    try {
        if (idCliente) {
            await api.put(`/clientes/${idCliente}`, payload);
            alert('Cliente actualizado correctamente.');
        } else {
            await api.post('/clientes', payload);
            alert('Cliente registrado exitosamente.');
        }
        
        // Se redirige al listado tras éxito
        window.location.href = '/lab8/gerente/clientes';

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
        const input = formCliente.querySelector(`[name="${campo}"]`);
        
        // Se busca el span correspondiente al error del campo (Ej: error-cedula)
        let spanError = document.getElementById(`error-${campo}`); 
        
        if (input) {
            input.style.borderColor = '#dc2626'; // Borde rojo
            input.setAttribute('aria-invalid', 'true');
            
            // Si no existe un span de error en el HTML, se crea dinámicamente debajo del input
            if (!spanError) {
                spanError = document.createElement('span');
                spanError.id = `error-${campo}`;
                spanError.className = 'mensaje-error';
                input.parentNode.appendChild(spanError);
            }
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
    const primerError = formCliente.querySelector('[aria-invalid="true"]');
    if (primerError) primerError.focus();
}

function limpiarErrores() {
    formCliente.querySelectorAll('.mensaje-error, [id^="error-"]').forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
    formCliente.querySelectorAll('input, select, textarea').forEach(input => {
        input.style.borderColor = ''; 
        input.removeAttribute('aria-invalid');
    });
}