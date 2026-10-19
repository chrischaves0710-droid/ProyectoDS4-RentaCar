import{t as e}from"./ApiClient-B4CE_Sca.js";import{t}from"./AppState-BANczeEP.js";var n=document.querySelector(`.contenedor-tabla`),r=document.querySelector(`.paginacion`);if(n&&r){let i=new e(`/api`),a=new t({data:[],pagination:{},loading:!1,error:null,paginaActual:1,busqueda:``}),o=document.getElementById(`buscar-renta`),s=document.querySelector(`.formulario-filtros`),c=document.getElementById(`estado-renta`),l=document.querySelector(`.encabezado-listado p`),u=location.pathname.startsWith(`/lab8/gestor/`)?`/lab8/gestor/rentas`:`/lab8/gerente/rentas`,d=null,f=null,p=0;c&&(c.value=``,c.disabled=!0,c.title=`El filtro necesita un estado de renta en el modelo y la API.`);function m(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#39;`})[e])}function h(){p++,f?.abort(),f=null}function g(e){n.innerHTML=`
            <table class="tabla-vehiculos">

                <caption class="visualmente-oculto">
                    Contratos de alquiler
                </caption>

                <thead>
                    <tr>
                        <th>N° Contrato</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Período</th>
                        <th>Total Estimado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>${e.map(e=>{let t=encodeURIComponent(String(e.id)),n=e.cliente?.nombre_completo??`Sin cliente`,r=[e.vehiculo?.marca,e.vehiculo?.modelo].filter(Boolean).join(` `)||`Sin vehículo`,i=Number(e.monto_total),a=Number.isFinite(i)?i.toFixed(2):`0.00`,o=typeof e.estado==`string`?e.estado:e.estado?.nombre??`No registrado`;return`
                <tr>
                    <td>
                        <strong>RN-${m(e.id)}</strong>
                    </td>

                    <td>${m(n)}</td>

                    <td>${m(r)}</td>

                    <td>
                        ${m(e.fecha_inicio)}
                        -
                        ${m(e.fecha_fin)}
                    </td>

                    <td>$${a}</td>

                    <td>${m(o)}</td>

                    <td>
                        <div class="acciones-tabla">

                            <a class="accion-tabla"
                               href="${u}/consultar?id=${t}">
                                Consultar
                            </a>

                            <a class="accion-tabla"
                               href="${u}/editar?id=${t}">
                                Editar
                            </a>

                        </div>
                    </td>
                </tr>`}).join(``)}</tbody>

            </table>`}function _(e){let t=Math.max(1,Number(e.current_page)||1),n=Math.max(1,Number(e.last_page)||1);r.innerHTML=`
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
            </button>`}a.subscribe(e=>{if(e.loading){n.innerHTML=`
                <p role="status"
                   style="padding:2rem;text-align:center">
                    Cargando rentas...
                </p>`,r.innerHTML=``,l&&(l.textContent=`Cargando...`);return}if(e.error){n.innerHTML=`
                <div style="padding:2rem;text-align:center">

                    <p role="alert">
                        ${m(e.error)}
                    </p>

                    <button type="button"
                            id="reintentar-rentas"
                            class="boton boton-secundario">
                        Reintentar
                    </button>

                </div>`,r.innerHTML=``,l&&(l.textContent=`Error al consultar las rentas`),document.getElementById(`reintentar-rentas`)?.addEventListener(`click`,()=>{v(e.paginaActual)});return}if(e.data.length===0){n.innerHTML=`
                <p style="padding:2rem;text-align:center">
                    No se encontraron rentas.
                </p>`,r.innerHTML=``,l&&(l.textContent=`0 rentas encontradas`);return}g(e.data),_(e.pagination),l&&(l.textContent=`${e.pagination.total??e.data.length} rentas encontradas`)});async function v(e=1){h();let t=p,n=new AbortController;f=n,a.setState({loading:!0,error:null,paginaActual:e});let r=new URLSearchParams({page:String(e)}),o=a.getState().busqueda;o&&r.set(`q`,o);try{let o=await i.get(`/rentas?${r.toString()}`,{signal:n.signal});if(t!==p||n.signal.aborted||o?.aborted)return;if(!Array.isArray(o?.data))throw Error(`La API devolvió un listado con formato inesperado.`);a.setState({data:o.data,pagination:o.meta??{current_page:e,last_page:1},loading:!1,error:null})}catch(e){if(t!==p||n.signal.aborted||e?.name===`AbortError`)return;e?.status!==401&&a.setState({loading:!1,error:e?.message??`No se pudieron cargar las rentas.`})}finally{f===n&&(f=null)}}o?.addEventListener(`input`,()=>{clearTimeout(d),h(),a.setState({busqueda:o.value.trim(),paginaActual:1,loading:!0,error:null}),d=setTimeout(()=>{v(1)},400)}),s?.addEventListener(`submit`,e=>{e.preventDefault(),clearTimeout(d),a.setState({busqueda:o?.value.trim()??``}),v(1)}),r.addEventListener(`click`,e=>{let t=e.target.closest(`button[data-pagina]`);t&&!t.disabled&&v(Number(t.dataset.pagina))}),v(1)}