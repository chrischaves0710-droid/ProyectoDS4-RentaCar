const key = 'form_backup_401';

export function saveCurrentForm() {
    const form = document.querySelector('form[data-preserve-session], form.formulario-renta, form.secciones-formulario, form#form-vehiculo, form#form-cliente, form#form-accesorio, form#form-estado, form#form-categoria');
    if (!form) return false;
    const values = {};
    for (const [name, value] of new FormData(form)) {
        if (typeof value !== 'string' || /password|clave|token|csrf/i.test(name)) continue;
        if (Object.hasOwn(values, name)) values[name] = [values[name], value].flat();
        else values[name] = value;
    }
    sessionStorage.setItem(key, JSON.stringify({ path: location.pathname + location.search, values }));
    return true;
}

export function restoreForm(form) {
    const raw = sessionStorage.getItem(key);
    if (!raw || !form) return false;
    let data;
    try { data = JSON.parse(raw); } catch { return false; }
    const currentPath = location.pathname + location.search;
    if (!data || data.path !== currentPath || !data.values || typeof data.values !== 'object') return false;
    let applied = false;
    for (const [name, stored] of Object.entries(data.values)) {
        const controls = Array.from(form.elements).filter(field => field.name === name);
        if (!controls.length) continue;
        const values = (Array.isArray(stored) ? stored : [stored]).map(String);
        for (const field of controls) {
            if (field.type === 'checkbox' || field.type === 'radio') field.checked = values.includes(field.value);
            else if (field.tagName === 'SELECT' && !field.multiple) {
                if (!Array.from(field.options).some(option => option.value === values[0])) field.add(new Option(`Recuperado (ID ${values[0]})`, values[0]));
                field.value = values[0];
            } else field.value = values[0] ?? '';
            applied = true;
        }
    }
    for (const control of form.querySelectorAll('input[name="accesorios[]"]')) {
        const quantity = form.elements.namedItem(`cantidad_${control.value}`);
        if (quantity) quantity.disabled = !control.checked;
    }
    if (applied) sessionStorage.removeItem(key);
    return applied;
}
