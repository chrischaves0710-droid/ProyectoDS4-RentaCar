import { ApiClient } from '../../api/ApiClient.js';

const api = new ApiClient('/api');

// Se selecciona el formulario principal
const formEstado = document.querySelector('.secciones-formulario') || document.querySelector('form');

// =====================================================================
// 1. REQUERIMIENTO: Restaurar datos tras expiración de Token (401)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const backupData = sessionStorage.getItem('form_backup_401');
    
    if (backupData && formEstado) {
        const data = JSON.parse(backupData);
        
        // Se rellenan los campos con los datos guardados
        Object.keys(data).forEach(key => {
            const input = formEstado.elements[key];
            if (input) {
                input.value = data[key];
            }
        });

        // Se limpia el respaldo y se muestra un aviso
        sessionStorage.removeItem('form_backup_401');
        
        const alerta = document.createElement('div');
        alerta.style.cssText = 'background-color: #fef08a; color: #854d0e; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;';
        alerta.innerText = '💡 Se han restaurado los datos ingresados antes de la expiración de la sesión.';
        formEstado.prepend(alerta);
    }
});

// =====================================================================
// 2. REQUERIMIENTO: Envío asíncrono y Errores de Validación (422)
// =====================================================================
formEstado?.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    // Se establece el estado de carga en el botón
    const btnSubmit = formEstado.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.innerText;
    btnSubmit.innerText = 'Guardando...';
    btnSubmit.disabled = true;

    // Se recopilan los datos del formulario
    const formData = new FormData(formEstado);
    const payload = Object.fromEntries(formData.entries());
    
    // Se verifica si existe un ID en la URL para determinar la acción (PUT o POST)
    const idEstado = new URLSearchParams(window.location.search).get('id') || payload.id;

    try {
        if (idEstado) {
            await api.put(`/estados/${idEstado}`, payload);
            alert('Estado actualizado correctamente.');
        } else {
            await api.post('/estados', payload);
            alert('Estado registrado exitosamente.');
        }
        
        // Se determina la redirección dependiendo de la ruta actual
        const redirectUrl = window.location.pathname.includes('admin') 
            ? '/lab8/admin/estados' 
            : '/lab8/gerente/estados';
        
        window.location.href = redirectUrl;

    } catch (error) {
        // Se capturan los errores de validación del backend
        if (error.status === 422 && error.errors) {
            mostrarErrores(error.errors);
        } else if (error.status !== 401) {
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
        const input = formEstado.querySelector(`[name="${campo}"]`);
        let spanError = document.getElementById(`error-${campo}`); 
        
        if (input) {
            input.style.borderColor = '#dc2626';
            input.setAttribute('aria-invalid', 'true');
            
            // Si el span no existe, se crea dinámicamente debajo del campo
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
    
    // Se enfoca el primer campo con error para facilitar correcciones
    const primerError = formEstado.querySelector('[aria-invalid="true"]');
    if (primerError) primerError.focus();
}

function limpiarErrores() {
    formEstado.querySelectorAll('.mensaje-error, [id^="error-"]').forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
    formEstado.querySelectorAll('input, select, textarea').forEach(input => {
        input.style.borderColor = ''; 
        input.removeAttribute('aria-invalid');
    });
}