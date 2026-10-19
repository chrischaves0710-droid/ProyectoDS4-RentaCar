import{t as e}from"./ApiClient-B4CE_Sca.js";import{t}from"./AppState-BANczeEP.js";var n=new e(`/api`),r=new t({data:[],pagination:{},loading:!1,error:null,paginaActual:1,busqueda:``}),i=document.querySelector(`.contenedor-tabla`),a=document.querySelector(`.paginacion`),o=document.getElementById(`buscar-cliente`),s=document.querySelector(`.formulario-filtros`),c=document.querySelector(`.encabezado-listado p`),l=null,u=null,d=0;function f(e){return String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#39;`})[e])}function p(e){return[e.nombre1,e.nombre2,e.apellido1,e.apellido2].filter(e=>e!=null&&e!==``).join(` `)}function m(){d++,u?.abort(),u=null}function h(e){i.innerHTML=`
        <table class="tabla-vehiculos">

            <caption class="visualmente-oculto">
                Directorio de clientes
            </caption>

            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre completo</th>
                    <th>Año Nac.</th>
                    <th>Correo electrónico</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>${e.map(e=>{let t=f(e.id);return`
            <tr>
                <td>${f(e.cedula)}</td>
                <td>${f(p(e))}</td>
                <td>${f(e.anno_nacimiento)}</td>
                <td>${f(e.correo)}</td>
                <td>${f(e.telefono)}</td>

                <td>
                    <div class="acciones-tabla">

                        <button type="button"
                                class="accion-tabla"
                                data-consultar-id="${t}">
                            Consultar
                        </button>

                        <a class="accion-tabla"
                           href="/lab8/gerente/clientes/editar?id=${encodeURIComponent(String(e.id))}">
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
                Cargando clientes...
            </p>`,a.innerHTML=``,c&&(c.textContent=`Cargando...`);return}if(e.error){i.innerHTML=`
            <div style="padding:2rem;text-align:center">

                <p role="alert">
                    ${f(e.error)}
                </p>

                <button type="button"
                        id="reintentar-clientes"
                        class="boton boton-secundario">
                    Reintentar
                </button>

            </div>`,a.innerHTML=``,c&&(c.textContent=`No se pudo consultar el directorio`),document.getElementById(`reintentar-clientes`)?.addEventListener(`click`,()=>{_(e.paginaActual)});return}if(e.data.length===0){i.innerHTML=`
            <p style="padding:2rem;text-align:center">
                No se encontraron clientes.
            </p>`,a.innerHTML=``,c&&(c.textContent=`0 clientes encontrados`);return}h(e.data),g(e.pagination),c&&(c.textContent=`${e.pagination.total??e.data.length} clientes encontrados`)}});async function _(e=1){m();let t=d,i=new AbortController;u=i,r.setState({loading:!0,error:null,paginaActual:e});let a=new URLSearchParams({page:String(e),perPage:`10`}),o=r.getState().busqueda;o&&a.set(`q`,o);try{let o=await n.get(`/clientes?${a.toString()}`,{signal:i.signal});if(t!==d||i.signal.aborted||o?.aborted)return;r.setState({data:Array.isArray(o?.data)?o.data:[],pagination:o?.meta??{current_page:e,last_page:1},loading:!1,error:null})}catch(e){if(t!==d||i.signal.aborted||e?.name===`AbortError`)return;e?.status!==401&&r.setState({loading:!1,error:e?.message??`No se pudo consultar el directorio.`})}finally{u===i&&(u=null)}}o?.addEventListener(`input`,()=>{clearTimeout(l),m(),r.setState({busqueda:o.value.trim(),paginaActual:1,loading:!0,error:null}),l=setTimeout(()=>{_(1)},400)}),s?.addEventListener(`submit`,e=>{e.preventDefault(),clearTimeout(l),r.setState({busqueda:o?.value.trim()??``}),_(1)}),a?.addEventListener(`click`,e=>{let t=e.target.closest(`button[data-pagina]`);t&&!t.disabled&&_(Number(t.dataset.pagina))});async function v(e){try{let t=await n.get(`/clientes/${encodeURIComponent(e)}`),r=t.data??t,i=document.createElement(`dialog`);i.style.cssText=`max-width:450px;width:90%;padding:1.5rem;border:1px solid #cbd5e1;border-radius:8px;`;let a=document.createElement(`h2`);a.textContent=`Datos del cliente`,i.append(a);let o={Cédula:r.cedula,Nombre:p(r),"Año de nacimiento":r.anno_nacimiento,Correo:r.correo,Teléfono:r.telefono};for(let[e,t]of Object.entries(o)){let n=document.createElement(`p`);n.textContent=`${e}: ${t??`N/A`}`,i.append(n)}let s=document.createElement(`button`);s.type=`button`,s.className=`boton boton-secundario`,s.textContent=`Cerrar`,s.addEventListener(`click`,()=>{i.close()}),i.append(s),i.addEventListener(`close`,()=>{i.remove()}),document.body.append(i),i.showModal()}catch(e){e?.status!==401&&alert(e?.message??`No se pudo consultar el cliente.`)}}i?.addEventListener(`click`,async e=>{let t=e.target.closest(`button[data-consultar-id]`);if(t){await v(t.dataset.consultarId);return}let i=e.target.closest(`button[data-eliminar-id]`);if(i&&confirm(`¿Está seguro de que desea eliminar este cliente?`)){i.disabled=!0;try{await n.delete(`/clientes/${encodeURIComponent(i.dataset.eliminarId)}`);let{paginaActual:e,data:t}=r.getState();await _(t.length===1&&e>1?e-1:e)}catch(e){e?.status!==401&&(alert(e?.message??`No se pudo eliminar el cliente.`),i.disabled=!1)}}});var y=document.querySelector(`.boton-registrar`);y&&(y.href=`/lab8/gerente/clientes/registrar`);var b=document.getElementById(`mensaje-eliminacion-cliente`);b&&(b.textContent=`Los clientes con rentas asociadas no se pueden eliminar.`),i&&a&&_(1);