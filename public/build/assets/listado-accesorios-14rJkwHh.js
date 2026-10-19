import{t as e}from"./ApiClient-B4CE_Sca.js";import{t}from"./AppState-BANczeEP.js";var n=new e(`/api`),r=new t({data:[],pagination:{},loading:!1,error:null,paginaActual:1,busqueda:``}),i=document.querySelector(`.contenedor-tabla`),a=document.querySelector(`.paginacion`),o=document.getElementById(`buscar-accesorio`),s=document.querySelector(`.formulario-filtros`),c=document.querySelector(`.encabezado-listado p`),l=location.pathname.startsWith(`/lab8/admin/`)?`/lab8/admin/accesorios/editar`:`/lab8/gerente/accesorios/editar`,u=null,d=null,f=0;function p(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#39;`})[e])}function m(){f++,d?.abort(),d=null}function h(e){i.innerHTML=`
        <table class="tabla-vehiculos">

            <caption class="visualmente-oculto">
                Accesorios registrados
            </caption>

            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del accesorio</th>
                    <th scope="col">Precio unitario diario</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>

            <tbody>${e.map(e=>{let t=encodeURIComponent(String(e.id)),n=Number(e.precio_unitario),r=Number.isFinite(n)?n.toFixed(2):`0.00`;return`
            <tr>
                <td>
                    <strong>${p(e.id)}</strong>
                </td>

                <td>${p(e.nombre)}</td>

                <td>$${r}</td>

                <td>
                    <div class="acciones-tabla">

                        <a class="accion-tabla"
                           href="${l}?id=${t}">
                            Editar
                        </a>

                        <button type="button"
                                class="accion-tabla accion-eliminar"
                                data-eliminar-id="${t}">
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>`}).join(``)}</tbody>

        </table>`}function g(e){let t=Math.max(1,Number(e.current_page)||1),n=Math.max(1,Number(e.last_page??e.total_pages)||1);a.innerHTML=`
        <button type="button"
                class="boton boton-secundario"
                data-pagina="${t-1}"
                ${t<=1?`disabled`:``}>
            Anterior
        </button>

        <span class="pagina-actual" aria-current="page">
            ${t} de ${n}
        </span>

        <button type="button"
                class="boton boton-secundario"
                data-pagina="${t+1}"
                ${t>=n?`disabled`:``}>
            Siguiente
        </button>`}r.subscribe(e=>{if(i&&a){if(e.loading){i.innerHTML=`
            <p role="status"
               style="padding:2rem;text-align:center">
                Cargando accesorios...
            </p>`,a.innerHTML=``;return}if(e.error){i.innerHTML=`
            <div style="padding:2rem;text-align:center">

                <p role="alert">
                    ${p(e.error)}
                </p>

                <button type="button"
                        id="reintentar-accesorios"
                        class="boton boton-secundario">
                    Reintentar
                </button>

            </div>`,a.innerHTML=``,document.getElementById(`reintentar-accesorios`)?.addEventListener(`click`,()=>{_(e.paginaActual)});return}if(e.data.length===0){i.innerHTML=`
            <p style="padding:2rem;text-align:center">
                No se encontraron accesorios.
            </p>`,a.innerHTML=``,c&&(c.textContent=`0 accesorios encontrados`);return}h(e.data),g(e.pagination),c&&(c.textContent=`${e.pagination.total??e.data.length} accesorios encontrados`)}});async function _(e=1){m();let t=f,i=new AbortController;d=i,r.setState({loading:!0,error:null,paginaActual:e});let a=new URLSearchParams({page:String(e),per_page:`10`}),o=r.getState().busqueda;o&&a.set(`q`,o);try{let o=await n.get(`/accesorios?${a.toString()}`,{signal:i.signal});if(t!==f||i.signal.aborted||o?.aborted)return;r.setState({data:Array.isArray(o?.data)?o.data:[],pagination:o?.meta??{current_page:e,last_page:1},loading:!1,error:null})}catch(e){if(t!==f||i.signal.aborted||e?.name===`AbortError`)return;e?.status!==401&&r.setState({loading:!1,error:e?.message??`No se pudieron cargar los accesorios.`})}finally{d===i&&(d=null)}}o?.addEventListener(`input`,()=>{clearTimeout(u),m(),r.setState({busqueda:o.value.trim(),paginaActual:1,loading:!0,error:null}),u=setTimeout(()=>{_(1)},400)}),s?.addEventListener(`submit`,e=>{e.preventDefault(),clearTimeout(u),r.setState({busqueda:o?.value.trim()??``}),_(1)}),a?.addEventListener(`click`,e=>{let t=e.target.closest(`button[data-pagina]`);t&&!t.disabled&&_(Number(t.dataset.pagina))}),i?.addEventListener(`click`,async e=>{let t=e.target.closest(`button[data-eliminar-id]`);if(t&&confirm(`¿Está seguro de que desea eliminar este accesorio?`)){t.disabled=!0;try{await n.delete(`/accesorios/${t.dataset.eliminarId}`);let{paginaActual:e,data:i}=r.getState();await _(i.length===1&&e>1?e-1:e)}catch(e){e?.status!==401&&(alert(e?.message??`No se pudo eliminar el accesorio.`),t.disabled=!1)}}}),i&&a&&_(1);