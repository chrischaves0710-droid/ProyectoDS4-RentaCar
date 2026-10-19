import{t as e}from"./ApiClient-B4CE_Sca.js";import{t}from"./AppState-BANczeEP.js";var n=new e(`/api`),r=new t({data:[],pagination:{},loading:!1,error:null,paginaActual:1,busqueda:``}),i=document.querySelector(`.contenedor-tabla`),a=document.querySelector(`.paginacion`),o=document.getElementById(`buscar-estado`),s=document.querySelector(`.formulario-filtros`),c=document.querySelector(`.encabezado-listado p`),l=window.location.pathname.startsWith(`/lab8/admin/`)?`/lab8/admin/estados/editar`:`/lab8/gerente/estados/editar`,u=null,d=null,f=0;function p(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#39;`})[e])}function m(){f++,d?.abort(),d=null}function h(e){i.innerHTML=`
        <table class="tabla-vehiculos">
            <caption class="visualmente-oculto">
                Estados registrados en el sistema
            </caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nombre del estado</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>${e.map(e=>{let t=String(e.id),n=String(e.nombre??`Sin nombre`),r=n.toLowerCase().includes(`disponible`)?`estado-disponible`:`estado-inactivo`;return`
            <tr>
                <td>
                    <strong>${p(t)}</strong>
                </td>
                <td>
                    <span class="estado ${r}">
                        ${p(n)}
                    </span>
                </td>
                <td>
                    <div class="acciones-tabla">
                        <a class="accion-tabla"
                           href="${l}?id=${encodeURIComponent(t)}">
                            Editar
                        </a>

                        <button type="button"
                                class="accion-tabla accion-eliminar"
                                data-eliminar-id="${p(t)}">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>`}).join(``)}</tbody>
        </table>`}function g(e){let t=Math.max(1,Number(e.current_page)||1),n=Math.max(1,Number(e.total_pages??e.last_page)||1);a.innerHTML=`
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
                Cargando estados...
            </p>`,a.innerHTML=``,c&&(c.textContent=`Cargando...`);return}if(e.error){i.innerHTML=`
            <div style="padding:2rem;text-align:center">
                <p role="alert">
                    ${p(e.error)}
                </p>

                <button type="button"
                        id="reintentar-estados"
                        class="boton boton-secundario">
                    Reintentar
                </button>
            </div>`,a.innerHTML=``,c&&(c.textContent=`No se pudo consultar el listado`),document.getElementById(`reintentar-estados`)?.addEventListener(`click`,()=>{_(e.paginaActual)});return}if(e.data.length===0){i.innerHTML=`
            <p style="padding:2rem;text-align:center">
                No se encontraron estados.
            </p>`,a.innerHTML=``,c&&(c.textContent=`0 estados encontrados`);return}h(e.data),g(e.pagination),c&&(c.textContent=`${e.pagination.total_records??e.data.length} estados encontrados`)}});async function _(e=1){m();let t=f,i=new AbortController;d=i,r.setState({loading:!0,error:null,paginaActual:e});let a=new URLSearchParams({page:String(e),per_page:`10`}),o=r.getState().busqueda;o&&a.set(`nombre`,o);try{let o=await n.get(`/estados?${a.toString()}`,{signal:i.signal});if(t!==f||i.signal.aborted||o?.aborted)return;r.setState({data:Array.isArray(o?.data)?o.data:[],pagination:o?.meta??{current_page:e,total_pages:1},loading:!1,error:null})}catch(e){if(t!==f||i.signal.aborted||e?.name===`AbortError`)return;e?.status!==401&&r.setState({loading:!1,error:e?.message??`No se pudieron cargar los estados.`})}finally{d===i&&(d=null)}}o?.addEventListener(`input`,()=>{clearTimeout(u),m(),r.setState({busqueda:o.value.trim(),paginaActual:1,loading:!0,error:null}),u=setTimeout(()=>{_(1)},400)}),s?.addEventListener(`submit`,e=>{e.preventDefault(),clearTimeout(u),r.setState({busqueda:o?.value.trim()??``}),_(1)}),a?.addEventListener(`click`,e=>{let t=e.target.closest(`button[data-pagina]`);t&&!t.disabled&&_(Number(t.dataset.pagina))}),i?.addEventListener(`click`,async e=>{let t=e.target.closest(`button[data-eliminar-id]`);if(t&&confirm(`¿Está seguro de que desea eliminar este estado?`)){t.disabled=!0;try{await n.delete(`/estados/${encodeURIComponent(t.dataset.eliminarId)}`);let{paginaActual:e,data:i}=r.getState();await _(i.length===1&&e>1?e-1:e)}catch(e){e?.status!==401&&(alert(e?.message??`No se pudo eliminar el estado.`),t.disabled=!1)}}}),i&&a&&_(1);