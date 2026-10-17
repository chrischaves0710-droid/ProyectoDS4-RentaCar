import { ApiClient } from '../../api/ApiClient.js';

const api = new ApiClient('/api');

// Se selecciona el formulario principal
const formAccesorio = document.querySelector('.secciones-formulario') || document.querySelector('form');

// =====================================================================
// 1. REQUERIMIENTO: Restaurar datos tras expiración de Token (401)
// =====================================================================
document.addEventListener('DOMContentLoaded', () => {
    const backupData = sessionStorage.getItem('form_backup_401');
    
    if (backupData && formAccesorio) {
        const data = JSON.parse(backupData);
        
        // Se rellenan los campos con los datos guardados
        Object.keys(data).forEach(key => {
            const input = formAccesorio.elements[key];
            if (input) {
                input.value = data[key];
            }
        });

        // Se limpia el respaldo y se muestra un aviso
        sessionStorage.removeItem('form_backup_401');
        
        const alerta = document.createElement('div');
        alerta.style.cssText = 'background-color: #fef08a; color: #854d0e; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;';
        alerta.innerText = '💡 Se han restaurado los datos ingresados antes de la expiración de la sesión.';
        formAccesorio.prepend(alerta);
    }
});

// =====================================================================
// 2. REQUERIMIENTO: Envío asíncrono y Errores de Validación (422)
// =====================================================================
formAccesorio?.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    // Se establece el estado de carga en el botón
    const btnSubmit = formAccesorio.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.innerText;
    btnSubmit.innerText = 'Guardando...';
    btnSubmit.disabled = true;

    // Se recopilan los datos del formulario
    const formData = new FormData(formAccesorio);
    const payload = Object.fromEntries(formData.entries());
    
    // Se verifica si existe un ID en la URL para determinar la acción (PUT o POST)
    const idAccesorio = new URLSearchParams(window.location.search).get('id') || payload.id;

    try {
        if (idAccesorio) {
            await api.put(`/accesorios/${idAccesorio}`, payload);
            alert('Accesorio actualizado correctamente.');
        } else {
            await api.post('/accesorios', payload);
            alert('Accesorio registrado exitosamente.');
        }
        
        // Se determina la redirección dependiendo de la ruta actual
        const redirectUrl = window.location.pathname.includes('admin') 
            ? '/lab8/admin/accesorios' 
            : '/lab8/gerente/accesorios';
        
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
        const input = formAccesorio.querySelector(`[name="${campo}"]`);
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
    const primerError = formAccesorio.querySelector('[aria-invalid="true"]');
    if (primerError) primerError.focus();
}

function limpiarErrores() {
    formAccesorio.querySelectorAll('.mensaje-error, [id^="error-"]').forEach(span => {
        span.textContent = '';
        span.style.display = 'none';
    });
    formAccesorio.querySelectorAll('input, select, textarea').forEach(input => {
        input.style.borderColor = ''; 
        input.removeAttribute('aria-invalid');
    });
}