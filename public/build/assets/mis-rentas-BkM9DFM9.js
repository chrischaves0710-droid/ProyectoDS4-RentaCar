import{t as e}from"./ApiClient-B4CE_Sca.js";import{t}from"./AppState-BANczeEP.js";var n=new e(`/api`),r=new t({data:[],pagination:{},loading:!1,error:null,paginaActual:1}),i=null,a=document.querySelector(`.contenedor-tabla`),o=document.querySelector(`.paginacion`);r.subscribe(e=>{if(e.loading){a&&(a.innerHTML=`<div style="text-align: center; padding: 2.5rem; color: #64748b;">⏳ Cargando historial de rentas...</div>`);return}if(e.error){a&&(a.innerHTML=`<div style="text-align: center; padding: 2.5rem; color: #dc2626;">⚠️ ${e.error}</div>`);return}if(!e.data||e.data.length===0){a&&(a.innerHTML=`<div style="text-align: center; padding: 2.5rem; color: #64748b;">No se encontraron contratos de alquiler registrados a su nombre.</div>`),o&&(o.innerHTML=``);return}s(e.data),c(e.pagination)});function s(e){a&&(a.innerHTML=`
        <table class="tabla-vehiculos">
            <caption class="visualmente-oculto">Registro histórico de alquileres asociados a su cuenta.</caption>
            <thead>
                <tr>
                    <th scope="col">N° Contrato</th>
                    <th scope="col">Vehículo</th>
                    <th scope="col">Período</th>
                    <th scope="col">Total Abonado</th>
                    <th scope="col">Estado</th>
                </tr>
            </thead>
            <tbody>
                ${e.map(e=>{let t=`estado-inactivo`,n=e.estado||`Finalizada`;n.toLowerCase().includes(`curso`)||n.toLowerCase()===`activa`?(t=`estado-disponible`,n=`En curso`):n.toLowerCase().includes(`cancelada`)&&(t=`estado-mantenimiento`,n=`Cancelada`);let r=e.vehiculo?`${e.vehiculo.marca} ${e.vehiculo.modelo}`:`Vehículo no especificado`,i=e.fecha_inicio?new Date(e.fecha_inicio).toLocaleDateString(`es-CR`):`N/A`,a=e.fecha_fin?new Date(e.fecha_fin).toLocaleDateString(`es-CR`):`N/A`;return`
            <tr>
                <td><strong>${e.numero_contrato||e.id}</strong></td>
                <td>${r}</td>
                <td>${i} - ${a}</td>
                <td>$${parseFloat(e.total_pagar||0).toFixed(2)}</td>
                <td>
                    <span class="estado ${t}">${n}</span>
                </td>
            </tr>
        `}).join(``)}
            </tbody>
        </table>`)}function c(e){if(!o||!e||!e.last_page)return;let{current_page:t,last_page:n}=e;o.innerHTML=`
        <button type="button" class="boton boton-secundario" id="btn-prev" ${t<=1?`disabled`:``}>Anterior</button>
        <span class="pagina-actual">Página ${t} de ${n}</span>
        <button type="button" class="boton boton-secundario" id="btn-next" ${t>=n?`disabled`:``}>Siguiente</button>
    `,document.getElementById(`btn-prev`)?.addEventListener(`click`,()=>{t>1&&l(t-1)}),document.getElementById(`btn-next`)?.addEventListener(`click`,()=>{t<n&&l(t+1)})}async function l(e=1){r.setState({loading:!0,error:null}),i&&i.abort(),i=new AbortController;try{let t=await n.get(`/rentas?page=${e}`,{signal:i.signal});t&&!t.aborted&&r.setState({data:t.data||t,pagination:t.meta||{current_page:e,last_page:1},loading:!1,paginaActual:e})}catch(e){e.status!==401&&r.setState({loading:!1,error:e.message||`Error al conectar con el servidor.`})}}l(1);