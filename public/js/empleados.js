// public/js/empleados.js
(function () {
  const cfg = window.EmpleadosConfig || {};
  const baseUrl = cfg.baseUrl || "";
  const csrfToken = cfg.csrfToken || "";
  const lookups = cfg.lookups || {};

  /* =================== Helpers =================== */

  function asArray(v) {
    return Array.isArray(v) ? v : [];
  }

  function handleCrudError(error, fallbackMessage) {
    let message = fallbackMessage || "Ocurrió un error inesperado.";

    if (error && error.errors) {
      const firstKey = Object.keys(error.errors)[0];
      if (firstKey && error.errors[firstKey][0]) {
        message = error.errors[firstKey][0];
      }
    } else if (error && error.message) {
      message = error.message;
    }

    Swal.showValidationMessage(message);
  }

  function showErrorAlert(message) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: message || "Ocurrió un error.",
      confirmButtonColor: "#4f46e5",
    });
  }

  function parseDate(value) {
    if (!value) return null;
    return new Date(value + "T00:00:00");
  }

  function cambiarEstado(id, nuevoEstado) {
      Swal.fire({
          title: '¿Confirmar cambio?',
          text: `El empleado pasará a estado: ${nuevoEstado}`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, cambiar',
          cancelButtonText: 'Cancelar'
      }).then(res => {
          if (!res.isConfirmed) return;

          fetch(`/empleados/${id}/estado`, {
              method: "PATCH",
              headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({ estado: nuevoEstado })
          })
          .then(r => r.json())
          .then(resp => {
              if (resp.ok) {
                  Swal.fire('Estado actualizado', '', 'success');
                  location.reload();
              }
          })
      });
  }

  /* =================== Filtros en tiempo real =================== */

  document.addEventListener("DOMContentLoaded", () => {
    const textInput = document.getElementById("empleado-search-text");
    const estadoSel = document.getElementById("empleado-filter-estado");
    const patronSel = document.getElementById("empleado-filter-patron");
    const sucursalSel = document.getElementById("empleado-filter-sucursal");
    const deptoSel = document.getElementById("empleado-filter-departamento");
    const supSel = document.getElementById("empleado-filter-supervisor");
    const ingresoDesde = document.getElementById("empleado-filter-ingreso-desde");
    const ingresoHasta = document.getElementById("empleado-filter-ingreso-hasta");
    const imssDesde = document.getElementById("empleado-filter-imss-desde");
    const imssHasta = document.getElementById("empleado-filter-imss-hasta");
    const clearBtn = document.getElementById("empleado-filter-clear");

    if (!textInput) return;

    const rows = () =>
      document.querySelectorAll("tbody[data-empleados] tr[data-empleado-row]");

    function applyFilters() {
      const text = (textInput.value || "").toLowerCase().trim();
      const estado = estadoSel.value || "";
      const patronId = patronSel.value || "";
      const sucursalId = sucursalSel.value || "";
      const deptoId = deptoSel.value || "";
      const supId = supSel.value || "";
      const fIngDesde = parseDate(ingresoDesde.value);
      const fIngHasta = parseDate(ingresoHasta.value);
      const fImssDesde = parseDate(imssDesde.value);
      const fImssHasta = parseDate(imssHasta.value);

      rows().forEach((row) => {
        const search = (row.dataset.search || "").toLowerCase();
        const rowEstado = row.dataset.estado || "";
        const rowPatron = row.dataset.patronId || "";
        const rowSucursal = row.dataset.sucursalId || "";
        const rowDepto = row.dataset.departamentoId || "";
        const rowSup = row.dataset.supervisorId || "";
        const fIng = parseDate(row.dataset.fechaIngreso || "");
        const fImss = parseDate(row.dataset.fechaAltaImss || "");

        let visible = true;

        if (text && !search.includes(text)) visible = false;
        if (estado && rowEstado !== estado) visible = false;
        if (patronId && rowPatron !== patronId) visible = false;
        if (sucursalId && rowSucursal !== sucursalId) visible = false;
        if (deptoId && rowDepto !== deptoId) visible = false;
        if (supId && rowSup !== supId) visible = false;

        if (fIngDesde && (!fIng || fIng < fIngDesde)) visible = false;
        if (fIngHasta && (!fIng || fIng > fIngHasta)) visible = false;
        if (fImssDesde && (!fImss || fImss < fImssDesde)) visible = false;
        if (fImssHasta && (!fImss || fImss > fImssHasta)) visible = false;

        row.style.display = visible ? "" : "none";
      });
    }

    [
      textInput,
      estadoSel,
      patronSel,
      sucursalSel,
      deptoSel,
      supSel,
      ingresoDesde,
      ingresoHasta,
      imssDesde,
      imssHasta,
    ].forEach((el) => {
      if (!el) return;
      const evt = el.tagName === "INPUT" ? "input" : "change";
      el.addEventListener(evt, applyFilters);
    });

    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        textInput.value = "";
        estadoSel.value = "";
        patronSel.value = "";
        sucursalSel.value = "";
        deptoSel.value = "";
        supSel.value = "";
        ingresoDesde.value = "";
        ingresoHasta.value = "";
        imssDesde.value = "";
        imssHasta.value = "";
        applyFilters();
      });
    }

    applyFilters();
  });

  /* =================== Construcción de selects lookup =================== */

  function buildLookupOptions(items, value, labelKey = "nombre") {
    const opts = items
      .map(
        (item) =>
          `<option value="${item.id}">${item[labelKey] || ""}</option>`
      )
      .join("");
    const placeholder =
      '<option value="">Selecciona una opción…</option>';
    return placeholder + opts;
  }

  /* =================== Plantilla HTML del formulario =================== */

  function empleadoFormHtml(initial) {
    const patrones = asArray(lookups.patrones);
    const sucursales = asArray(lookups.sucursales);
    const departamentos = asArray(lookups.departamentos);
    const supervisores = asArray(lookups.supervisores);

    const supervisorLabel = (s) =>
      [s.nombres, s.apellidoPaterno, s.apellidoMaterno].filter(Boolean).join(
        " "
      );

    const supervisorOptions =
      '<option value="">Sin supervisor</option>' +
      supervisores
        .map(
          (s) =>
            `<option value="${s.id}">${supervisorLabel(s)}</option>`
        )
        .join("");

    const patronesOptions = buildLookupOptions(patrones, "id", "nombre");
    const sucursalesOptions = buildLookupOptions(sucursales, "id", "nombre");
    const deptosOptions = buildLookupOptions(departamentos, "id", "nombre");

    const v = (field, def = "") =>
      typeof initial[field] !== "undefined" && initial[field] !== null
        ? initial[field]
        : def;

    return `
    <div class="empleados-form-wrapper">
      <!-- Sección 1 -->
      <div class="empleados-section empleados-section-open" data-section>
        <button type="button" class="empleados-section-header" data-section-toggle>
          <span class="empleados-section-title">Datos personales y laborales</span>
          <span class="empleados-section-subtitle">Nombre, estado y fechas principales</span>
          <span class="empleados-section-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="empleados-section-body">
          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Nombres <span class="empleados-required">*</span></label>
              <input id="emp-nombres" type="text" class="empleados-input"
                     placeholder="Tal como aparece en documentos oficiales"
                     value="${v("nombres")}">
            </div>
            <div>
              <label class="empleados-label">Apellido paterno <span class="empleados-required">*</span></label>
              <input id="emp-apellidoPaterno" type="text" class="empleados-input"
                     placeholder="Obligatorio"
                     value="${v("apellidoPaterno")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Apellido materno</label>
              <input id="emp-apellidoMaterno" type="text" class="empleados-input"
                     placeholder="Opcional"
                     value="${v("apellidoMaterno")}">
            </div>
            <div>
              <label class="empleados-label">Número trabajador <span class="empleados-required">*</span></label>
              <input id="emp-numeroTrabajador" type="text" class="empleados-input"
                     placeholder="Debe ser único dentro de la empresa"
                     value="${v("numero_trabajador")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Estado <span class="empleados-required">*</span></label>
              <select id="emp-estado" class="empleados-select">
                <option value="alta" ${v("estado", "alta") === "alta" ? "selected" : ""}>Alta</option>
                <option value="baja" ${v("estado") === "baja" ? "selected" : ""}>Baja</option>
              </select>
            </div>
            <div>
              <label class="empleados-label">Fecha ingreso <span class="empleados-required">*</span></label>
              <input id="emp-fechaIngreso" type="date" class="empleados-input"
                     value="${v("fecha_ingreso", "")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Fecha baja</label>
              <input id="emp-fechaBaja" type="date" class="empleados-input"
                     value="${v("fecha_baja", "")}">
              <p class="empleados-help">Sólo si el empleado ya está de baja.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección 2 -->
      <div class="empleados-section" data-section>
        <button type="button" class="empleados-section-header" data-section-toggle>
          <span class="empleados-section-title">Asignación (patrón, sucursal, departamento, supervisor)</span>
          <span class="empleados-section-subtitle">Vincula al empleado con su estructura organizacional</span>
          <span class="empleados-section-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="empleados-section-body">
          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Patrón / empresa <span class="empleados-required">*</span></label>
              <select id="emp-patron" class="empleados-select">
                ${patronesOptions}
              </select>
            </div>
            <div>
              <label class="empleados-label">Sucursal <span class="empleados-required">*</span></label>
              <select id="emp-sucursal" class="empleados-select">
                ${sucursalesOptions}
              </select>
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Departamento <span class="empleados-required">*</span></label>
              <select id="emp-departamento" class="empleados-select">
                ${deptosOptions}
              </select>
            </div>
            <div>
              <label class="empleados-label">Supervisor</label>
              <select id="emp-supervisor" class="empleados-select">
                ${supervisorOptions}
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección 3 -->
      <div class="empleados-section" data-section>
        <button type="button" class="empleados-section-header" data-section-toggle>
          <span class="empleados-section-title">Datos IMSS</span>
          <span class="empleados-section-subtitle">Información para seguridad social</span>
          <span class="empleados-section-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="empleados-section-body">
          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Número IMSS <span class="empleados-required">*</span></label>
              <input id="emp-numeroImss" type="text" class="empleados-input"
                     placeholder="Número de seguridad social"
                     value="${v("numero_imss")}">
            </div>
            <div>
              <label class="empleados-label">Registro patronal <span class="empleados-required">*</span></label>
              <input id="emp-registroPatronal" type="text" class="empleados-input"
                     placeholder="Registro patronal asociado"
                     value="${v("registro_patronal")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Código postal</label>
              <input id="emp-codigoPostal" type="text" class="empleados-input"
                     placeholder="CP asociado al registro"
                     value="${v("codigo_postal")}">
            </div>
            <div>
              <label class="empleados-label">Fecha alta IMSS</label>
              <input id="emp-fechaAltaImss" type="date" class="empleados-input"
                     value="${v("fecha_alta_imss", "")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">CURP <span class="empleados-required">*</span></label>
              <input id="emp-curp" type="text" class="empleados-input"
                     placeholder="18 caracteres"
                     value="${v("curp")}">
            </div>
            <div>
              <label class="empleados-label">RFC <span class="empleados-required">*</span></label>
              <input id="emp-rfc" type="text" class="empleados-input"
                     placeholder="Con homoclave"
                     value="${v("rfc")}">
            </div>
          </div>
        </div>
      </div>

      <!-- Sección 4 -->
      <div class="empleados-section" data-section>
        <button type="button" class="empleados-section-header" data-section-toggle>
          <span class="empleados-section-title">Datos bancarios y sueldos</span>
          <span class="empleados-section-subtitle">Información opcional para pagos y facturación</span>
          <span class="empleados-section-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="empleados-section-body">
          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Banco</label>
              <input id="emp-banco" type="text" class="empleados-input"
                     placeholder="Nombre del banco"
                     value="${v("banco")}">
            </div>
            <div>
              <label class="empleados-label">Cuenta bancaria</label>
              <input id="emp-cuentaBancaria" type="text" class="empleados-input"
                     placeholder="Número de cuenta"
                     value="${v("cuenta_bancaria")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Tarjeta</label>
              <input id="emp-tarjeta" type="text" class="empleados-input"
                     placeholder="Número de tarjeta"
                     value="${v("tarjeta")}">
            </div>
            <div>
              <label class="empleados-label">CLABE interbancaria</label>
              <input id="emp-clabe" type="text" class="empleados-input"
                     placeholder="18 dígitos"
                     value="${v("clabe_interbancaria")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Sueldo diario bruto</label>
              <input id="emp-sueldoBruto" type="number" step="0.01" class="empleados-input"
                     placeholder="0.00"
                     value="${v("sueldo_diario_bruto", "")}">
            </div>
            <div>
              <label class="empleados-label">Sueldo diario neto</label>
              <input id="emp-sueldoNeto" type="number" step="0.01" class="empleados-input"
                     placeholder="0.00"
                     value="${v("sueldo_diario_neto", "")}">
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Salario diario IMSS</label>
              <input id="emp-salarioImss" type="number" step="0.01" class="empleados-input"
                     placeholder="0.00"
                     value="${v("salario_diario_imss", "")}">
            </div>
            <div>
              <label class="empleados-label">SDI</label>
              <input id="emp-sdi" type="number" step="0.01" class="empleados-input"
                     placeholder="0.00"
                     value="${v("sdi", "")}">
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
  }

  function initSectionToggles() {
    document
      .querySelectorAll(".empleados-section-header")
      .forEach((btn) => {
        btn.addEventListener("click", () => {
          const section = btn.closest("[data-section]");
          if (!section) return;
          section.classList.toggle("empleados-section-open");
        });
      });
  }

  function collectEmpleadoFormValues(id) {
    const getVal = (sel) =>
      (document.getElementById(sel) || { value: "" }).value.trim();

    const payload = {
      id: id || null,
      nombres: getVal("emp-nombres"),
      apellidoPaterno: getVal("emp-apellidoPaterno"),
      apellidoMaterno: getVal("emp-apellidoMaterno") || null,
      numero_trabajador: getVal("emp-numeroTrabajador"),
      estado: getVal("emp-estado") || "alta",
      fecha_ingreso: getVal("emp-fechaIngreso"),
      fecha_baja: getVal("emp-fechaBaja") || null,
      patron_id: getVal("emp-patron"),
      sucursal_id: getVal("emp-sucursal"),
      departamento_id: getVal("emp-departamento"),
      supervisor_id: getVal("emp-supervisor") || null,
      numero_imss: getVal("emp-numeroImss"),
      registro_patronal: getVal("emp-registroPatronal"),
      codigo_postal: getVal("emp-codigoPostal") || null,
      fecha_alta_imss: getVal("emp-fechaAltaImss") || null,
      curp: getVal("emp-curp"),
      rfc: getVal("emp-rfc"),
      banco: getVal("emp-banco") || null,
      cuenta_bancaria: getVal("emp-cuentaBancaria") || null,
      tarjeta: getVal("emp-tarjeta") || null,
      clabe_interbancaria: getVal("emp-clabe") || null,
      sueldo_diario_bruto: getVal("emp-sueldoBruto") || null,
      sueldo_diario_neto: getVal("emp-sueldoNeto") || null,
      salario_diario_imss: getVal("emp-salarioImss") || null,
      sdi: getVal("emp-sdi") || null,
    };

    // Validaciones rápidas de front
    if (!payload.nombres) {
      Swal.showValidationMessage("El campo Nombres es obligatorio.");
      return null;
    }
    if (!payload.apellidoPaterno) {
      Swal.showValidationMessage("El Apellido paterno es obligatorio.");
      return null;
    }
    if (!payload.numero_trabajador) {
      Swal.showValidationMessage(
        "El Número de trabajador es obligatorio."
      );
      return null;
    }
    if (!payload.fecha_ingreso) {
      Swal.showValidationMessage("La fecha de ingreso es obligatoria.");
      return null;
    }
    if (!payload.patron_id) {
      Swal.showValidationMessage("Debes seleccionar un patrón / empresa.");
      return null;
    }
    if (!payload.sucursal_id) {
      Swal.showValidationMessage("Debes seleccionar una sucursal.");
      return null;
    }
    if (!payload.departamento_id) {
      Swal.showValidationMessage("Debes seleccionar un departamento.");
      return null;
    }
    if (!payload.numero_imss) {
      Swal.showValidationMessage("El número IMSS es obligatorio.");
      return null;
    }
    if (!payload.registro_patronal) {
      Swal.showValidationMessage("El registro patronal es obligatorio.");
      return null;
    }
    if (!payload.curp) {
      Swal.showValidationMessage("La CURP es obligatoria.");
      return null;
    }
    if (!payload.rfc) {
      Swal.showValidationMessage("El RFC es obligatorio.");
      return null;
    }

    return payload;
  }

  async function sendEmpleado(method, url, payload) {
    const response = await fetch(url, {
      method,
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const data = await response.json().catch(() => ({}));
      throw data;
    }

    return response.json();
  }

  /* =================== Crear =================== */

  window.openCreateEmpleadoModal = function () {
    Swal.fire({
      title: "Nuevo empleado",
      html: empleadoFormHtml({}),
      width: "90%",
      maxWidth: 1100,
      showCloseButton: true,
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      focusConfirm: false,
      customClass: {
        popup: "empleados-swal-popup",
        confirmButton: "empleados-swal-confirm",
        cancelButton: "empleados-swal-cancel",
      },
      didOpen: () => {
        initSectionToggles();
      },
      preConfirm: () => {
        const payload = collectEmpleadoFormValues(null);
        if (!payload) return false;

        Swal.showLoading();

        return sendEmpleado("POST", `${baseUrl}/empleados`, payload)
          .then((data) => {
            return data;
          })
          .catch((error) => {
            handleCrudError(error, "Error al registrar el empleado.");
            return false;
          });
      },
    }).then((result) => {
      if (!result.isConfirmed || !result.value) return;
      Swal.fire({
        icon: "success",
        title: "Guardado",
        text: "Empleado registrado correctamente.",
        confirmButtonColor: "#4f46e5",
      }).then(() => window.location.reload());
    });
  };

  /* =================== Editar =================== */

  window.openEditEmpleadoModal = function (btn) {
    const dataset = btn.dataset || {};
    const initial = {
      id: dataset.id,
      nombres: dataset.nombres || "",
      apellidoPaterno: dataset.apellidoPaterno || "",
      apellidoMaterno: dataset.apellidoMaterno || "",
      numero_trabajador: dataset.numeroTrabajador || "",
      estado: dataset.estado || "alta",
      fecha_ingreso: dataset.fechaIngreso || "",
      fecha_baja: dataset.fechaBaja || "",
      patron_id: dataset.patronId || "",
      sucursal_id: dataset.sucursalId || "",
      departamento_id: dataset.departamentoId || "",
      supervisor_id: dataset.supervisorId || "",
      numero_imss: dataset.numeroImss || "",
      registro_patronal: dataset.registroPatronal || "",
      codigo_postal: dataset.codigoPostal || "",
      fecha_alta_imss: dataset.fechaAltaImss || "",
      curp: dataset.curp || "",
      rfc: dataset.rfc || "",
      banco: dataset.banco || "",
      cuenta_bancaria: dataset.cuentaBancaria || "",
      tarjeta: dataset.tarjeta || "",
      clabe_interbancaria: dataset.clabe || "",
      sueldo_diario_bruto: dataset.sueldoBruto || "",
      sueldo_diario_neto: dataset.sueldoNeto || "",
      salario_diario_imss: dataset.salarioImss || "",
      sdi: dataset.sdi || "",
    };

    Swal.fire({
      title: "Editar empleado",
      html: empleadoFormHtml(initial),
      width: "90%",
      maxWidth: 1100,
      showCloseButton: true,
      showCancelButton: true,
      confirmButtonText: "Actualizar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      focusConfirm: false,
      customClass: {
        popup: "empleados-swal-popup",
        confirmButton: "empleados-swal-confirm",
        cancelButton: "empleados-swal-cancel",
      },
      didOpen: () => {
        initSectionToggles();

        // Seleccionar valores de combos
        const setVal = (id, val) => {
          const el = document.getElementById(id);
          if (el && val) el.value = val;
        };
        setVal("emp-patron", initial.patron_id);
        setVal("emp-sucursal", initial.sucursal_id);
        setVal("emp-departamento", initial.departamento_id);
        setVal("emp-supervisor", initial.supervisor_id);
      },
      preConfirm: () => {
        const payload = collectEmpleadoFormValues(initial.id);
        if (!payload) return false;

        Swal.showLoading();

        return sendEmpleado(
          "PUT",
          `${baseUrl}/empleados/${initial.id}`,
          payload
        )
          .then((data) => data)
          .catch((error) => {
            handleCrudError(error, "Error al actualizar el empleado.");
            return false;
          });
      },
    }).then((result) => {
      if (!result.isConfirmed || !result.value) return;
      Swal.fire({
        icon: "success",
        title: "Actualizado",
        text: "Empleado actualizado correctamente.",
        confirmButtonColor: "#4f46e5",
      }).then(() => window.location.reload());
    });
  };

  /* =================== Ver (solo lectura) =================== */

  window.openShowEmpleadoModal = function (btn) {
    const d = btn.dataset || {};
    const supervisor =
      d.supervisorNombre && d.supervisorNombre.trim().length
        ? d.supervisorNombre
        : "—";

    const html = `
      <div class="empleados-form-wrapper empleados-view-wrapper">
        <div class="empleados-section empleados-section-open" data-section>
          <div class="empleados-section-header empleados-section-header-static">
            <span class="empleados-section-title">Datos generales</span>
            <span class="empleados-section-subtitle">Vista rápida del empleado</span>
          </div>
          <div class="empleados-section-body">
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Nombre completo</p>
                <p class="empleados-view-value">${d.nombres || ""} ${d.apellidoPaterno || ""} ${d.apellidoMaterno || ""}</p>
              </div>
              <div>
                <p class="empleados-view-label">Número trabajador</p>
                <p class="empleados-view-value">${d.numeroTrabajador || "—"}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Estado</p>
                <p class="empleados-view-value">${(d.estado || "").toUpperCase()}</p>
              </div>
              <div>
                <p class="empleados-view-label">Fechas</p>
                <p class="empleados-view-value">
                  Ingreso: ${d.fechaIngreso || "—"} · Baja: ${d.fechaBaja || "—"}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="empleados-section empleados-section-open" data-section>
          <div class="empleados-section-header empleados-section-header-static">
            <span class="empleados-section-title">Relación laboral</span>
          </div>
          <div class="empleados-section-body">
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Patrón / empresa</p>
                <p class="empleados-view-value">${d.patronNombre || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Sucursal</p>
                <p class="empleados-view-value">${d.sucursalNombre || "—"}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Departamento</p>
                <p class="empleados-view-value">${d.departamentoNombre || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Supervisor</p>
                <p class="empleados-view-value">${supervisor}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="empleados-section empleados-section-open" data-section>
          <div class="empleados-section-header empleados-section-header-static">
            <span class="empleados-section-title">IMSS y fiscales</span>
          </div>
          <div class="empleados-section-body">
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Número IMSS</p>
                <p class="empleados-view-value">${d.numeroImss || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Registro patronal</p>
                <p class="empleados-view-value">${d.registroPatronal || "—"}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">CURP</p>
                <p class="empleados-view-value">${d.curp || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">RFC</p>
                <p class="empleados-view-value">${d.rfc || "—"}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Alta IMSS</p>
                <p class="empleados-view-value">${d.fechaAltaImss || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Código postal</p>
                <p class="empleados-view-value">${d.codigoPostal || "—"}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    Swal.fire({
      title: "Detalle de empleado",
      html,
      width: "80%",
      maxWidth: 900,
      showCloseButton: true,
      showConfirmButton: true,
      confirmButtonText: "Cerrar",
      customClass: {
        popup: "empleados-swal-popup",
        confirmButton: "empleados-swal-cancel",
      },
    });
  };

  /* =================== Cambiar estado rápido =================== */

  window.openToggleEstadoEmpleado = function (id, currentEstado) {
    Swal.fire({
      title: "Cambiar estado",
      text: "Puedes activar o dar de baja al empleado rápidamente.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      showCloseButton: true,
      html: `
        <div class="mt-3">
          <label class="empleados-label mb-2 block">Estado</label>
          <select id="emp-toggle-estado" class="empleados-select w-full">
            <option value="alta" ${currentEstado === "alta" ? "selected" : ""}>Alta</option>
            <option value="baja" ${currentEstado === "baja" ? "selected" : ""}>Baja</option>
          </select>
        </div>
      `,
      preConfirm: () => {
        const sel = document.getElementById("emp-toggle-estado");
        if (!sel) return false;
        const nuevo = sel.value;
        if (!nuevo) {
          Swal.showValidationMessage("Selecciona un estado.");
          return false;
        }

        Swal.showLoading();

        return fetch(`${baseUrl}/empleados/${id}/estado`, {
          method: "PATCH",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
          },
          body: JSON.stringify({ estado: nuevo }),
        })
          .then((res) => {
            if (!res.ok) return res.json().then((d) => Promise.reject(d));
            return res.json();
          })
          .catch((err) => {
            handleCrudError(err, "No se pudo actualizar el estado.");
            return false;
          });
      },
    }).then((result) => {
      if (!result.isConfirmed || !result.value) return;
      Swal.fire({
        icon: "success",
        title: "Estado actualizado",
        confirmButtonColor: "#4f46e5",
      }).then(() => window.location.reload());
    });
  };

  /* =================== Eliminar =================== */

  window.confirmDeleteEmpleado = function (id) {
    Swal.fire({
      title: "¿Eliminar empleado?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#dc2626",
      reverseButtons: true,
      showCloseButton: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      Swal.fire({
        title: "Eliminando",
        text: "Por favor espera…",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      fetch(`${baseUrl}/empleados/${id}`, {
        method: "DELETE",
        headers: {
          "X-CSRF-TOKEN": csrfToken,
          Accept: "application/json",
        },
      })
        .then((res) => {
          if (!res.ok) return res.json().then((d) => Promise.reject(d));
          return res.json();
        })
        .then(() => {
          Swal.fire({
            icon: "success",
            title: "Eliminado",
            text: "Empleado eliminado correctamente.",
            confirmButtonColor: "#4f46e5",
          }).then(() => window.location.reload());
        })
        .catch((err) => {
          Swal.close();
          showErrorAlert(
            (err && err.message) ||
              "Ocurrió un error al eliminar el empleado."
          );
        });
    });
  };
})();
