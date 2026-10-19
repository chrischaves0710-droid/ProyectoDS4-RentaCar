import{t as e}from"./ApiClient-B4CE_Sca.js";import{t}from"./AppState-BANczeEP.js";var n=new e(`/api`),r=new t({data:[],pagination:{},loading:!1,error:null,paginaActual:1,busqueda:``,categoriaId:``,estadoId:``}),i=document.querySelector(`.contenedor-tabla`),a=document.querySelector(`.paginacion`),o=document.querySelector(`.formulario-filtros`),s=document.getElementById(`buscar-gestion`),c=document.getElementById(`categoria-gestion`),l=document.getElementById(`estado-gestion`),u=document.querySelector(`.encabezado-listado p`),d=location.pathname.startsWith(`/lab8/admin/`)?`/lab8/admin/vehiculos/editar`:`/lab8/gerente/vehiculos/editar`,f=null,p=null,m=0;function h(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#39;`})[e])}function g(e){return typeof e==`string`?e:e?.nombre??`Sin especificar`}r.subscribe(e=>{if(i&&a){if(e.loading){i.innerHTML=`
            <p role="status"
               style="padding:2rem;text-align:center">
                Cargando vehículos...
            </p>`,a.innerHTML=``;return}if(e.error){i.innerHTML=`
            <div style="padding:2rem;text-align:center">
                <p role="alert">
                    ${h(e.error)}
                </p>

                <button type="button"
                        id="reintentar-vehiculos"
                        class="boton boton-secundario">
                    Reintentar
                </button>
            </div>`,a.innerHTML=``,document.getElementById(`reintentar-vehiculos`)?.addEventListener(`click`,()=>{y(e.paginaActual)});return}if(e.data.length===0){i.innerHTML=`
            <p style="padding:2rem;text-align:center">
                No se encontraron vehículos.
            </p>`,a.innerHTML=``,u&&(u.textContent=`0 resultados`);return}_(e.data),v(e.pagination)}});function _(e){i.innerHTML=`
        <table class="tabla-vehiculos">

            <caption class="visualmente-oculto">
                Vehículos registrados
            </caption>

            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Km</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>${e.map(e=>{let t=g(e.estado),n=g(e.categoria),r=t.toLowerCase()===`disponible`,i=encodeURIComponent(String(e.id));return`
            <tr>
                <td>
                    ${h(e.placa??e.matricula)}
                </td>

                <td>${h(e.marca)}</td>

                <td>${h(e.modelo)}</td>

                <td>
                    ${h(e.anno??e.anio_fabricacion)}
                </td>

                <td>${h(e.kilometraje)}</td>

                <td>${h(n)}</td>

                <td>
                    <span class="estado ${r?`estado-disponible`:`estado-inactivo`}">

                        ${h(t)}
                    </span>
                </td>

                <td>
                    <div class="acciones-tabla">

                        <a class="accion-tabla"
                           href="${d}?id=${i}">
                            Editar
                        </a>

                        <button type="button"
                                class="accion-tabla accion-eliminar"
                                data-eliminar-id="${i}">
                            Eliminar
                        </button>

                    </div>
                </td>
            </tr>`}).join(``)}</tbody>

        </table>`}function v(e){let t=Number(e.current_page??1),n=Number(e.last_page??e.total_pages??1);a.innerHTML=`
        <button type="button"
                class="boton boton-secundario"
                id="vehiculos-anterior"
                ${t<=1?`disabled`:``}>
            Anterior
        </button>

        <span class="pagina-actual" aria-current="page">
            ${t} de ${n}
        </span>

        <button type="button"
                class="boton boton-secundario"
                id="vehiculos-siguiente"
                ${t>=n?`disabled`:``}>
            Siguiente
        </button>`,document.getElementById(`vehiculos-anterior`)?.addEventListener(`click`,()=>{t>1&&y(t-1)}),document.getElementById(`vehiculos-siguiente`)?.addEventListener(`click`,()=>{t<n&&y(t+1)}),u&&(u.textContent=`${e.to??r.state.data.length} de ${e.total??r.state.data.length} resultados`)}async function y(e=1){let t=++m;p?.abort();let i=new AbortController;p=i,r.setState({loading:!0,error:null,paginaActual:e});let a=r.getState(),o=new URLSearchParams({page:String(e),limit:`10`});a.busqueda&&o.set(`q`,a.busqueda),a.categoriaId&&o.set(`categoria_id`,a.categoriaId),a.estadoId&&o.set(`estado_id`,a.estadoId);try{let a=await n.get(`/vehiculos?${o}`,{signal:i.signal});if(t!==m||i.signal.aborted||a?.aborted)return;r.setState({data:Array.isArray(a.data)?a.data:[],pagination:a.meta??{current_page:e,last_page:1},loading:!1,error:null})}catch(e){if(t!==m||i.signal.aborted||e?.name===`AbortError`)return;e?.status!==401&&r.setState({loading:!1,error:e?.message??`No se pudo consultar el listado.`})}finally{p===i&&(p=null)}}s?.addEventListener(`input`,()=>{clearTimeout(f),p?.abort(),m++,r.setState({loading:!0,error:null,busqueda:s.value.trim()}),f=setTimeout(()=>{y(1)},400)}),o?.addEventListener(`submit`,e=>{e.preventDefault(),clearTimeout(f),r.setState({busqueda:s?.value.trim()??``,categoriaId:c?.value??``,estadoId:l?.value??``}),y(1)});async function b(e,t,r){if(!e)return;e.replaceChildren(new Option(r,``));let i=1,a=1;do{let r=await n.get(`${t}?page=${i}&per_page=50`);for(let t of r.data??[])e.add(new Option(t.nombre,String(t.id)));a=Number(r.meta?.last_page??r.meta?.total_pages??1),i++}while(i<=a)}async function x(){try{await Promise.all([b(c,`/categorias`,`Todas`),b(l,`/estados`,`Todos`)])}catch(e){if(e?.status!==401&&o){let e=document.createElement(`p`);e.setAttribute(`role`,`alert`),e.textContent=`No se pudieron cargar las opciones de filtrado.`,o.append(e)}}}i?.addEventListener(`click`,async e=>{let t=e.target.closest(`button[data-eliminar-id]`);if(t&&confirm(`¿Eliminar este vehículo?`)){t.disabled=!0;try{await n.delete(`/vehiculos/${t.dataset.eliminarId}`);let e=r.getState().paginaActual,i=r.getState().data.length;await y(i===1&&e>1?e-1:e)}catch(e){e?.status!==401&&(alert(e?.message??`No se pudo eliminar el vehículo.`),t.disabled=!1)}}}),y(1),x();