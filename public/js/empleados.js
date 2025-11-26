// public/js/empleados.js
(function () {
  const cfg = window.EmpleadosConfig || {};
  const baseUrl = cfg.baseUrl || "";
  const csrfToken = cfg.csrfToken || "";
  const lookups = cfg.lookups || {};
  const canManage = !!cfg.canManage;

  // Lista de colores permitidos (solo los de la tabla de Excel)
  const EMPLOYEE_COLORS = [
    { value: "#ff66ff", label: "Incapacidad" },                         // rosa
    { value: "#ff0000", label: "Baja" },                                // rojo
    { value: "#00b0f0", label: "Modificar - INMAG - JSIG" },            // azul claro
    { value: "#00ffff", label: "Alta" },                                // turquesa
    { value: "#ffff00", label: "Correcciones" },                        // amarillo
    { value: "#0070c0", label: "No se pueden cambiar razón social" },   // azul
    { value: "#808080", label: "Pendiente respuesta para baja" },       // gris
  ];

  // Lista de estados de la republica mexicana
  const MEX_STATES = [
    "Aguascalientes",
    "Baja California",
    "Baja California Sur",
    "Campeche",
    "Chiapas",
    "Chihuahua",
    "Ciudad de México",
    "Coahuila",
    "Colima",
    "Durango",
    "Estado de México",
    "Guanajuato",
    "Guerrero",
    "Hidalgo",
    "Jalisco",
    "Michoacán",
    "Morelos",
    "Nayarit",
    "Nuevo León",
    "Oaxaca",
    "Puebla",
    "Querétaro",
    "Quintana Roo",
    "San Luis Potosí",
    "Sinaloa",
    "Sonora",
    "Tabasco",
    "Tamaulipas",
    "Tlaxcala",
    "Veracruz",
    "Yucatán",
    "Zacatecas",
  ];

  /* =================== Helpers =================== */

  function asArray(v) {
    return Array.isArray(v) ? v : [];
  }

  /**
   * Normaliza el mensaje de error para el usuario:
   * - Usa mensajes de validación (422) si vienen del back.
   * - Mapea errores de duplicado (CURP, RFC, IMSS, num. trabajador).
   * - Esconde detalles técnicos tipo SQLSTATE.
   */
  function normalizeErrorMessage(error, fallbackMessage) {
    let message = fallbackMessage || "Ocurrió un error al procesar la información.";

    if (!error) return message;

    // 1) Errores de validación estilo Laravel (422)
    if (error.errors && typeof error.errors === "object") {
      const firstKey = Object.keys(error.errors)[0];
      if (firstKey && Array.isArray(error.errors[firstKey]) && error.errors[firstKey][0]) {
        return error.errors[firstKey][0];
      }
    }

    const raw = error.message || error.error || "";
    if (typeof raw === "string" && raw) {
      const lower = raw.toLowerCase();

      // 2) Errores de duplicado (llaves únicas)
      if (lower.includes("duplicate entry")) {
        // CURP única
        if (lower.includes("empleados_curp_unique")) {
          return "Ya existe un empleado registrado con esa CURP. Verifica que no esté duplicado.";
        }

        // RFC único
        if (lower.includes("empleados_rfc_unique")) {
          return "Ya existe un empleado registrado con ese RFC. Verifica que no esté duplicado.";
        }

        // Número IMSS único
        if (lower.includes("empleados_numero_imss_unique") || lower.includes("numero_imss_unique")) {
          return "Ya existe un empleado registrado con ese número de IMSS.";
        }

        // Número trabajador único
        if (lower.includes("empleados_numero_trabajador_unique") || lower.includes("numero_trabajador_unique")) {
          return "Ya existe un empleado con ese número de trabajador.";
        }

        // Cualquier otro índice único relacionado
        return "Ya existe un registro con datos que deben ser únicos (CURP, RFC, IMSS o número de trabajador). Revisa la información capturada.";
      }

      // 3) Errores de red / fetch
      if (
        lower.includes("failed to fetch") ||
        lower.includes("networkerror") ||
        lower.includes("network error")
      ) {
        return "No se pudo conectar con el servidor. Intenta nuevamente en unos minutos.";
      }

      // 4) Para errores 4xx con mensaje 'humano' desde el back
      if (
        error.status &&
        error.status >= 400 &&
        error.status < 500 &&
        !lower.startsWith("sqlstate")
      ) {
        return raw;
      }

      // Cualquier SQLSTATE u otro stack técnico NO se muestra tal cual.
    }

    return message;
  }

  function handleCrudError(error, fallbackMessage) {
    console.error("Error en operación de empleados:", error);
    const message = normalizeErrorMessage(error, fallbackMessage);
    Swal.showValidationMessage(message);
  }

  function showErrorAlert(messageOrError, fallbackMessage) {
    // Permite pasar directamente el error crudo o un string
    let text;
    if (typeof messageOrError === "string") {
      text = messageOrError;
    } else {
      text = normalizeErrorMessage(messageOrError, fallbackMessage || "Ocurrió un error.");
    }

    Swal.fire({
      icon: "error",
      title: "Error",
      text,
      confirmButtonColor: "#4f46e5",
    });
  }

  function parseDate(value) {
    if (!value) return null;
    return new Date(value + "T00:00:00");
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
      const estado = estadoSel ? estadoSel.value || "" : "";
      const patronId = patronSel ? patronSel.value || "" : "";
      const sucursalId = sucursalSel ? sucursalSel.value || "" : "";
      const deptoId = deptoSel ? deptoSel.value || "" : "";
      const supId = supSel ? supSel.value || "" : "";
      const fIngDesde = parseDate(ingresoDesde ? ingresoDesde.value : "");
      const fIngHasta = parseDate(ingresoHasta ? ingresoHasta.value : "");
      const fImssDesde = parseDate(imssDesde ? imssDesde.value : "");
      const fImssHasta = parseDate(imssHasta ? imssHasta.value : "");

      rows().forEach((row) => {
        const search = (row.dataset.search || "").toLowerCase();
        const rowEstado = row.dataset.estadoImss || "";
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
        if (estadoSel) estadoSel.value = "";
        if (patronSel) patronSel.value = "";
        if (sucursalSel) sucursalSel.value = "";
        if (deptoSel) deptoSel.value = "";
        if (supSel) supSel.value = "";
        if (ingresoDesde) ingresoDesde.value = "";
        if (ingresoHasta) ingresoHasta.value = "";
        if (imssDesde) imssDesde.value = "";
        if (imssHasta) imssHasta.value = "";
        applyFilters();
      });
    }

    applyFilters();
  });

  /* =================== Lookups =================== */

  function buildLookupOptions(items, valueKey, labelKey = "nombre") {
    const opts = items
      .map(
        (item) =>
          `<option value="${item[valueKey]}">${item[labelKey] || ""}</option>`
      )
      .join("");
    const placeholder = '<option value="">Selecciona una opción…</option>';
    return placeholder + opts;
  }

  // Funcion para manejar estados de la republica mexicana
  function buildEstadoOptions() {
    return MEX_STATES
      .map((name) => `<option value="${name}">${name}</option>`)
      .join("");
  }

  /* =================== Formulario empleado =================== */

  function empleadoFormHtml(initial) {
    const patrones = asArray(lookups.patrones);
    const sucursales = asArray(lookups.sucursales);
    const departamentos = asArray(lookups.departamentos);
    const supervisores = asArray(lookups.supervisores);

    const supervisorLabel = (s) =>
      [s.nombres, s.apellidoPaterno, s.apellidoMaterno].filter(Boolean).join(" ");

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
          <span class="empleados-section-subtitle">Nombre, estado IMSS y fechas principales</span>
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
              <label class="empleados-label">Estado donde labora <span class="empleados-required">*</span></label>
              <input
                id="emp-estadoLaboral"
                type="text"
                class="empleados-input"
                list="empleados-estados-list"
                placeholder="Buscar Estado..."
                value="${v("estado", "")}">
              <datalist id="empleados-estados-list">
                ${buildEstadoOptions()}
              </datalist>
              <p class="empleados-help">Lista estática de estados de la República Mexicana.</p>
            </div>
            <div>
              <label class="empleados-label">Estado IMSS <span class="empleados-required">*</span></label>
              <select id="emp-estado" class="empleados-select">
                <option value="alta" ${v("estado_imss", "alta") === "alta" ? "selected" : ""}>Alta</option>
                <option value="inactivo" ${v("estado_imss") === "inactivo" ? "selected" : ""}>Inactivo</option>
              </select>
            </div>
          </div>

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Fecha ingreso <span class="empleados-required">*</span></label>
              <input id="emp-fechaIngreso" type="date" class="empleados-input"
                     value="${v("fecha_ingreso", "")}">
            </div>
            <div>
              <label class="empleados-label">Número de reingresos</label>
              <input id="emp-numeroReingresos" type="number" class="empleados-input"
                     value="${v("numero_reingresos", "0")}" readonly>
              <p class="empleados-help">Se calcula a partir del historial de periodos.</p>
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

          <div class="empleados-grid-2">
            <div>
              <label class="empleados-label">Empresa a facturar</label>
              <input id="emp-empresaFacturar" type="text" class="empleados-input"
                     placeholder="Empresa con la que se factura"
                     value="${v("empresa_facturar", "")}">
            </div>
            <div>
              <!-- vacío a propósito; el importe se captura en la sección de Datos bancarios -->
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
          <span class="empleados-section-title">Datos bancarios, SDI y color</span>
          <span class="empleados-section-subtitle">Información opcional para pagos y visualización</span>
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
              <label class="empleados-label">
                SDI <span class="empleados-required">*</span>
              </label>
              <input id="emp-sdi" type="number" step="0.01" class="empleados-input"
                    placeholder="Ej. 238.00"
                    value="${v("sdi", "")}">
            </div>
            <div>
              <label class="empleados-label">
                Importe factura mensual <span class="empleados-required">*</span>
              </label>
              <input id="emp-importeFactura" type="number" step="0.01" class="empleados-input"
                    placeholder="Ej. 8700.00"
                    value="${v("importe_factura_mensual", "")}">
            </div>
          </div>

          <div class="mt-4">
            <label class="empleados-label">
              Color del empleado <span class="empleados-required">*</span>
            </label>
            <div class="empleados-color-options" id="emp-color-group">
              ${EMPLOYEE_COLORS
                .map((c) => {
                  const selected = v("color", "") || EMPLOYEE_COLORS[0].value;
                  const checked = selected === c.value ? "checked" : "";
                  return `
                    <label class="empleados-color-option">
                      <input type="radio" name="emp-color" value="${c.value}" ${checked}>
                      <span class="empleados-color-swatch" style="background:${c.value};"></span>
                      <span class="empleados-color-label">${c.label}</span>
                    </label>
                  `;
                })
                .join("")}
            </div>
            <p class="empleados-help">
              Solo puedes elegir un color. Cada color representa un estatus como en la tabla de Excel.
            </p>
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

  // (Queda legacy, pero no rompe nada al no existir emp-color)
  function initColorPreview() {
    const colorInput = document.getElementById("emp-color");
    const colorHex = document.getElementById("emp-color-hex");
    if (!colorInput || !colorHex) return;

    const sync = () => {
      colorHex.textContent = colorInput.value || "";
    };

    colorInput.addEventListener("input", sync);
    sync();
  }

  function collectEmpleadoFormValues(id) {
    const getVal = (sel) =>
      (document.getElementById(sel) || { value: "" }).value.trim();

    const getRadioVal = (name) => {
      const el = document.querySelector(`input[name="${name}"]:checked`);
      return el ? el.value : "";
    };

    const payload = {
      id: id || null,
      nombres: getVal("emp-nombres"),
      apellidoPaterno: getVal("emp-apellidoPaterno"),
      apellidoMaterno: getVal("emp-apellidoMaterno") || null,
      numero_trabajador: getVal("emp-numeroTrabajador"),
      estado: getVal("emp-estadoLaboral"),
      estado_imss: getVal("emp-estado") || "alta",
      fecha_ingreso: getVal("emp-fechaIngreso"),
      numero_reingresos: getVal("emp-numeroReingresos") || null,
      patron_id: getVal("emp-patron"),
      sucursal_id: getVal("emp-sucursal"),
      departamento_id: getVal("emp-departamento"),
      supervisor_id: getVal("emp-supervisor") || null,
      empresa_facturar: getVal("emp-empresaFacturar") || null,
      importe_factura_mensual: getVal("emp-importeFactura"),
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
      sdi: getVal("emp-sdi"),
      color: getRadioVal("emp-color"),
    };

    // Validaciones front-friendly
    if (!payload.nombres) {
      Swal.showValidationMessage("El campo Nombres es obligatorio.");
      return null;
    }

    if (!payload.apellidoPaterno) {
      Swal.showValidationMessage("El Apellido paterno es obligatorio.");
      return null;
    }

    if (!payload.numero_trabajador) {
      Swal.showValidationMessage("El Número de trabajador es obligatorio.");
      return null;
    }

    if (!payload.estado) {
      Swal.showValidationMessage("El estado donde labora el empleado es obligatorio.");
      return null;
    }

    if (payload.estado) {
      const match = MEX_STATES.find(
        (name) => name.toLowerCase() === payload.estado.toLowerCase()
      );

      if (!match) {
        Swal.showValidationMessage("Selecciona un estado válido de la lista.");
        return null;
      }

      // Siempre se manda al back con el mismo formato
      payload.estado = match;
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

    if (!payload.importe_factura_mensual) {
      Swal.showValidationMessage("El importe de factura mensual es obligatorio. Captura el monto que se factura por este empleado.");
      return null;
    }

    if (!payload.sdi) {
      Swal.showValidationMessage("El SDI es obligatorio. Ingresa el salario diario integrado del empleado.");
      return null;
    }

    if (!payload.color) {
      Swal.showValidationMessage("Debes seleccionar un color para el empleado según la tabla de estatus.");
      return null;
    }

    return payload;
  }

  async function sendEmpleado(method, url, payload) {
    try {
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
        let data = {};
        try {
          data = await response.json();
        } catch (e) {
          data = {};
        }
        data.status = response.status;
        throw data;
      }

      return await response.json();
    } catch (err) {
      // Re-throw para que lo maneje handleCrudError en el flujo de Swal
      throw err;
    }
  }

  /* =================== Crear =================== */

  window.openCreateEmpleadoModal = function () {
    if (!canManage) {
      showErrorAlert("No tienes permisos para registrar empleados.");
      return;
    }

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
        initColorPreview();
      },
      preConfirm: () => {
        const payload = collectEmpleadoFormValues(null);
        if (!payload) return false;

        Swal.showLoading();

        return sendEmpleado("POST", `${baseUrl}/empleados`, payload)
          .then((data) => data)
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
    if (!canManage) {
      showErrorAlert("No tienes permisos para editar empleados.");
      return;
    }

    const dataset = btn.dataset || {};
    const initial = {
      id: dataset.id,
      nombres: dataset.nombres || "",
      apellidoPaterno: dataset.apellidoPaterno || "",
      apellidoMaterno: dataset.apellidoMaterno || "",
      numero_trabajador: dataset.numeroTrabajador || "",
      estado_imss: dataset.estadoImss || dataset.estado || "alta",
      estado: dataset.estadoLaboral || "",
      fecha_ingreso: dataset.fechaIngreso || "",
      numero_reingresos: dataset.numeroReingresos || "0",
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
      sdi: dataset.sdi || "",
      empresa_facturar: dataset.empresaFacturar || "",
      importe_factura_mensual: dataset.importeFacturaMensual || "",
      color: dataset.color || "",
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
        initColorPreview();

        const setVal = (id, val) => {
          const el = document.getElementById(id);
          if (el && val !== undefined && val !== null && val !== "") {
            el.value = val;
          }
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
                <p class="empleados-view-label">Estado donde labora</p>
                <p class="empleados-view-value">${d.estadoLaboral || d.estado || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Estado IMSS</p>
                <p class="empleados-view-value">${(d.estadoImss || d.estado || "").toUpperCase()}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Fechas</p>
                <p class="empleados-view-value">
                  Ingreso: ${d.fechaIngreso || "—"} · Alta IMSS: ${d.fechaAltaImss || "—"}
                </p>
              </div>
              <div>
                <p class="empleados-view-label">Número de reingresos</p>
                <p class="empleados-view-value">${d.numeroReingresos || "0"}</p>
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
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Empresa a facturar</p>
                <p class="empleados-view-value">${d.empresaFacturar || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Importe factura mensual</p>
                <p class="empleados-view-value">${d.importeFacturaMensual || "—"}</p>
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

        <div class="empleados-section empleados-section-open" data-section>
          <div class="empleados-section-header empleados-section-header-static">
            <span class="empleados-section-title">Datos bancarios y visuales</span>
          </div>
          <div class="empleados-section-body">
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Banco</p>
                <p class="empleados-view-value">${d.banco || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Cuenta bancaria</p>
                <p class="empleados-view-value">${d.cuentaBancaria || "—"}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">Tarjeta</p>
                <p class="empleados-view-value">${d.tarjeta || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">CLABE interbancaria</p>
                <p class="empleados-view-value">${d.clabe || "—"}</p>
              </div>
            </div>
            <div class="empleados-grid-2">
              <div>
                <p class="empleados-view-label">SDI</p>
                <p class="empleados-view-value">${d.sdi || "—"}</p>
              </div>
              <div>
                <p class="empleados-view-label">Color del empleado</p>
                <p class="empleados-view-value">
                  <span class="inline-block w-4 h-4 rounded-full align-middle mr-2"
                        style="background:${d.color || "#0ea5e9"};"></span>
                  <span>${d.color || "—"}</span>
                </p>
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

  /* =================== Cambiar estado IMSS =================== */

  window.openToggleEstadoEmpleado = function (id, currentEstado) {
    if (!canManage) {
      showErrorAlert("No tienes permisos para cambiar el estado IMSS.");
      return;
    }

    Swal.fire({
      title: "Cambiar estado IMSS",
      text: "Puedes activar o marcar como inactivo al empleado.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      showCloseButton: true,
      html: `
        <div class="mt-3">
          <label class="empleados-label mb-2 block">Estado IMSS</label>
          <select id="emp-toggle-estado" class="empleados-select w-full">
            <option value="alta" ${currentEstado === "alta" ? "selected" : ""}>Alta</option>
            <option value="inactivo" ${currentEstado === "inactivo" ? "selected" : ""}>Inactivo</option>
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
          body: JSON.stringify({ estado_imss: nuevo }),
        })
          .then((res) => {
            if (!res.ok) {
              return res.json().then((d) => {
                d.status = res.status;
                return Promise.reject(d);
              });
            }
            return res.json();
          })
          .catch((err) => {
            handleCrudError(err, "No se pudo actualizar el estado IMSS.");
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
    if (!canManage) {
      showErrorAlert("No tienes permisos para eliminar empleados.");
      return;
    }

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
          if (!res.ok) {
            return res.json().then((d) => {
              d.status = res.status;
              return Promise.reject(d);
            });
          }
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
          console.error("Error al eliminar empleado:", err);
          Swal.close();
          showErrorAlert(err, "Ocurrió un error al eliminar el empleado.");
        });
    });
  };

  /* =================== Periodos de empleado =================== */

  function buildPeriodosHtml(empleadoNombre, periodos) {
    if (!periodos.length) {
      return `
        <p class="text-sm text-slate-600 mb-4">
          No hay periodos registrados para este empleado.
        </p>
        ${
          canManage
            ? '<p class="text-xs text-slate-400 mb-2">Puedes registrar altas, bajas o reingresos desde el botón "Agregar periodo".</p>'
            : ""
        }
        <div class="mt-3">
          ${
            canManage
              ? '<button type="button" class="empleados-btn-cta" id="btn-add-periodo">Agregar periodo</button>'
              : ""
          }
        </div>
      `;
    }

    const rows = periodos
      .map((p) => {
        const fechaAlta = p.fecha_alta || "—";
        const fechaBaja = p.fecha_baja || "—";
        const tipo = (p.tipo_alta || "").toUpperCase();
        const motivo = p.motivo_baja || "—";

        return `
          <tr class="border-b border-slate-100">
            <td class="px-3 py-2 text-xs md:text-sm text-slate-800">${tipo}</td>
            <td class="px-3 py-2 text-xs md:text-sm text-slate-700">${fechaAlta}</td>
            <td class="px-3 py-2 text-xs md:text-sm text-slate-700">${fechaBaja}</td>
            <td class="px-3 py-2 text-xs md:text-sm text-slate-700">${motivo}</td>
          </tr>
        `;
      })
      .join("");

    return `
      <p class="text-sm text-slate-700 mb-3">
        Historial de periodos (altas, bajas y reingresos) del empleado:
        <span class="font-semibold">${empleadoNombre}</span>
      </p>

      <div class="overflow-x-auto rounded-xl border border-slate-200 mb-3">
        <table class="min-w-full text-xs md:text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase text-[10px]">Tipo</th>
              <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase text-[10px]">Fecha alta</th>
              <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase text-[10px]">Fecha baja</th>
              <th class="px-3 py-2 text-left font-semibold text-slate-500 uppercase text-[10px]">Motivo baja</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-100">
            ${rows}
          </tbody>
        </table>
      </div>

      <div class="mt-3 flex justify-end">
        ${
          canManage
            ? '<button type="button" class="empleados-btn-cta" id="btn-add-periodo">Agregar periodo</button>'
            : ""
        }
      </div>
    `;
  }

  async function fetchPeriodos(empleadoId) {
    const res = await fetch(`${baseUrl}/empleados/${empleadoId}/periodos`, {
      headers: {
        Accept: "application/json",
      },
    });

    if (!res.ok) {
      let data = {};
      try {
        data = await res.json();
      } catch (e) {
        data = {};
      }
      data.status = res.status;
      throw data;
    }

    const data = await res.json();
    return Array.isArray(data.data) ? data.data : data;
  }

  function periodoFormHtml() {
    const today = new Date().toISOString().slice(0, 10);
    return `
      <div class="empleados-form-wrapper">
        <div class="empleados-grid-2">
          <div>
            <label class="empleados-label">Tipo de alta <span class="empleados-required">*</span></label>
            <select id="per-tipo" class="empleados-select">
              <option value="">Selecciona...</option>
              <option value="alta">Alta</option>
              <option value="reingreso">Reingreso</option>
              <option value="baja">Baja</option>
            </select>
          </div>
          <div>
            <label class="empleados-label">Fecha de alta <span class="empleados-required">*</span></label>
            <input id="per-fecha-alta" type="date" class="empleados-input" value="${today}">
          </div>
        </div>

        <div class="empleados-grid-2 mt-3">
          <div>
            <label class="empleados-label">Fecha de baja</label>
            <input id="per-fecha-baja" type="date" class="empleados-input">
            <p class="empleados-help">Solo para periodos de baja.</p>
          </div>
          <div>
            <label class="empleados-label">Motivo de baja</label>
            <textarea id="per-motivo" class="empleados-input" rows="3"
              placeholder="Opcional, solo si aplica baja."></textarea>
          </div>
        </div>
      </div>
    `;
  }

  async function sendPeriodo(method, url, payload) {
    const res = await fetch(url, {
      method,
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      let data = {};
      try {
        data = await res.json();
      } catch (e) {
        data = {};
      }
      data.status = res.status;
      throw data;
    }

    return res.json();
  }

  window.openPeriodosEmpleadoModal = function (btn) {
    const empleadoId = btn.dataset.empleadoId;
    const empleadoNombre = btn.dataset.empleadoNombre || "";

    Swal.fire({
      title: "Historial de periodos",
      html: '<p class="text-sm text-slate-600">Cargando periodos...</p>',
      width: "80%",
      maxWidth: 900,
      showCloseButton: true,
      showConfirmButton: true,
      confirmButtonText: "Cerrar",
      didOpen: () => {
        Swal.showLoading();
        fetchPeriodos(empleadoId)
          .then((periodos) => {
            Swal.update({
              html: buildPeriodosHtml(empleadoNombre, periodos),
            });

            const addBtn = document.getElementById("btn-add-periodo");
            if (addBtn && canManage) {
              addBtn.addEventListener("click", () => {
                openCreatePeriodoModal(empleadoId, empleadoNombre);
              });
            }
          })
          .catch((err) => {
            console.error("Error al cargar periodos:", err);
            Swal.update({
              html: '<p class="text-sm text-rose-600">No se pudieron cargar los periodos.</p>',
            });
          })
          .finally(() => {
            Swal.hideLoading();
          });
      },
    });
  };

  function openCreatePeriodoModal(empleadoId, empleadoNombre) {
    if (!canManage) {
      showErrorAlert("No tienes permisos para registrar periodos.");
      return;
    }

    Swal.fire({
      title: "Registrar periodo",
      html: periodoFormHtml(),
      width: "600px",
      showCloseButton: true,
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      reverseButtons: true,
      focusConfirm: false,
      preConfirm: () => {
        const tipo = (document.getElementById("per-tipo") || { value: "" }).value;
        const fechaAlta = (document.getElementById("per-fecha-alta") || { value: "" }).value;
        const fechaBaja = (document.getElementById("per-fecha-baja") || { value: "" }).value;
        const motivo = (document.getElementById("per-motivo") || { value: "" }).value.trim();

        if (!tipo) {
          Swal.showValidationMessage("Selecciona el tipo de alta.");
          return false;
        }
        if (!fechaAlta) {
          Swal.showValidationMessage("La fecha de alta es obligatoria.");
          return false;
        }

        const payload = {
          empleado_id: empleadoId,
          tipo_alta: tipo,
          fecha_alta: fechaAlta,
          fecha_baja: fechaBaja || null,
          motivo_baja: motivo || null,
        };

        Swal.showLoading();

        return sendPeriodo("POST", `${baseUrl}/empleados/${empleadoId}/periodos`, payload)
          .then((data) => data)
          .catch((err) => {
            handleCrudError(err, "No se pudo registrar el periodo.");
            return false;
          });
      },
    }).then((result) => {
      if (!result.isConfirmed || !result.value) return;

      Swal.fire({
        icon: "success",
        title: "Periodo registrado",
        text: "El periodo se guardó correctamente.",
        confirmButtonColor: "#4f46e5",
      }).then(() => {
        const fakeBtn = {
          dataset: { empleadoId, empleadoNombre },
        };
        window.openPeriodosEmpleadoModal(fakeBtn);
      });
    });
  }
})();
