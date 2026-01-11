<script>
import moment from "moment";
import Swal from "sweetalert2";
import simpleBar from "simplebar-vue"
import { CalendarIcon } from "@zhuowenli/vue-feather-icons";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import "flatpickr/dist/l10n/pt.js";
import Choices from "choices.js";

import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin, { Draggable } from "@fullcalendar/interaction";
import bootstrapPlugin from "@fullcalendar/bootstrap";
import listPlugin from "@fullcalendar/list";

import FullCalendar from "@fullcalendar/vue3";
import ptBr from "@fullcalendar/core/locales/pt-br";

import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Modal from "@/Components/Modal.vue";
import SuggestInput from "@/Components/SuggestInput.vue";
import TableGrid from "@/Components/Tables/TableGrid.vue";

export default {
  data() {
    return {
      opcoesCalendario: {
        timeZone: "local",
        droppable: true,
        navLinks: true,
        navLinkDayClick: this.cliqueNoDiaNavegacao,
        locale: "pt-br",
        locales: [ptBr],
        plugins: [
          dayGridPlugin,
          timeGridPlugin,
          interactionPlugin,
          bootstrapPlugin,
          listPlugin,
        ],
        themeSystem: "bootstrap",
        headerToolbar: {
          left: "prev,next today",
          center: "title",
          right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
        },
        moreLinkClick: this.onMoreLinkClick,
        windowResize: () => {
          this.obterVisaoInicial();
        },
        initialView: this.obterVisaoInicial(),
        initialEvents: [],
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        dateClick: this.cliqueNaData,
        eventClick: this.editarEvento,
        viewDidMount: this.aoMontarVisao,
        datesSet: this.aoDefinirDatas,
      },
      eventosAtuais: [],
      modalAgendarVisivel: false,
      modalEditarVisivel: false,
      edit: {},
      pacientesLocal: [],
      profissionaisLocal: [],
      procedimentosLocal: [],
      procedimentosFiltrados: [],
      opcoesStatus: [],
      agendamentoForm: {
        paciente_id: null,
        profissional_saude_id: null,
        procedimento_id: null,
        data: "",
        hora: "",
        status_id: null,
        valor_cobrado: "",
        observacoes: ""
      },
      termoBuscaPaciente: "",
      mostrarSugestoesPaciente: false,
      orcamentosPagos: [],
      carregandoOrcamentosPagos: false,
      termoBuscaOrcamento: "",
      buscandoOrcamentosPagos: false,
      timerBuscaOrcamento: null,
      mostrarSugestoesOrcamento: false,
      buscaOrcamentoAbortController: null,
      ignorarMudancaTermoOrcamento: false,
      orcamentoSelecionado: null,
      orcamentoSelecionadoId: null,
      itensOrcamentoSelecionado: [],
      itensOrcamentoPorProcedimento: {},
      processandoCriacao: false,
      opcoesFlatpickrData: {
        altInput: true,
        altFormat: "d M, Y",
        dateFormat: "Y-m-d",
        locale: "pt",
      },
      opcoesFlatpickrHora: {
        enableTime: true,
        noCalendar: true,
        altInput: true,
        altFormat: "H:i",
        dateFormat: "H:i",
        time_24hr: true,
        locale: "pt",
      },
      agendasHoje: [],
      modalEventosDiaVisivel: false,
      eventosDiaModal: [],
      dataEventosDiaModal: null,
      paletaMedicos: [
        { bg: "bg-success-subtle", text: "text-success" },
        { bg: "bg-info-subtle", text: "text-info" },
        { bg: "bg-warning-subtle", text: "text-warning" },
        { bg: "bg-danger-subtle", text: "text-danger" },
        { bg: "bg-primary-subtle", text: "text-primary" },
        { bg: "bg-secondary-subtle", text: "text-secondary" },
      ],
      dataAgendaSelecionada: null,
      carregandoAgendas: false,
      classeFundoDataSelecionada: null,
      coresDataSelecionada: [],
      mapaDiasSemanaSelecionados: {},
      mapaAgendasSemana: {},
      processandoEdicao: false,
      chaveProcedimentoCriacao: 0,
      chaveProfissionalCriacao: 0,
      editAgendamentoForm: {
        id: null,
        paciente_id: null,
        profissional_saude_id: null,
        procedimento_id: null,
        data: "",
        hora: "",
        status_id: null,
        valor_cobrado: "",
        observacoes: ""
      },
      orcamentosPagosEdicao: [],
      carregandoOrcamentosPagosEdicao: false,
      termoBuscaPacienteEdicao: "",
      mostrarSugestoesPacienteEdicao: false,
      termoBuscaOrcamentoEdicao: "",
      buscandoOrcamentosPagosEdicao: false,
      timerBuscaOrcamentoEdicao: null,
      mostrarSugestoesOrcamentoEdicao: false,
      buscaOrcamentoAbortControllerEdicao: null,
      ignorarMudancaTermoOrcamentoEdicao: false,
      orcamentoSelecionadoEdicao: null,
      orcamentoSelecionadoEdicaoId: null,
      carregandoItensOrcamentoEdicao: false,
      procedimentoIdOriginalEdicao: null,
      itensOrcamentoSelecionadoEdicao: [],
      itensOrcamentoPorProcedimentoEdicao: {},
      procedimentosFiltradosEdicao: [],
      mostrarDropdownOrcamentoEdicao: false,
      modalCancelamento: false,
      chaveProcedimentoEdicao: 0,
      chaveOrcamentoEdicao: 0,
      chaveRenderizacaoModalEvento: 0,
      renderProcedimentoEdicao: true,
      nomePacienteEdicao: "",
      eventoSelecionado: null,
      editandoNoModalDeCriacao: false,
    };
  },
  components: {
    Layout,
    PageHeader,
    FullCalendar,
    CalendarIcon,
    flatPickr,
    simpleBar,
    Modal,
    SuggestInput,
    TableGrid
  },
  computed: {
    tituloModalEventosDia() {
      try {
        if (!this.dataEventosDiaModal) return "Agendamentos do dia";
        return `Agendamentos de ${moment(this.dataEventosDiaModal, "YYYY-MM-DD", true).format("DD/MM/YYYY")}`;
      } catch (e) {
        return "Agendamentos do dia";
      }
    },
    pacientesSugestoesCriacao() {
      try {
        const base = this.listaPacientesCriacao || [];
        const q = String(this.termoBuscaPaciente || "").trim().toLowerCase();
        if (!q) return base;
        return base.filter(p => {
          const nome = String(p?.nome || "").toLowerCase();
          const cpf = String(p?.cpf || "").toLowerCase();
          return nome.includes(q) || cpf.includes(q);
        });
      } catch (e) {
        return this.listaPacientesCriacao || [];
      }
    },
    pacientesSugestoesEdicao() {
      try {
        const base = this.listaPacientesEdicao || [];
        const q = String(this.termoBuscaPacienteEdicao || "").trim().toLowerCase();
        if (!q) return base;
        return base.filter(p => {
          const nome = String(p?.nome || "").toLowerCase();
          const cpf = String(p?.cpf || "").toLowerCase();
          return nome.includes(q) || cpf.includes(q);
        });
      } catch (e) {
        return this.listaPacientesEdicao || [];
      }
    },
    listaPacientesCriacao() {
      try {
        if (this.orcamentoSelecionado && this.orcamentoSelecionado.paciente_id != null) {
          const p = (this.pacientesLocal || []).find(x => String(x.id) === String(this.orcamentoSelecionado.paciente_id));
          if (p) return [p];
          const nome = this.orcamentoSelecionado?.paciente || this.orcamentoSelecionado?.paciente_nome || "Paciente";
          return [{ id: this.orcamentoSelecionado.paciente_id, nome }];
        }
        return this.pacientesLocal || [];
      } catch (e) {
        return this.pacientesLocal || [];
      }
    },
    listaProfissionaisCriacao() {
      try {
        if (this.orcamentoSelecionado && this.orcamentoSelecionado.profissional_saude_id != null) {
          const d = (this.profissionaisLocal || []).find(x => String(x.id) === String(this.orcamentoSelecionado.profissional_saude_id));
          if (d) return [d];
          return [{ id: this.orcamentoSelecionado.profissional_saude_id, nome: "Profissional" }];
        }
        return this.profissionaisLocal || [];
      } catch (e) {
        return this.profissionaisLocal || [];
      }
    },
    listaPacientesEdicao() {
      try {
        if (this.orcamentoSelecionadoEdicao && this.orcamentoSelecionadoEdicao.paciente_id != null) {
          const p = (this.pacientesLocal || []).find(x => String(x.id) === String(this.orcamentoSelecionadoEdicao.paciente_id));
          if (p) return [p];
          const nome = this.orcamentoSelecionadoEdicao?.paciente || this.orcamentoSelecionadoEdicao?.paciente_nome || this.nomePacienteEdicao || "Paciente";
          return [{ id: this.orcamentoSelecionadoEdicao.paciente_id, nome }];
        }
        return this.pacientesLocal || [];
      } catch (e) {
        return this.pacientesLocal || [];
      }
    },
    listaProfissionaisEdicao() {
      try {
        if (this.orcamentoSelecionadoEdicao) {
          const id = this.orcamentoSelecionadoEdicao.profissional_saude_id != null
            ? this.orcamentoSelecionadoEdicao.profissional_saude_id
            : this.editAgendamentoForm?.profissional_saude_id ?? null;
          if (id != null) {
            const d = (this.profissionaisLocal || []).find(x => String(x.id) === String(id));
            if (d) return [d];
            return [{ id, nome: "Profissional" }];
          }
        }
        return this.profissionaisLocal || [];
      } catch (e) {
        return this.profissionaisLocal || [];
      }
    },
  },
  mounted() {
    new Draggable(document.getElementById("external-events"), {
      itemSelector: ".external-event",
      eventData: function (eventEl) {
        return {
          title: eventEl.innerText,
          start: new Date(),
          className: eventEl.getAttribute("data-class"),
        };
      },
    });
    try {
      moment.locale("pt-br");
      const props = this.$page && this.$page.props ? this.$page.props : {};
      this.pacientesLocal = [...(props.pacientes || [])];
      this.profissionaisLocal = [...(props.profissionais || [])];
      this.procedimentosLocal = [...(props.procedimentos || [])];
      this.procedimentosFiltrados = [...(this.procedimentosLocal || [])];
      this.opcoesStatus = [...(props.status || [])];
      this.agendasHoje = [...(props.agendasHoje || [])];
      this.dataAgendaSelecionada = moment().format("YYYY-MM-DD");
      if (this.agendasHoje.length > 0) {
        this.classeFundoDataSelecionada = this.paletaMedicos[0].bg;
        this.coresDataSelecionada = this.agendasHoje.map((_, idx) => this.paletaMedicos[idx % this.paletaMedicos.length].bg);
      }
      this.buscarMapaDiasSemanaSelecionados();
      this.buscarUltimosAgendamentos();
      window.addEventListener("unhandledrejection", (event) => {
        try {
          const r = event?.reason;
          const s = typeof r === "string" ? r : String(r?.message || r || "");
          if (s && (s.includes("A listener indicated an asynchronous response by returning true") || s.includes("message channel closed"))) {
            if (event && event.preventDefault) event.preventDefault();
          }
        } catch (e) {}
      });
    } catch (e) {}
  },
  watch: {
    orcamentoSelecionadoId(nv) {
      const v = nv != null ? String(nv) : "";
      if (v) {
        this.aoSelecionarOrcamentoPorId();
      } else {
        this.orcamentoSelecionado = null;
        this.itensOrcamentoSelecionado = [];
        this.itensOrcamentoPorProcedimento = {};
        this.procedimentosFiltrados = [...(this.procedimentosLocal || [])];
        this.agendamentoForm.procedimento_id = null;
        this.agendamentoForm.valor_cobrado = "";
      }
    },
    orcamentoSelecionadoEdicaoId(nv) {
      const v = nv != null ? String(nv) : "";
      if (v) {
        this.aoSelecionarOrcamentoEdicaoPorId();
      } else {
        this.orcamentoSelecionadoEdicao = null;
        this.itensOrcamentoSelecionadoEdicao = [];
        this.itensOrcamentoPorProcedimentoEdicao = {};
        this.procedimentosFiltradosEdicao = [...(this.procedimentosLocal || [])];
        this.editAgendamentoForm.procedimento_id = null;
        this.editAgendamentoForm.valor_cobrado = "";
      }
    },
    termoBuscaOrcamento(nv) {
      try {
        if (this.ignorarMudancaTermoOrcamento) {
          this.mostrarSugestoesOrcamento = false;
          this.ignorarMudancaTermoOrcamento = false;
          return;
        }
        if (this.timerBuscaOrcamento) {
          clearTimeout(this.timerBuscaOrcamento);
          this.timerBuscaOrcamento = null;
        }
        this.timerBuscaOrcamento = setTimeout(async () => {
          const q = String(nv || "").trim();
          if (q === "") {
            this.buscandoOrcamentosPagos = false;
            this.mostrarSugestoesOrcamento = false;
            this.orcamentosPagos = [];
            if (this.buscaOrcamentoAbortController) {
              try { this.buscaOrcamentoAbortController.abort(); } catch (_) {}
              this.buscaOrcamentoAbortController = null;
            }
            return;
          }
          this.mostrarSugestoesOrcamento = true;
          await this.buscarOrcamentosPagosPorTexto(q);
        }, 250);
      } catch (e) {}
    },
    termoBuscaOrcamentoEdicao(nv) {
      try {
        if (this.ignorarMudancaTermoOrcamentoEdicao) {
          this.mostrarSugestoesOrcamentoEdicao = false;
          this.ignorarMudancaTermoOrcamentoEdicao = false;
          return;
        }
        if (this.timerBuscaOrcamentoEdicao) {
          clearTimeout(this.timerBuscaOrcamentoEdicao);
          this.timerBuscaOrcamentoEdicao = null;
        }
        this.timerBuscaOrcamentoEdicao = setTimeout(async () => {
          const q = String(nv || "").trim();
          if (q === "") {
            this.buscandoOrcamentosPagosEdicao = false;
            this.mostrarSugestoesOrcamentoEdicao = false;
            this.orcamentosPagosEdicao = [];
            if (this.buscaOrcamentoAbortControllerEdicao) {
              try { this.buscaOrcamentoAbortControllerEdicao.abort(); } catch (_) {}
              this.buscaOrcamentoAbortControllerEdicao = null;
            }
            return;
          }
          this.mostrarSugestoesOrcamentoEdicao = true;
          await this.buscarOrcamentosPagosPorTextoEdicao(q);
        }, 250);
      } catch (e) {}
    },
    "agendamentoForm.profissional_saude_id"() {
      try { this.verificarAgendaProfissionalParaDia(); } catch (e) {}
    },
  },
  methods: {
    onMoreLinkClick(arg) {
      try {
        let ds = null;
        try {
          const cell = arg?.dayEl?.closest?.("[data-date]") || arg?.dayEl;
          const attr = cell?.getAttribute?.("data-date");
          if (attr) ds = String(attr).slice(0, 10);
        } catch (_) {}
        if (!ds && arg?.date) {
          ds = moment.utc(arg.date).format("YYYY-MM-DD");
        }
        if (!ds) return false;
        this.dataEventosDiaModal = ds;
        const api = this.$refs.fullCalendar?.getApi?.();
        const events = api?.getEvents?.() || [];
        const dayStart = moment(ds, "YYYY-MM-DD", true).startOf("day");
        const dayEnd = moment(ds, "YYYY-MM-DD", true).endOf("day");
        const dayEvents = events.filter(ev => {
          const s = ev?.start ? moment(ev.start) : null;
          if (!s || !s.isValid()) return false;
          const e = ev?.end ? moment(ev.end) : s;
          return s.isSameOrBefore(dayEnd) && e.isSameOrAfter(dayStart);
        });
        this.eventosDiaModal = dayEvents;
        this.atribuirMedicoAEventosDia(ds).then(() => {
          this.modalEventosDiaVisivel = true;
        }).catch(() => {
          this.modalEventosDiaVisivel = true;
        });
        this.fecharPopoversCalendario();
      } catch (e) {}
      return false;
    },
    async atribuirMedicoAEventosDia(ds) {
      try {
        const agendas = await this.buscarAgendasPorDataSomente(ds);
        const list = Array.isArray(agendas) ? agendas : [];
        (this.eventosDiaModal || []).forEach(ev => {
          try {
            const hhmm = moment(ev?.start).format("HH:mm");
            const m = this.parseMinutosHora(hhmm);
            let chosen = null;
            for (const a of list) {
              const mi = this.parseMinutosHora(a.hora_inicio);
              const mf = this.parseMinutosHora(a.hora_fim);
              if (mi == null || mf == null) continue;
              if (m >= mi && m <= mf) { chosen = a; break; }
            }
            if (!chosen && list.length > 0) chosen = list[0];
            if (chosen) {
              if (typeof ev.setExtendedProp === "function") {
                ev.setExtendedProp("profissional_nome", chosen.nome || "Profissional");
                ev.setExtendedProp("profissional_saude_id", chosen.profissional_saude_id ?? null);
              } else {
                ev.extendedProps = { ...(ev.extendedProps || {}), profissional_nome: chosen.nome || "Profissional", profissional_saude_id: chosen.profissional_saude_id ?? null };
              }
            }
          } catch (_) {}
        });
      } catch (e) {}
    },
    formatHora24(date) {
      try {
        if (!date) return "";
        const d = moment(date);
        if (!d.isValid()) return "";
        return d.format("HH:mm");
      } catch (e) {
        return "";
      }
    },
    eventosDiaGrid() {
      try {
        return (this.eventosDiaModal || []).map(ev => {
          const t = String(ev?.title || "");
          let pacNome = "";
          let procNome = "";
          if (t.includes("•")) {
            const parts = t.split("•");
            pacNome = String(parts[0] || "").trim();
            procNome = String(parts[1] || "").trim();
          } else {
            pacNome = t.trim();
            procNome = String(ev?.extendedProps?.procedimento_nome || "").trim();
          }
          const st = String(ev?.extendedProps?.status || "").trim();
          const medNome = String(ev?.extendedProps?.profissional_nome || "").trim();
          return {
            id: ev?.id,
            paciente: pacNome || "Paciente",
            procedimento: procNome || "Procedimento",
            hora: this.formatHora24(ev?.start) || "--:--",
            status: st || "Agendado",
            medico: medNome || "Profissional",
          };
        });
      } catch (e) {
        return [];
      }
    },
    async cancelarAgendamentoPorId(row) {
      try {
        const id = row?.id ?? null;
        if (!id) return;
        await window.axios.put(`/agendamentos/${id}/cancel`, { observacoes: null });
        const api = this.$refs.fullCalendar?.getApi?.();
        const ev = api?.getEventById?.(id);
        if (ev) {
          ev.setExtendedProp("status", "Cancelado");
          ev.setProp("classNames", ["bg-danger-subtle"]);
        }
        this.eventosDiaModal = (this.eventosDiaModal || []).map(e => {
          if (String(e.id) === String(id)) {
            try { e.extendedProps = { ...(e.extendedProps || {}), status: "Cancelado" }; } catch (_) {}
          }
          return e;
        });
        await this.buscarUltimosAgendamentos();
      } catch (e) {}
    },
    onBlurSugestoesPaciente() {
      setTimeout(() => { this.mostrarSugestoesPaciente = false; }, 150);
    },
    selecionarSugestaoPaciente(p) {
      const id = p?.id ?? null;
      this.agendamentoForm.paciente_id = id;
      this.termoBuscaPaciente = String(p?.nome || "");
      this.mostrarSugestoesPaciente = false;
      this.buscarOrcamentosPorPaciente();
    },
    onBlurSugestoesPacienteEdicao() {
      setTimeout(() => { this.mostrarSugestoesPacienteEdicao = false; }, 150);
    },
    selecionarSugestaoPacienteEdicao(p) {
      const id = p?.id ?? null;
      this.editAgendamentoForm.paciente_id = id;
      this.termoBuscaPacienteEdicao = String(p?.nome || "");
      this.mostrarSugestoesPacienteEdicao = false;
      this.buscarOrcamentosPorPacienteEdicao();
    },
    formatarData(date) {
      try {
        if (!date) return "";
        const d = moment(date);
        if (!d.isValid()) return "";
        const mes = d.format("MMM");
        const dia = d.format("DD");
        const ano = d.format("YYYY");
        return `${dia} ${mes} ${ano}`;
      } catch (e) {
        return "";
      }
    },
    encontrarPacientePorNome(nome) {
      const n = String(nome || "").trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
      const arr = this.pacientesLocal || [];
      for (const p of arr) {
        const pn = String(p?.nome || "").trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        if (pn === n) return p;
      }
      return null;
    },
    async buscarOrcamentosPorPaciente() {
      const pid = this.agendamentoForm.paciente_id;
      if (!pid) {
        this.orcamentosPagos = [];
        return;
      }
      this.carregandoOrcamentosPagos = true;
      try {
        const resp = await window.axios.get(`/pacientes/${pid}/orcamentos`);
        const arr = Array.isArray(resp?.data?.orcamentos) ? resp.data.orcamentos : [];
        const paid = arr.filter(o => !!o.pago);
        this.orcamentosPagos = paid;
        if (paid.length > 0) {
          if (!this.orcamentoSelecionadoId) {
            this.orcamentoSelecionadoId = paid[0].id;
            this.aoSelecionarOrcamento(paid[0]);
          }
        } else {
          this.orcamentoSelecionadoId = null;
          this.orcamentoSelecionado = null;
        }
      } catch (e) {
        this.orcamentosPagos = [];
      } finally {
        this.carregandoOrcamentosPagos = false;
      }
    },
    async buscarOrcamentosPagosPorTexto(q) {
      try {
        if (this.buscaOrcamentoAbortController) {
          try { this.buscaOrcamentoAbortController.abort(); } catch (_) {}
        }
        this.buscaOrcamentoAbortController = new AbortController();
        this.buscandoOrcamentosPagos = true;
        const resp = await window.axios.get("/orcamentos/search-paid", { params: { q }, signal: this.buscaOrcamentoAbortController.signal });
        const arr = Array.isArray(resp?.data?.orcamentos) ? resp.data.orcamentos : [];
        this.orcamentosPagos = arr;
      } catch (e) {
        try {
          const name = e?.name || "";
          const msg = String(e?.message || "");
          if (name.includes("Abort") || msg.includes("canceled") || msg.includes("Cancelled")) {
            return;
          }
        } catch (_) {}
      } finally {
        this.buscandoOrcamentosPagos = false;
        this.buscaOrcamentoAbortController = null;
      }
    },
    async buscarOrcamentosPagosPorTextoEdicao(q) {
      try {
        if (this.buscaOrcamentoAbortControllerEdicao) {
          try { this.buscaOrcamentoAbortControllerEdicao.abort(); } catch (_) {}
        }
        this.buscaOrcamentoAbortControllerEdicao = new AbortController();
        this.buscandoOrcamentosPagosEdicao = true;
        const resp = await window.axios.get("/orcamentos/search-paid", { params: { q }, signal: this.buscaOrcamentoAbortControllerEdicao.signal });
        const arr = Array.isArray(resp?.data?.orcamentos) ? resp.data.orcamentos : [];
        this.orcamentosPagosEdicao = arr;
      } catch (e) {
        try {
          const name = e?.name || "";
          const msg = String(e?.message || "");
          if (name.includes("Abort") || msg.includes("canceled") || msg.includes("Cancelled")) {
            return;
          }
        } catch (_) {}
      } finally {
        this.buscandoOrcamentosPagosEdicao = false;
        this.buscaOrcamentoAbortControllerEdicao = null;
      }
    },
    onBlurSugestoesOrcamento() {
      setTimeout(() => { this.mostrarSugestoesOrcamento = false; }, 150);
    },
    selecionarSugestaoOrcamento(o) {
      this.orcamentoSelecionadoId = o?.id ?? null;
      if (o) this.aoSelecionarOrcamento(o);
      this.ignorarMudancaTermoOrcamento = true;
      this.termoBuscaOrcamento = String(o?.numero || "");
      this.mostrarSugestoesOrcamento = false;
    },
    onBlurSugestoesOrcamentoEdicao() {
      setTimeout(() => { this.mostrarSugestoesOrcamentoEdicao = false; }, 150);
    },
    selecionarSugestaoOrcamentoEdicao(o) {
      this.orcamentoSelecionadoEdicaoId = o?.id ?? null;
      if (o) this.aoSelecionarOrcamentoEdicao(o);
      this.ignorarMudancaTermoOrcamentoEdicao = true;
      this.termoBuscaOrcamentoEdicao = String(o?.numero || "");
      this.mostrarSugestoesOrcamentoEdicao = false;
    },
    aoSelecionarOrcamentoPorId() {
      const id = this.orcamentoSelecionadoId;
      if (!id) {
        this.orcamentoSelecionado = null;
        return;
      }
      const o = (this.orcamentosPagos || []).find(x => String(x.id) === String(id));
      if (o) this.aoSelecionarOrcamento(o);
    },
    aoSelecionarOrcamentoEdicaoPorId() {
      const id = this.orcamentoSelecionadoEdicaoId;
      if (!id) {
        this.orcamentoSelecionadoEdicao = null;
        return;
      }
      const o = (this.orcamentosPagosEdicao || []).find(x => String(x.id) === String(id));
      if (o) this.aoSelecionarOrcamentoEdicao(o);
    },


    formatarHora(params) {
      try {
        const d = params ? new Date(params) : null;
        if (!d || isNaN(d.getTime())) return null;
        let hour = d.getHours();
        let minute = d.getMinutes();
        const timeFormat = hour >= 12 ? "PM" : "AM";
        hour = hour % 12;
        hour = hour ? hour : 12;
        minute = minute < 10 ? "0" + minute : String(minute).padStart(2, "0");
        return `${hour}:${minute} ${timeFormat}`;
      } catch (e) {
        return null;
      }
    },

    obterVisaoInicial() {
      if (window.innerWidth >= 768 && window.innerWidth < 1200) {
        return "timeGridWeek";
      } else if (window.innerWidth <= 768) {
        return "listMonth";
      } else {
        return "dayGridMonth";
      }
    },
    /**
     * Modal open for add event
     */
    cliqueNaData(info) {
      try {
        const ds = info && info.dateStr ? info.dateStr : moment(info?.date).format("YYYY-MM-DD");
        this.agendamentoForm.data = ds;
        this.buscarAgendasPorData(ds);
      } catch (e) {}
      this.editandoNoModalDeCriacao = false;
      this.orcamentoSelecionado = null;
      this.orcamentoSelecionadoId = null;
      this.termoBuscaOrcamento = "";
      this.mostrarSugestoesOrcamento = false;
      this.orcamentosPagos = [];
      try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.destroyChoiceEl(el1); } catch (_) {}
      try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.destroyChoiceEl(el2); } catch (_) {}
      this.chaveProfissionalCriacao++;
      this.chaveProcedimentoCriacao++;
      try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) {}
      this.modalAgendarVisivel = true;
    },
    abrirNovoAgendamento() {
      this.editandoNoModalDeCriacao = false;
      this.agendamentoForm = { paciente_id: null, profissional_saude_id: null, procedimento_id: null, data: "", hora: "", status_id: null, valor_cobrado: "", observacoes: "" };
      this.termoBuscaPaciente = "";
      this.mostrarSugestoesPaciente = false;
      this.orcamentoSelecionado = null;
      this.orcamentoSelecionadoId = null;
      this.termoBuscaOrcamento = "";
      this.mostrarSugestoesOrcamento = false;
      this.orcamentosPagos = [];
      this.procedimentosFiltrados = [...(this.procedimentosLocal || [])];
      try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.destroyChoiceEl(el1); } catch (_) {}
      try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.destroyChoiceEl(el2); } catch (_) {}
      this.chaveProfissionalCriacao++;
      this.chaveProcedimentoCriacao++;
      this.modalAgendarVisivel = true;
      this.$nextTick(() => {
        try { const elInit1 = this.$refs.selProfissionalCriacao; if (elInit1) window.initChoiceEl(elInit1); } catch (_) {}
        try { const elInit2 = this.$refs.selProcedimentoCriacao; if (elInit2) window.initChoiceEl(elInit2); } catch (_) {}
      });
    },
    cliqueNoDiaNavegacao(date, jsEvent) {
      try {
        if (jsEvent && jsEvent.preventDefault) jsEvent.preventDefault();
        if (jsEvent && jsEvent.stopPropagation) jsEvent.stopPropagation();
      } catch (e) {}
      try {
        const ds = moment(date).format("YYYY-MM-DD");
        this.buscarAgendasPorData(ds);
      } catch (e) {}
    },
    /**
     * Modal open for edit event
     */
    async editarEvento(info) {
      this.eventoSelecionado = info.event;
      const start = this.eventoSelecionado.start ? moment(this.eventoSelecionado.start) : null;
      const ds = start && start.isValid() ? start.format("YYYY-MM-DD") : "";
      const hs = start && start.isValid() ? start.format("HH:mm") : "";
      try { if (window.pauseChoicesObserver) window.pauseChoicesObserver(); } catch (_) {}
      this.editAgendamentoForm.id = this.eventoSelecionado.id || null;
      this.editAgendamentoForm.data = ds;
      this.editAgendamentoForm.hora = hs;
      this.editAgendamentoForm.observacoes = (this.eventoSelecionado.extendedProps && this.eventoSelecionado.extendedProps.observacoes) ? this.eventoSelecionado.extendedProps.observacoes : "";
      this.editAgendamentoForm.status_id = null;
      const pacIdFromEP = this.eventoSelecionado?.extendedProps?.paciente_id ?? null;
      const procIdFromEP = this.eventoSelecionado?.extendedProps?.procedimento_id ?? null;
      const budgetIdFromEP = this.eventoSelecionado?.extendedProps?.orcamento_id ?? null;
      const title = String(this.eventoSelecionado.title || "");
      let pacNome = "";
      let procNome = "";
      if (title.includes("•")) {
        const parts = title.split("•");
        pacNome = String(parts[0] || "").trim();
        procNome = String(parts[1] || "").trim();
      } else {
        pacNome = title.trim();
        procNome = String(this.eventoSelecionado.extendedProps?.procedimento_nome || "").trim();
      }
      this.nomePacienteEdicao = pacNome;
      this.termoBuscaPacienteEdicao = pacNome;
      if (pacIdFromEP !== null && pacIdFromEP !== "") {
        this.editAgendamentoForm.paciente_id = Number(pacIdFromEP);
      } else {
        const pac = this.encontrarPacientePorNome(pacNome);
        this.editAgendamentoForm.paciente_id = pac ? pac.id : null;
      }
      this.editAgendamentoForm.profissional_saude_id = null;
      if (procIdFromEP !== null && procIdFromEP !== "") {
        this.editAgendamentoForm.procedimento_id = Number(procIdFromEP);
      } else {
        const proc = (this.procedimentosLocal || []).find(p => String(p.nome).trim() === procNome);
        this.editAgendamentoForm.procedimento_id = proc ? proc.id : null;
      }
      this.orcamentoSelecionadoEdicaoId = budgetIdFromEP || this.orcamentoSelecionadoEdicaoId;
      if (this.editandoNoModalDeCriacao && (budgetIdFromEP || this.orcamentoSelecionadoEdicaoId)) {
        this.orcamentoSelecionadoId = budgetIdFromEP || this.orcamentoSelecionadoEdicaoId;
      }
      this.procedimentoIdOriginalEdicao = this.editAgendamentoForm.procedimento_id || null;
      const scheduledProcIdInit = this.editAgendamentoForm.procedimento_id;
      if (scheduledProcIdInit != null) {
        const scheduledProcInit = (this.procedimentosLocal || []).find(p => String(p.id) === String(scheduledProcIdInit));
        this.procedimentosFiltradosEdicao = scheduledProcInit ? [{ ...scheduledProcInit, nome: `${scheduledProcInit.nome} (agendado)` }] : [];
      } else {
        this.procedimentosFiltradosEdicao = [];
      }
      this.chaveProcedimentoEdicao++;
      this.fecharPopoversCalendario();
      this.editandoNoModalDeCriacao = true;
      this.buscarOrcamentosPorPacienteEdicao();
      this.inferirProfissionalDaAgenda(ds, hs);
      this.agendamentoForm.paciente_id = this.editAgendamentoForm.paciente_id;
      this.agendamentoForm.profissional_saude_id = this.editAgendamentoForm.profissional_saude_id;
      this.agendamentoForm.procedimento_id = this.editAgendamentoForm.procedimento_id;
      this.agendamentoForm.data = this.editAgendamentoForm.data;
      this.agendamentoForm.hora = this.editAgendamentoForm.hora;
      this.agendamentoForm.status_id = this.editAgendamentoForm.status_id;
      this.agendamentoForm.valor_cobrado = this.editAgendamentoForm.valor_cobrado;
      this.agendamentoForm.observacoes = this.editAgendamentoForm.observacoes;
      this.termoBuscaPaciente = this.termoBuscaPacienteEdicao;
      this.procedimentosFiltrados = [...this.procedimentosFiltradosEdicao];
      this.orcamentosPagos = [...(this.orcamentosPagosEdicao || [])];
      try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.destroyChoiceEl(el1); } catch (_) {}
      try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.destroyChoiceEl(el2); } catch (_) {}
      this.chaveProfissionalCriacao++;
      this.chaveProcedimentoCriacao++;
      await this.$nextTick();
      try { const elInit1 = this.$refs.selProfissionalCriacao; if (elInit1) window.initChoiceEl(elInit1); } catch (_) {}
      try { const elInit2 = this.$refs.selProcedimentoCriacao; if (elInit2) window.initChoiceEl(elInit2); } catch (_) {}
      try { const elSync1 = this.$refs.selProfissionalCriacao; if (elSync1) window.syncChoiceValue(elSync1, String(this.agendamentoForm.profissional_saude_id ?? '')); } catch (_) {}
      try { const elSync2 = this.$refs.selProcedimentoCriacao; if (elSync2) window.syncChoiceValue(elSync2, String(this.agendamentoForm.procedimento_id ?? '')); } catch (_) {}
      try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) {}
      this.modalAgendarVisivel = true;
      try { this.selecionarOrcamentoCriacaoPorProcedimentoEdicao(); } catch (_) {}
    },

    fecharPopoversCalendario() {
      try {
        const pops = document.querySelectorAll(".fc-popover");
        pops.forEach(el => el.remove());
      } catch (e) {}
    },
    async buscarOrcamentosPorPacienteEdicao() {
      const pid = this.editAgendamentoForm.paciente_id;
      if (!pid) {
        const q = String(this.termoBuscaPacienteEdicao || "").trim();
        if (q) {
          this.buscandoOrcamentosPagosEdicao = true;
          try {
            await this.buscarOrcamentosPagosPorTextoEdicao(q);
          } catch (_) {
            this.orcamentosPagosEdicao = [];
          } finally {
            this.buscandoOrcamentosPagosEdicao = false;
          }
        } else {
          this.orcamentosPagosEdicao = [];
        }
        return;
      }
      this.carregandoOrcamentosPagosEdicao = true;
      this.buscandoOrcamentosPagosEdicao = true;
      try {
        const resp = await window.axios.get(`/pacientes/${pid}/orcamentos`);
        const arr = Array.isArray(resp?.data?.orcamentos) ? resp.data.orcamentos : [];
        const paid = arr.filter(o => !!o.pago);
        this.orcamentosPagosEdicao = paid;
        if (paid.length > 0) {
          let chosen = null;
          if (this.orcamentoSelecionadoEdicaoId) {
            chosen = paid.find(o => String(o.id) === String(this.orcamentoSelecionadoEdicaoId)) || null;
          }
          if (!chosen) chosen = paid[0];
          this.orcamentoSelecionadoEdicaoId = chosen.id;
          this.ignorarMudancaTermoOrcamentoEdicao = true;
          this.termoBuscaOrcamentoEdicao = String(chosen?.numero || "");
          this.mostrarSugestoesOrcamentoEdicao = false;
          await this.aoSelecionarOrcamentoEdicao(chosen);
          if (this.editandoNoModalDeCriacao) {
            this.orcamentosPagos = [...paid];
            this.orcamentoSelecionadoId = chosen.id;
            this.ignorarMudancaTermoOrcamento = true;
            this.termoBuscaOrcamento = String(chosen?.numero || "");
            this.mostrarSugestoesOrcamento = false;
            await this.aoSelecionarOrcamento(chosen);
          }
        } else {
          this.orcamentoSelecionadoEdicaoId = null;
          this.orcamentoSelecionadoEdicao = null;
          if (this.editandoNoModalDeCriacao) {
            this.orcamentosPagos = [];
            this.orcamentoSelecionadoId = null;
            this.orcamentoSelecionado = null;
            this.termoBuscaOrcamento = "";
          }
          this.termoBuscaOrcamentoEdicao = "";
        }
      } catch (e) {
        this.orcamentosPagosEdicao = [];
      } finally {
        this.carregandoOrcamentosPagosEdicao = false;
        this.buscandoOrcamentosPagosEdicao = false;
      }
    },
    async aoSelecionarOrcamentoEdicao(o) {
      try { if (window.pauseChoicesObserver) window.pauseChoicesObserver(); } catch (_) {}
      this.orcamentoSelecionadoEdicao = { ...o };
      this.orcamentoSelecionadoEdicaoId = o.id || null;
      this.ignorarMudancaTermoOrcamentoEdicao = true;
      this.termoBuscaOrcamentoEdicao = String(o?.numero || "");
      this.mostrarSugestoesOrcamentoEdicao = false;
      this.carregandoItensOrcamentoEdicao = true;
      this.editAgendamentoForm.paciente_id = o.paciente_id || this.editAgendamentoForm.paciente_id;
      this.editAgendamentoForm.profissional_saude_id = o.profissional_saude_id != null ? Number(o.profissional_saude_id) : this.editAgendamentoForm.profissional_saude_id;
      try { const el1 = this.$refs.selProfissionalEdicao; if (el1) window.destroyChoiceEl(el1); } catch (_) {}
      try {
        const pacNome = String(o?.paciente || "").trim();
        if (pacNome) {
          this.termoBuscaPacienteEdicao = pacNome;
        } else {
          const p = (this.pacientesLocal || []).find(px => String(px.id) === String(this.editAgendamentoForm.paciente_id));
          this.termoBuscaPacienteEdicao = p ? String(p.nome || "") : this.termoBuscaPacienteEdicao;
        }
        this.mostrarSugestoesPacienteEdicao = false;
      } catch (_) {}
      this.itensOrcamentoSelecionadoEdicao = [];
      this.itensOrcamentoPorProcedimentoEdicao = {};
      try {
        const res = await window.axios.get(`/orcamentos/${o.id}`);
        const itens = Array.isArray(res?.data?.itens) ? res.data.itens : [];
        try { this.orcamentoSelecionadoEdicao = { ...(this.orcamentoSelecionadoEdicao || {}), ...(res?.data?.orcamento || {}) }; } catch (_) {}
        try {
          const profId = res?.data?.orcamento?.profissional_saude_id ?? null;
          if (profId != null) this.editAgendamentoForm.profissional_saude_id = Number(profId);
        } catch (_) {}
        const ids = new Set(itens.map(it => it.procedimento_id));
        const filtered = (this.procedimentosLocal || []).filter(p => ids.has(p.id));
        const byProc = {};
        (itens || []).forEach(it => { byProc[String(it.procedimento_id)] = { valor_unitario: it.valor_unitario, valor_total: it.valor_total, quantidade: it.quantidade }; });
        this.itensOrcamentoPorProcedimentoEdicao = byProc;
        const scheduledProcId = this.procedimentoIdOriginalEdicao;
        let filteredList = filtered;
        if (scheduledProcId != null) {
          const scheduledProc = (this.procedimentosLocal || []).find(p => String(p.id) === String(scheduledProcId));
          if (scheduledProc && !ids.has(Number(scheduledProc.id))) {
            filteredList = [scheduledProc, ...filteredList];
          }
        }
        this.itensOrcamentoSelecionadoEdicao = [...itens];
        let nextProcId = this.editAgendamentoForm.procedimento_id;
        const scheduledInBudget = scheduledProcId != null && ids.has(Number(scheduledProcId));
        if (scheduledInBudget) {
          nextProcId = Number(scheduledProcId);
        } else {
          const first = (itens && itens.length) ? itens[0] : null;
          nextProcId = first ? first.procedimento_id : null;
        }
        this.editAgendamentoForm.procedimento_id = nextProcId;
        const pid = this.editAgendamentoForm.procedimento_id;
        if (pid && this.itensOrcamentoPorProcedimentoEdicao[String(pid)]) {
          const item = this.itensOrcamentoPorProcedimentoEdicao[String(pid)];
          const v = item?.valor_unitario ?? item?.valor_total ?? null;
          this.editAgendamentoForm.valor_cobrado = v != null ? String(Number(v).toFixed(2)) : "";
        } else {
          this.editAgendamentoForm.valor_cobrado = "";
        }
        const scheduledId = this.procedimentoIdOriginalEdicao != null ? String(this.procedimentoIdOriginalEdicao) : null;
        const labeled = (filteredList || []).map(p => {
          const isScheduled = scheduledId != null && String(p.id) === scheduledId;
          return isScheduled ? { ...p, nome: `${p.nome} (agendado)` } : p;
        });
        this.procedimentosFiltradosEdicao = labeled;
        try { const el2 = this.$refs.selProcedimentoEdicao; if (el2) window.destroyChoiceEl(el2); } catch (_) {}
        this.renderProcedimentoEdicao = false;
        this.chaveProcedimentoEdicao++;
        await this.$nextTick();
        this.renderProcedimentoEdicao = true;
        await this.$nextTick();
        try { const elInit1 = this.$refs.selProfissionalEdicao; if (elInit1) window.initChoiceEl(elInit1); } catch (_) {}
        try { const elInit2 = this.$refs.selProcedimentoEdicao; if (elInit2) window.initChoiceEl(elInit2); } catch (_) {}
        try { const elSync1 = this.$refs.selProfissionalEdicao; if (elSync1) window.syncChoiceValue(elSync1, String(this.editAgendamentoForm.profissional_saude_id ?? '')); } catch (_) {}
        try { const elSync2 = this.$refs.selProcedimentoEdicao; if (elSync2) window.syncChoiceValue(elSync2, String(this.editAgendamentoForm.procedimento_id ?? '')); } catch (_) {}
        this.mostrarDropdownOrcamentoEdicao = false;
        try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) {}
      } catch (e) {
        this.procedimentosFiltradosEdicao = [];
        this.itensOrcamentoSelecionadoEdicao = [];
        this.itensOrcamentoPorProcedimentoEdicao = {};
      } finally {
        this.carregandoItensOrcamentoEdicao = false;
        try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) {}
      }
    },
    aoAlterarProcedimentoEdicao() {
      const pid = this.editAgendamentoForm.procedimento_id;
      if (this.orcamentoSelecionadoEdicao && pid && this.itensOrcamentoPorProcedimentoEdicao && this.itensOrcamentoPorProcedimentoEdicao[String(pid)]) {
        const item = this.itensOrcamentoPorProcedimentoEdicao[String(pid)];
        const v = item?.valor_unitario ?? item?.valor_total ?? null;
        this.editAgendamentoForm.valor_cobrado = v != null ? String(Number(v).toFixed(2)) : "";
      }
    },
    async inferirProfissionalDaAgenda(ds, hs) {
      try {
        const d = ds || moment().format("YYYY-MM-DD");
        const resp = await window.axios.get("/agendas-medicas/by-date", { params: { data: d } });
        const arr = Array.isArray(resp?.data?.agendas) ? resp.data.agendas : [];
        const hhmm = String(hs || "").slice(0,5);
        const m = (t) => {
          const s = String(t || "").slice(0,5);
          const hh = Number(s.slice(0,2));
          const mm = Number(s.slice(3,5));
          return isNaN(hh) || isNaN(mm) ? null : hh*60+mm;
        };
        const mh = m(hhmm);
        let chosen = null;
        if (mh != null) {
          arr.forEach(a => {
            const mi = m(a.hora_inicio);
            const mf = m(a.hora_fim);
            if (mi == null || mf == null) return;
            if (mh >= mi && mh <= mf && chosen == null) {
              chosen = a.profissional_saude_id;
            }
          });
        }
        if (!chosen && arr.length > 0) {
          chosen = arr[0].profissional_saude_id;
        }
        this.editAgendamentoForm.profissional_saude_id = chosen != null ? Number(chosen) : this.editAgendamentoForm.profissional_saude_id;
      } catch (e) {}
    },
    async salvarEdicaoAgendamento() {
      const payload = {
        paciente_id: this.editAgendamentoForm.paciente_id,
        profissional_saude_id: this.editAgendamentoForm.profissional_saude_id,
        procedimento_id: this.editAgendamentoForm.procedimento_id,
        data: this.editAgendamentoForm.data,
        hora: this.editAgendamentoForm.hora,
        status_id: this.editAgendamentoForm.status_id,
        valor_cobrado: this.editAgendamentoForm.valor_cobrado ? Number(String(this.editAgendamentoForm.valor_cobrado).replace(/[^\d.,-]/g, '').replace(',', '.')) : null,
        observacoes: this.editAgendamentoForm.observacoes,
      };
      this.processandoEdicao = true;
      try {
        if (this.editAgendamentoForm.id) {
          try {
            await window.axios.put(`/agendamentos/${this.editAgendamentoForm.id}`, payload);
          } catch (e) {}
        }
        const api = this.$refs.fullCalendar?.getApi?.();
        const ev = api?.getEventById?.(this.editAgendamentoForm.id);
        if (ev) {
          if (this.editAgendamentoForm.data && this.editAgendamentoForm.hora) {
            ev.setStart(`${this.editAgendamentoForm.data}T${this.editAgendamentoForm.hora}:00`);
          }
          ev.setExtendedProp("observacoes", this.editAgendamentoForm.observacoes || "");
          const procNome = this.procedimentosLocal.find(pr => String(pr.id) === String(this.editAgendamentoForm.procedimento_id))?.nome || null;
          const pacNome = this.pacientesLocal.find(p => String(p.id) === String(this.editAgendamentoForm.paciente_id))?.nome || null;
          ev.setExtendedProp("procedimento_id", this.editAgendamentoForm.procedimento_id || null);
          ev.setExtendedProp("orcamento_id", this.orcamentoSelecionadoEdicaoId || this.orcamentoSelecionadoId || null);
          if (procNome) ev.setExtendedProp("procedimento_nome", procNome);
          if (pacNome || procNome) {
            const title = `${pacNome || ev.title.split("•")[0].trim()} • ${procNome || (ev.title.includes("•") ? ev.title.split("•")[1].trim() : "Procedimento")}`;
            ev.setProp("title", title);
          }
        }
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, success: "Agendamento atualizado" };
        } catch (_) {}
        this.modalEditarVisivel = false;
      } catch (e) {
        const msg = e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(' • ') : 'Falha ao atualizar';
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, error: String(msg || "Erro") };
        } catch (_) {}
      } finally {
        this.processandoEdicao = false;
      }
    },
    async cancelarAgendamento() {
      if (!this.editAgendamentoForm.id) return;
      this.modalCancelamento = true;
    },
    async confirmarCancelamentoAgendamento() {
      try {
        await window.axios.put(`/agendamentos/${this.editAgendamentoForm.id}/cancel`, { observacoes: this.editAgendamentoForm.observacoes || null });
        const api = this.$refs.fullCalendar?.getApi?.();
        const ev = api?.getEventById?.(this.editAgendamentoForm.id);
        if (ev) {
          ev.setExtendedProp("status", "Cancelado");
          ev.setProp("classNames", ["bg-danger-subtle"]);
        }
        await this.buscarUltimosAgendamentos();
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, success: "Agendamento cancelado" };
        } catch (_) {}
        this.modalCancelamento = false;
        this.modalEditarVisivel = false;
      } catch (e) {
        this.modalCancelamento = false;
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, error: "Falha ao cancelar" };
        } catch (_) {}
      }
    },

    /**
     * Show list of events
     */
    async buscarAgendasPorData(ds) {
      const data = ds || moment().format("YYYY-MM-DD");
      try {
        this.carregandoAgendas = true;
        this.agendasHoje = [];
        const resp = await window.axios.get("/agendas-medicas/by-date", { params: { data } });
        const arr = Array.isArray(resp?.data?.agendas) ? resp.data.agendas : [];
        this.agendasHoje = arr;
        this.dataAgendaSelecionada = data;
        this.classeFundoDataSelecionada = (arr && arr.length > 0) ? this.paletaMedicos[0].bg : null;
        this.coresDataSelecionada = (arr || []).map((_, idx) => this.paletaMedicos[idx % this.paletaMedicos.length].bg);
        this.verificarAgendaProfissionalParaDia();
        await this.buscarMapaDiasSemanaSelecionados();
      } catch (e) {
      } finally {
        this.carregandoAgendas = false;
      }
    },
    verificarAgendaProfissionalParaDia() {
      try {
        const pid = this.agendamentoForm?.profissional_saude_id ?? null;
        const has = pid != null && (this.agendasHoje || []).some(a => String(a.profissional_saude_id) === String(pid));
        if (pid != null && !has) {
          try {
            const fp = (this.$page?.props?.flash ?? {});
            this.$page.props.flash = { ...fp, warning: "Profissional não possui agenda para o dia selecionado." };
          } catch (_) {}
        }
      } catch (e) {}
    },
    obterTituloAgenda() {
      const today = moment().format("YYYY-MM-DD");
      if (!this.dataAgendaSelecionada || this.dataAgendaSelecionada === today) {
        return "Médicos com agenda para hoje";
      }
      return `Médicos com agenda para data ${moment(this.dataAgendaSelecionada).format("DD/MM/YYYY")}`;
    },
    diaInteiro(ag) {
      const hi = typeof ag?.hora_inicio === "string" ? ag.hora_inicio.slice(0,5) : null;
      const hf = typeof ag?.hora_fim === "string" ? ag.hora_fim.slice(0,5) : null;
      return hi === "00:00" && hf === "23:59";
    },
    formatarIntervaloAgenda(ag) {
      const hi = typeof ag?.hora_inicio === "string" ? ag.hora_inicio.slice(0,5) : "";
      const hf = typeof ag?.hora_fim === "string" ? ag.hora_fim.slice(0,5) : "";
      return `${hi} às ${hf}`;
    },
    aplicarTodasListras() {
      try {
        if (!this.agendasHoje || this.agendasHoje.length === 0) {
          this.limparListras();
          return;
        }
        const api = this.$refs.fullCalendar?.getApi?.();
        const viewType = api?.view?.type || "dayGridMonth";
        const ds = this.dataAgendaSelecionada || moment().format("YYYY-MM-DD");
        this.limparListras();
        if (viewType === "dayGridMonth") {
          const cells = document.querySelectorAll(".fc-daygrid-day");
          cells.forEach(el => {
            const d = el.getAttribute("data-date");
            if (!d) return;
            const weekday = moment(d).day();
            const colors = this.coresDoDiaSemanaSelecionado(weekday);
            this.criarListrasParaData(d, colors);
          });
        } else {
          if (viewType === "timeGridWeek") {
            this.aplicarListrasHorarioSemana();
          } else if (viewType === "timeGridDay") {
            this.aplicarListrasHorarioDia(ds);
          } else {
            const colorsSel = this.coresDataSelecionada || [];
            this.criarListrasParaData(ds, colorsSel);
          }
        }
      } catch (e) {}
    },
    limparListras() {
      try {
        const overlays = document.querySelectorAll(".agenda-stripes");
        overlays.forEach(el => el.remove());
      } catch (e) {}
    },
    criarListrasParaData(dateStr, bgClasses) {
      try {
        const frame = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"] .fc-daygrid-day-frame`);
        if (!frame) return;
        frame.style.position = "relative";
        const count = (bgClasses || []).length;
        if (!count) return;
        const alpha = this.calcularIntensidadeAlpha(count);
        let overlay = frame.querySelector(".agenda-stripes");
        if (!overlay) {
          overlay = document.createElement("div");
          overlay.className = "agenda-stripes";
          overlay.style.position = "absolute";
          overlay.style.top = "0";
          overlay.style.right = "0";
          overlay.style.bottom = "0";
          overlay.style.left = "0";
          overlay.style.pointerEvents = "none";
          overlay.style.zIndex = "0";
          try { frame.insertAdjacentElement("afterbegin", overlay); } catch (_) { frame.appendChild(overlay); }
        }
        overlay.innerHTML = "";
        const n = Math.max(1, Math.min(bgClasses.length || 0, 4));
        const classes = (bgClasses || []).slice(0, n);
        classes.forEach((cls, idx) => {
          const stripe = document.createElement("div");
          stripe.className = `agenda-stripe ${cls}`;
          stripe.style.position = "absolute";
          stripe.style.top = "0";
          stripe.style.bottom = "0";
          stripe.style.left = `${(idx * 100) / n}%`;
          stripe.style.width = `${100 / n}%`;
          stripe.style.opacity = String(alpha);
          overlay.appendChild(stripe);
        });
      } catch (e) {}
    },
    aoMontarVisao() {
      this.$nextTick(() => this.aplicarTodasListras());
    },
    aoDefinirDatas(info) {
      try {
        const d = info?.view?.currentStart || info?.start || new Date();
        const ds = moment(d).format("YYYY-MM-DD");
        this.buscarAgendasPorData(ds);
      } catch (e) {
        this.aplicarTodasListras();
      }
    },
    async buscarMapaDiasSemanaSelecionados() {
      try {
        const ids = (this.agendasHoje || []).map(a => a.profissional_saude_id).filter(v => v != null);
        if (!ids.length) {
          this.mapaDiasSemanaSelecionados = {};
          this.aplicarTodasListras();
          return;
        }
        const resp = await window.axios.get("/agendas-medicas/weekday-by-doctors", { params: { ids } });
        const map = resp?.data?.weekday_map || {};
        this.mapaDiasSemanaSelecionados = map;
      } catch (e) {
        this.mapaDiasSemanaSelecionados = {};
      } finally {
        this.aplicarTodasListras();
      }
    },
    corDoMedicoSelecionado(id) {
      const arr = this.agendasHoje || [];
      const idx = arr.findIndex(a => String(a.profissional_saude_id) === String(id));
      if (idx < 0) return null;
      return (this.coresDataSelecionada || [])[idx % this.paletaMedicos.length] || null;
    },
    coresDoDiaSemanaSelecionado(weekday) {
      const ids = this.mapaDiasSemanaSelecionados?.[weekday] || [];
      const colors = [];
      ids.forEach(id => {
        const c = this.corDoMedicoSelecionado(id);
        if (c) colors.push(c);
      });
      return colors;
    },
    calcularIntensidadeAlpha(count) {
      return 0.7;
    },
    parseMinutosHora(hhmm) {
      const s = String(hhmm || "").slice(0,5);
      const hh = Number(s.slice(0,2));
      const mm = Number(s.slice(3,5));
      if (isNaN(hh) || isNaN(mm)) return 0;
      return hh * 60 + mm;
    },
    async buscarAgendasPorDataSomente(ds) {
      try {
        const resp = await window.axios.get("/agendas-medicas/by-date", { params: { data: ds } });
        const arr = Array.isArray(resp?.data?.agendas) ? resp.data.agendas : [];
        return arr;
      } catch (e) {
        return [];
      }
    },
    async aplicarListrasHorarioSemana() {
      try {
        const api = this.$refs.fullCalendar?.getApi?.();
        const start = moment(api?.view?.currentStart).startOf("day");
        const end = moment(api?.view?.currentEnd).startOf("day");
        const days = [];
        let cur = start.clone();
        while (cur.isBefore(end)) {
          days.push(cur.format("YYYY-MM-DD"));
          cur.add(1, "day");
        }
        const fetches = await Promise.all(days.map(async d => {
          if (!this.mapaAgendasSemana[d]) {
            const arr = await this.buscarAgendasPorDataSomente(d);
            this.mapaAgendasSemana[d] = arr;
          }
          return { d, arr: this.mapaAgendasSemana[d] || [] };
        }));
        fetches.forEach(({ d, arr }) => {
          this.criarListrasParaDataSemana(d, arr);
        });
      } catch (e) {}
    },
    async aplicarListrasHorarioDia(ds) {
      try {
        const d = ds || moment().format("YYYY-MM-DD");
        const arr = await this.buscarAgendasPorDataSomente(d);
        this.criarListrasParaDataSemana(d, arr);
      } catch (e) {}
    },
    criarListrasParaDataSemana(dateStr, agendas) {
      try {
        if (!this.coresDataSelecionada || this.coresDataSelecionada.length === 0) return;
        const col = document.querySelector(`.fc-timegrid-col[data-date="${dateStr}"]`);
        const frame = col ? col.querySelector(".fc-timegrid-col-frame") : null;
        const slots = col ? col.querySelector(".fc-timegrid-slots") : null;
        if (!frame) return;
        frame.style.position = "relative";
        const h = (slots || frame).offsetHeight || 0;
        const allColors = [];
        (agendas || []).forEach(ag => {
          const c = this.corDoMedicoSelecionado(ag?.profissional_saude_id);
          if (c && !allColors.includes(c)) allColors.push(c);
        });
        const n = Math.max(1, Math.min(allColors.length || 0, 4));
        let overlay = frame.querySelector(".agenda-stripes");
        if (!overlay) {
          overlay = document.createElement("div");
          overlay.className = "agenda-stripes";
          overlay.style.position = "absolute";
          overlay.style.top = "0";
          overlay.style.right = "0";
          overlay.style.bottom = "0";
          overlay.style.left = "0";
          overlay.style.pointerEvents = "none";
          overlay.style.zIndex = "0";
          try { frame.insertAdjacentElement("afterbegin", overlay); } catch (_) { frame.appendChild(overlay); }
        }
        overlay.innerHTML = "";
        (agendas || []).forEach(ag => {
          const c = this.corDoMedicoSelecionado(ag?.profissional_saude_id);
          if (!c) return;
          const idx = Math.max(0, allColors.indexOf(c));
          const left = `${(idx * 100) / (n || 1)}%`;
          const width = `${100 / (n || 1)}%`;
          const hi = String(ag?.hora_inicio || "00:00").slice(0,5);
          const hf = String(ag?.hora_fim || "23:59").slice(0,5);
          const mStart = this.parseMinutosHora(hi);
          const mEnd = this.parseMinutosHora(hf);
          const top = `${(mStart / (24 * 60)) * h}px`;
          const height = `${((Math.max(mEnd, mStart) - mStart) / (24 * 60)) * h}px`;
          const stripe = document.createElement("div");
          stripe.className = `agenda-stripe ${c}`;
          stripe.style.position = "absolute";
          stripe.style.left = left;
          stripe.style.width = width;
          stripe.style.top = top;
          stripe.style.height = height;
          stripe.style.opacity = String(this.calcularIntensidadeAlpha(1));
          overlay.appendChild(stripe);
        });
      } catch (e) {}
    },
    async aoSelecionarOrcamento(o) {
      this.orcamentoSelecionado = { ...o };
      this.orcamentoSelecionadoId = o.id || null;

      this.agendamentoForm.paciente_id =
        o.paciente_id || this.agendamentoForm.paciente_id;

      this.agendamentoForm.profissional_saude_id =
        o.profissional_saude_id || this.agendamentoForm.profissional_saude_id;
      try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.destroyChoiceEl(el1); } catch (e) {}
      this.chaveProfissionalCriacao++;

      try {
        const pacNome = String(o?.paciente || "").trim();
        if (pacNome) {
          this.termoBuscaPaciente = pacNome;
        } else {
          const p = (this.pacientesLocal || []).find(px => String(px.id) === String(this.agendamentoForm.paciente_id));
          this.termoBuscaPaciente = p ? String(p.nome || "") : this.termoBuscaPaciente;
        }
        this.mostrarSugestoesPaciente = false;
      } catch (_) {}

      // ❌ REMOVIDO (CAUSAVA O BUG)
      // this.orcamentosPagos = [];

      try {
        const res = await window.axios.get(`/orcamentos/${o.id}`);
        const itens = Array.isArray(res?.data?.itens)
          ? res.data.itens
          : [];

        this.itensOrcamentoSelecionado = itens;

        const byProc = {};
        itens.forEach(it => {
          byProc[it.procedimento_id] = {
            valor_unitario: it.valor_unitario,
            valor_total: it.valor_total,
            quantidade: it.quantidade,
          };
        });

        this.itensOrcamentoPorProcedimento = byProc;

        const ids = new Set(itens.map(it => it.procedimento_id));
        const filtered = (this.procedimentosLocal || []).filter(p => ids.has(p.id));
        this.procedimentosFiltrados = filtered;

        let chosenProcId = this.agendamentoForm.procedimento_id;
        const hasChosenInBudget = chosenProcId != null && ids.has(Number(chosenProcId));
        if (!hasChosenInBudget) {
          const first = itens[0] || null;
          chosenProcId = first ? first.procedimento_id : null;
        }
        this.agendamentoForm.procedimento_id = chosenProcId;
        try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.destroyChoiceEl(el2); } catch (e) {}
        this.chaveProcedimentoCriacao++;
        const pid = this.agendamentoForm.procedimento_id;
        if (pid && this.itensOrcamentoPorProcedimento[String(pid)]) {
          const item = this.itensOrcamentoPorProcedimento[String(pid)];
          const v = item?.valor_unitario ?? item?.valor_total ?? null;
          this.agendamentoForm.valor_cobrado = v != null ? String(Number(v).toFixed(2)) : this.agendamentoForm.valor_cobrado;
        }
        await this.$nextTick();
        try { const elInit1 = this.$refs.selProfissionalCriacao; if (elInit1) window.initChoiceEl(elInit1); } catch (e) {}
        try { const elInit2 = this.$refs.selProcedimentoCriacao; if (elInit2) window.initChoiceEl(elInit2); } catch (e) {}
        try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.syncChoiceValue(el1, String(this.agendamentoForm.profissional_saude_id ?? '')); } catch (e) {}
        try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.syncChoiceValue(el2, String(this.agendamentoForm.procedimento_id ?? '')); } catch (e) {}
      } catch {
        this.itensOrcamentoSelecionado = [];
        this.itensOrcamentoPorProcedimento = {};
        this.procedimentosFiltrados = [...(this.procedimentosLocal || [])];
      }
      try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) {}
    },
    aoAlterarProcedimento() {
      const pid = this.agendamentoForm.procedimento_id;
      if (this.orcamentoSelecionado && pid && this.itensOrcamentoPorProcedimento && this.itensOrcamentoPorProcedimento[String(pid)]) {
        const item = this.itensOrcamentoPorProcedimento[String(pid)];
        const v = item?.valor_unitario ?? item?.valor_total ?? null;
        this.agendamentoForm.valor_cobrado = v != null ? String(Number(v).toFixed(2)) : "";
      } else {
        // mantém valor atual quando não há orçamento
      }
    },
    async salvarAgendamento() {
      const payload = {
        paciente_id: this.agendamentoForm.paciente_id,
        profissional_saude_id: this.agendamentoForm.profissional_saude_id,
        procedimento_id: this.agendamentoForm.procedimento_id,
        orcamento_id: this.orcamentoSelecionadoId || null,
        data: this.agendamentoForm.data,
        hora: this.agendamentoForm.hora,
        status_id: this.agendamentoForm.status_id,
        valor_cobrado: this.agendamentoForm.valor_cobrado ? Number(String(this.agendamentoForm.valor_cobrado).replace(/[^\d.,-]/g, '').replace(',', '.')) : null,
        observacoes: this.agendamentoForm.observacoes,
      };
      this.processandoCriacao = true;
      try {
        const resp = await window.axios.post("/agendamentos", payload);
        const ag = resp?.data?.agendamento;
        if (ag) {
          const procNome = (this.procedimentosLocal.find(pr => String(pr.id) === String(ag.procedimento_id))?.nome || 'Procedimento');
          const pacNome = (this.pacientesLocal.find(p => String(p.id) === String(ag.paciente_id))?.nome || 'Paciente');
          const title = `${pacNome} • ${procNome}`;
          const calendarApi = this.$refs.fullCalendar.getApi();
          calendarApi.addEvent({
            id: ag.id,
            title,
            start: `${ag.data}T${ag.hora}:00`,
            allDay: false,
            className: "bg-success-subtle",
            classNames: ["bg-success-subtle"],
            extendedProps: { paciente_id: ag.paciente_id, procedimento_id: ag.procedimento_id, procedimento_nome: procNome, observacoes: this.agendamentoForm.observacoes || "", orcamento_id: payload.orcamento_id || null }
          });
          try { calendarApi.gotoDate(ag.data); } catch (e) {}
        }
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, success: "Agendamento criado" };
        } catch (_) {}
        this.modalAgendarVisivel = false;
        this.agendamentoForm = { paciente_id: null, profissional_saude_id: null, procedimento_id: null, data: "", hora: "", status_id: null, valor_cobrado: "", observacoes: "" };
        this.orcamentoSelecionado = null;
        this.orcamentoSelecionadoId = null;
        this.itensOrcamentoSelecionado = [];
        this.itensOrcamentoPorProcedimento = {};
        this.procedimentosFiltrados = [...(this.procedimentosLocal || [])];
        await this.buscarUltimosAgendamentos();
      } catch (e) {
        const msg = e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(' • ') : 'Falha ao agendar';
        try {
          const fp = (this.$page?.props?.flash ?? {});
          this.$page.props.flash = { ...fp, error: String(msg || "Falha ao agendar") };
        } catch (_) {}
      } finally {
        this.processandoCriacao = false;
      }
    },
    async salvarAgendarOuEditar() {
      if (this.editandoNoModalDeCriacao) {
        this.editAgendamentoForm.paciente_id = this.agendamentoForm.paciente_id;
        this.editAgendamentoForm.profissional_saude_id = this.agendamentoForm.profissional_saude_id;
        this.editAgendamentoForm.procedimento_id = this.agendamentoForm.procedimento_id;
        this.editAgendamentoForm.data = this.agendamentoForm.data;
        this.editAgendamentoForm.hora = this.agendamentoForm.hora;
        this.editAgendamentoForm.status_id = this.agendamentoForm.status_id;
        this.editAgendamentoForm.valor_cobrado = this.agendamentoForm.valor_cobrado;
        this.editAgendamentoForm.observacoes = this.agendamentoForm.observacoes;
        await this.salvarEdicaoAgendamento();
        this.editandoNoModalDeCriacao = false;
        this.modalAgendarVisivel = false;
        return;
      }
      await this.salvarAgendamento();
    },
    async selecionarOrcamentoCriacaoPorProcedimentoEdicao() {
      try { if (window.pauseChoicesObserver) window.pauseChoicesObserver(); } catch (_) {}
      try {
        const scheduledId = this.procedimentoIdOriginalEdicao != null ? Number(this.procedimentoIdOriginalEdicao) : null;
        const list = Array.isArray(this.orcamentosPagosEdicao) ? this.orcamentosPagosEdicao : [];
        let chosen = null;
        for (const o of list) {
          try {
            const res = await window.axios.get(`/orcamentos/${o.id}`);
            const itens = Array.isArray(res?.data?.itens) ? res.data.itens : [];
            if (scheduledId != null && itens.some(it => Number(it.procedimento_id) === Number(scheduledId))) {
              chosen = { o, itens };
              break;
            }
            if (!chosen) chosen = { o, itens };
          } catch (_) {}
        }
        if (chosen && chosen.o) {
          this.orcamentoSelecionadoId = chosen.o.id;
          this.termoBuscaOrcamento = String(chosen.o?.numero || "");
          await this.aoSelecionarOrcamento(chosen.o);
        }
      } catch (e) {}
      try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) {}
    },
    async buscarUltimosAgendamentos() {
      try {
        const resp = await window.axios.get("/agendamentos/latest", { params: { limit: 20 } });
        const arr = Array.isArray(resp?.data?.agendamentos) ? resp.data.agendamentos : [];
        const events = arr.map(r => {
          const ds = String(r.data || "");
          const hs = String(r.hora || "00:00").slice(0, 5);
          let startMoment = moment(`${ds} ${hs}`, "YYYY-MM-DD HH:mm", true);
          if (!startMoment.isValid()) startMoment = moment(ds, "YYYY-MM-DD", true);
          const start = startMoment.isValid() ? startMoment.toDate() : null;
          const title = `${r.paciente || ""}`.trim();
          const st = String(r.status || "").toLowerCase();
          const cls = st.includes("cancel") ? "bg-danger-subtle"
            : (st.includes("conclu") ? "bg-success-subtle"
            : (st.includes("pend") ? "bg-warning-subtle" : "bg-primary-subtle"));
          return {
            id: r.id,
            title,
            start,
            classNames: [cls],
            extendedProps: { paciente_id: r.paciente_id, procedimento_id: r.procedimento_id, procedimento_nome: r.procedimento || "", status: r.status || "", observacoes: r.observacoes || "", createdEm: r.criado_em || "" }
          };
        });
        this.eventosAtuais = events;
        this.sincronizarCalendarioComUltimos(events);
      } catch (e) {
        this.eventosAtuais = [];
      }
    },
    sincronizarCalendarioComUltimos(events) {
      try {
        const api = this.$refs.fullCalendar?.getApi?.();
        if (!api) return;
        api.removeAllEvents();
        (events || []).forEach(ev => {
          if (!ev || !ev.id || !ev.start) return;
          api.addEvent({
            id: ev.id,
            title: ev.title,
            start: ev.start,
            allDay: false,
            classNames: ev.classNames || ["bg-primary-subtle"],
            extendedProps: ev.extendedProps || {}
          });
        });
      } catch (e) {}
    },
    obterTextoBadgeEvento(event) {
      try {
        const fs = this.formatarHora(event?.start);
        const fe = this.formatarHora(event?.end);
        if (!fs || !fe || fs === fe) {
          const cm = event?.extendedProps?.createdEm || "";
          return cm ? `Criado em ${cm}` : "Criado em";
        }
        return `${fs} - ${fe}`;
      } catch (e) {
        return "Criado em";
      }
    },
    obterNomeProcedimentoEvento(event) {
      try {
        const ep = event?.extendedProps || {};
        if (ep?.procedimento_nome) return ep.procedimento_nome;
        const t = String(event?.title || "");
        if (t.includes("•")) {
          const parts = t.split("•");
          return String(parts[1] || "").trim() || "N.A.";
        }
        return ep?.description || "N.A.";
      } catch (e) {
        return "N.A.";
      }
    },
  },
};
</script>

<template>
  <Layout>
    <PageHeader title="Agendamento" pageTitle="Apps" />

    <BRow>
      <BCol cols="12">
        <BRow>
          <BCol xl="3">
            <BCard no-body class="card-h-100">
              <BCardBody>
                <BButton variant="primary" class="w-100" id="btn-new-event" @click="abrirNovoAgendamento">
                  <i class="mdi mdi-plus"></i> Novo Agendamento
                </BButton>

                <div id="external-events">
                  <br />
                  <p class="text-muted">{{ obterTituloAgenda() }}</p>
                  <div v-if="carregandoAgendas" class="placeholder-wave">
                    <div class="external-event fc-event bg-light-subtle text-muted mb-2">
                      <span class="placeholder col-7 rounded"></span>
                    </div>
                    <div class="external-event fc-event bg-light-subtle text-muted mb-2">
                      <span class="placeholder col-5 rounded"></span>
                    </div>
                    <div class="external-event fc-event bg-light-subtle text-muted">
                      <span class="placeholder col-8 rounded"></span>
                    </div>
                  </div>
                  <template v-else>
                    <div v-if="agendasHoje.length === 0" class="text-muted small mb-2">Nenhum profissional com agenda nesta data.</div>
                    <div
                      v-for="(ag, idx) in agendasHoje"
                      :key="`${ag.profissional_saude_id}-${idx}`"
                      class="external-event fc-event d-flex align-items-center"
                      :class="[paletaMedicos[idx % paletaMedicos.length].bg, paletaMedicos[idx % paletaMedicos.length].text]"
                      :data-class="paletaMedicos[idx % paletaMedicos.length].bg"
                    >
                      <i class="mdi mdi-checkbox-blank-circle me-2"></i>
                      <div class="flex-grow-1" style="min-width:0">
                        <div class="d-flex align-items-center" style="min-width:0">
                          <span class="text-truncate flex-grow-1" :title="ag.nome">{{ ag.nome }}</span>
                          <span v-if="!diaInteiro(ag)" class="ms-2 small text-muted text-nowrap">({{ formatarIntervaloAgenda(ag) }})</span>
                        </div>
                        <div v-if="ag.especialidades" class="text-muted small text-truncate">{{ ag.especialidades }}</div>
                      </div>
                    </div>
                  </template>
                </div>
              </BCardBody>
            </BCard>
            <div>
              <h5 class="mb-1">Últimos agendamentos</h5>
              <p class="text-muted">Acompanhe os últimos agendamentos</p>
              <simpleBar class="upcoming-events pe-2 me-n1 mb-3" data-simplebar="init" style="height: 400px">
                <BCard no-body class="mb-3" v-for="event in eventosAtuais" :key="event.id">
                  <BCardBody>
                    <div class="d-flex mb-3">
                      <div class="flex-grow-1">
                        <i :class="`mdi mdi-checkbox-blank-circle me-2 ${event.classNames}`"></i><span
                          class="fw-medium">{{ formatarData(event.start) }}</span>
                      </div>
                      <div class="flex-shrink-0">
                        <span class="fs-10 text-muted ms-auto">{{ obterTextoBadgeEvento(event) }}</span>
                      </div>
                    </div>
                    <h6 class="card-title fs-16">{{ event.title }}</h6>
                    <p class="text-muted text-truncate-two-lines mb-0">{{ obterNomeProcedimentoEvento(event) }}</p>
                  </BCardBody>
                </BCard>
              </simpleBar>
            </div>
            <BCard no-body>
              <BCardBody class="bg-info-subtle">
                <div class="d-flex">
                  <div class="flex-shrink-0">
                    <CalendarIcon class="text-info icon-dual-info"></CalendarIcon>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="fs-15">Welcome to your Calendar!</h6>
                    <p class="text-muted mb-0">
                      Event that applications book will appear here. Click on an
                      event to see the details and manage applicants event.
                    </p>
                  </div>
                </div>
              </BCardBody>
            </BCard>
          </BCol>
          <BCol xl="9">
            <BCard no-body class="card-h-100">
              <BCardBody>
                <FullCalendar ref="fullCalendar" :options="opcoesCalendario" />
              </BCardBody>
            </BCard>
          </BCol>
        </BRow>
        <div style="clear: both"></div>
      </BCol>
    </BRow>

    <Modal :modelValue="modalAgendarVisivel" :title="editandoNoModalDeCriacao ? 'Editar Agendamento' : 'Novo Agendamento'" :nameButton="editandoNoModalDeCriacao ? 'Salvar' : 'Agendar'" :processing="editandoNoModalDeCriacao ? processandoEdicao : processandoCriacao" size="lg"
           @update:modelValue="modalAgendarVisivel = $event" @save="salvarAgendarOuEditar">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Paciente</label>
          <SuggestInput
            v-model="termoBuscaPaciente"
            :suggestions="pacientesSugestoesCriacao"
            :loading="false"
            :show="mostrarSugestoesPaciente"
            :disabled="!!orcamentoSelecionado"
            placeholder="Buscar paciente por nome ou CPF"
            keyPrefix="sug-pac"
            primaryTextProp="nome"
            secondaryTextProp="cpf"
            @focus="mostrarSugestoesPaciente = true"
            @blur="onBlurSugestoesPaciente"
            @select="selecionarSugestaoPaciente"
          />
        </div>
        <div class="col-md-6">
          <label class="form-label">Orçamento Pago</label>
          <SuggestInput
            v-model="termoBuscaOrcamento"
            :suggestions="orcamentosPagos"
            :loading="buscandoOrcamentosPagos"
            :show="mostrarSugestoesOrcamento"
            placeholder="Buscar orçamento por número ou paciente"
            keyPrefix="sug"
            primaryTextProp="numero"
            secondaryTextProp="paciente"
            @focus="mostrarSugestoesOrcamento = true"
            @blur="onBlurSugestoesOrcamento"
            @select="selecionarSugestaoOrcamento"
          />
          <!-- <div class="text-muted small mt-1" v-if="orcamentoSelecionado">Selecionado: {{ orcamentoSelecionado.numero }}</div> -->
        </div>
        <div class="col-md-6">
          <label class="form-label">Profissional</label>
          <select :key="chaveProfissionalCriacao" ref="selProfissionalCriacao" v-model="agendamentoForm.profissional_saude_id" data-choices class="form-select" :disabled="!!orcamentoSelecionado">
            <option v-for="d in listaProfissionaisCriacao" :key="d.id" :value="d.id">{{ d.nome }}</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Procedimento</label>
          <select :key="chaveProcedimentoCriacao" ref="selProcedimentoCriacao" v-model="agendamentoForm.procedimento_id" data-choices class="form-select" @change="aoAlterarProcedimento">
            <option v-for="p in procedimentosFiltrados" :key="p.id" :value="p.id">{{ p.nome }}</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Data</label>
          <flatPickr v-model="agendamentoForm.data" class="form-control" :config="opcoesFlatpickrData" placeholder="Selecione a data" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Hora</label>
          <flatPickr v-model="agendamentoForm.hora" class="form-control" :config="opcoesFlatpickrHora" placeholder="Selecione a hora" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select v-model="agendamentoForm.status_id" class="form-select">
            <option :value="null">Selecione</option>
            <option v-for="s in opcoesStatus" :key="s.id" :value="s.id">{{ s.descricao }}</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Valor Cobrado</label>
          <input v-model="agendamentoForm.valor_cobrado" type="text" class="form-control" placeholder="0,00" :disabled="!!orcamentoSelecionado" />
        </div>
        <div class="col-md-12">
          <label class="form-label">Observações</label>
          <textarea v-model="agendamentoForm.observacoes" class="form-control" rows="3" maxlength="500" placeholder="Anotações gerais"></textarea>
        </div>
      </div>
    </Modal>

    <Modal :modelValue="modalCancelamento" title="Cancelar agendamento" nameButton="Sim, cancelar"
           @update:modelValue="modalCancelamento = $event" @save="confirmarCancelamentoAgendamento"
           :ZIndex="1060" :backdropZIndex="1055">
      <p>Cancelar agendamento? Esta ação marcará o agendamento como cancelado.</p>
    </Modal>

    <Modal :modelValue="modalEventosDiaVisivel"
           :title="tituloModalEventosDia"
           :nameButton="'Fechar'"
           :processing="false"
           size="lg"
           @update:modelValue="modalEventosDiaVisivel = $event">
      <TableGrid
        :columns="[{ id: 'id', name: 'ID' }, { id: 'paciente', name: 'Paciente' }, { id: 'procedimento', name: 'Procedimento' }, { id: 'medico', name: 'Médico' }, { id: 'hora', name: 'Hora' }, { id: 'status', name: 'Status' }]"
        :data="eventosDiaGrid()"
        :tableTitle="'Lista de agendamentos'"
        :search="true"
        :showCheckbox="false"
        :showAddButton="false"
        :showStatus="false"
        :showActions="true"
        :compactSpacing="true"
        :actionsConfig="{ delete: true, edit: false, show: false, diary: false, print: false, download: false }"
        :actionsLabels="{ delete: 'Cancelar' }"
        :actionsButtonText="{ delete: 'Cancelar' }"
        :actionsIcons="{ delete: 'ri-close-line' }"
        @delete="cancelarAgendamentoPorId"
      />
    </Modal>

    <Modal :modelValue="modalEditarVisivel" title="Editar Agendamento" nameButton="Salvar" :processing="processandoEdicao" size="lg"
           @update:modelValue="modalEditarVisivel = $event" @save="salvarEdicaoAgendamento">
      <div :key="chaveRenderizacaoModalEvento" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Paciente</label>
          <SuggestInput
            v-model="termoBuscaPacienteEdicao"
            :suggestions="pacientesSugestoesEdicao"
            :loading="false"
            :show="mostrarSugestoesPacienteEdicao"
            :disabled="!!orcamentoSelecionadoEdicao"
            placeholder="Buscar paciente por nome ou CPF"
            keyPrefix="sug-pac-edit"
            primaryTextProp="nome"
            secondaryTextProp="cpf"
            @focus="mostrarSugestoesPacienteEdicao = true"
            @blur="onBlurSugestoesPacienteEdicao"
            @select="selecionarSugestaoPacienteEdicao"
          />
        </div>
        <div class="col-md-6">
          <label class="form-label">Orçamento Pago</label>
          <SuggestInput
            v-model="termoBuscaOrcamentoEdicao"
            :suggestions="orcamentosPagosEdicao"
            :loading="buscandoOrcamentosPagosEdicao"
            :show="mostrarSugestoesOrcamentoEdicao"
            placeholder="Buscar orçamento por número ou paciente"
            keyPrefix="sug-edit"
            primaryTextProp="numero"
            secondaryTextProp="paciente"
            @focus="mostrarSugestoesOrcamentoEdicao = true"
            @blur="onBlurSugestoesOrcamentoEdicao"
            @select="selecionarSugestaoOrcamentoEdicao"
          />
          <!-- <div class="text-muted small mt-1" v-if="orcamentoSelecionadoEdicao">Selecionado: {{ orcamentoSelecionadoEdicao.numero }}</div> -->
        </div>
        <div class="col-md-6">
          <label class="form-label">Profissional</label>
          <select :key="orcamentoSelecionadoEdicao ? orcamentoSelecionadoEdicao.id : 'none'" ref="selProfissionalEdicao" v-model="editAgendamentoForm.profissional_saude_id" data-choices class="form-select" :disabled="!!orcamentoSelecionadoEdicao">
            <option v-for="d in listaProfissionaisEdicao" :key="d.id" :value="d.id">{{ d.nome }}</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Procedimento</label>
          <select v-if="renderProcedimentoEdicao" :key="chaveProcedimentoEdicao" ref="selProcedimentoEdicao" v-model="editAgendamentoForm.procedimento_id" data-choices class="form-select" @change="aoAlterarProcedimentoEdicao" :disabled="carregandoItensOrcamentoEdicao">
            <option v-for="p in procedimentosFiltradosEdicao" :key="p.id" :value="p.id">{{ p.nome }}</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Data</label>
          <flatPickr v-model="editAgendamentoForm.data" class="form-control" :config="opcoesFlatpickrData" placeholder="Selecione a data" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Hora</label>
          <flatPickr v-model="editAgendamentoForm.hora" class="form-control" :config="opcoesFlatpickrHora" placeholder="Selecione a hora" />
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select v-model="editAgendamentoForm.status_id" class="form-select">
            <option :value="null">Selecione</option>
            <option v-for="s in opcoesStatus" :key="s.id" :value="s.id">{{ s.descricao }}</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Valor Cobrado</label>
          <input v-model="editAgendamentoForm.valor_cobrado" type="text" class="form-control" placeholder="0,00" :disabled="!!orcamentoSelecionadoEdicao" />
        </div>
        <div class="col-md-12">
          <label class="form-label">Observações</label>
          <textarea v-model="editAgendamentoForm.observacoes" class="form-control" rows="3" maxlength="500" placeholder="Anotações gerais"></textarea>
        </div>
      </div>
      <template #extraFooterLeft>
        <button type="button" class="btn btn-outline-danger" @click="cancelarAgendamento">Cancelar agendamento</button>
      </template>
    </Modal>
  </Layout>
</template>
<style>
.fc-theme-standard { --fc-today-bg-color: transparent; }
.fc .fc-day-today { background-color: transparent !important; }
.fc .fc-daygrid-day.fc-day-today,
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-frame,
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-bg,
.fc .fc-timegrid-col.fc-day-today,
.fc .fc-timegrid-col.fc-day-today .fc-timegrid-col-frame,
.fc .fc-list-day.fc-day-today,
.fc .fc-list-day-cushion.fc-day-today { background-color: transparent !important; }
.fc-popover { display: none !important; }
</style>
<style scoped>
/* Botões da toolbar do FullCalendar com cor primária do template */
:deep(.fc-toolbar-chunk .btn-group .btn),
:deep(.fc .fc-toolbar .btn),
:deep(.fc .fc-toolbar .fc-button) {
  color: #099885 !important;
  background-color: rgba(9, 152, 133, 0.12) !important;
  border: none !important;
  box-shadow: none !important;
}
:deep(.fc-toolbar-chunk .btn-group .btn:hover),
:deep(.fc .fc-toolbar .btn:hover),
:deep(.fc .fc-toolbar .fc-button:hover) {
  color: #099885 !important;
  background-color: rgba(9, 152, 133, 0.25) !important;
}
:deep(.fc-toolbar-chunk .btn-group .btn.active),
:deep(.fc .fc-toolbar .btn.fc-button-active),
:deep(.fc .fc-toolbar .fc-button.fc-button-active) {
  color: #fff !important;
  background-color: #099885 !important;
}

/* Ajuste de bordas do grupo de botões na toolbar */
:deep(.fc .btn-group > .btn:not(:last-child):not(.dropdown-toggle)),
:deep(.fc .btn-group > .btn.dropdown-toggle-split:first-child),
:deep(.fc .btn-group > .btn-group:not(:last-child) > .btn) {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}

/* Capitalização do texto dos botões */
:deep(.fc .fc-toolbar .btn) {
  text-transform: capitalize;
}
.agenda-stripes {}
.agenda-stripe.bg-success-subtle { background-color: rgb(25, 135, 84); }
.agenda-stripe.bg-info-subtle { background-color: rgb(13, 202, 240); }
.agenda-stripe.bg-warning-subtle { background-color: rgb(255, 193, 7); }
.agenda-stripe.bg-danger-subtle { background-color: rgb(220, 53, 69); }
.agenda-stripe.bg-primary-subtle { background-color: rgb(53, 119, 241); }
.agenda-stripe.bg-secondary-subtle { background-color: rgb(108, 117, 125); }
.sugestoes-orcamento { padding: 0; border: 1px solid rgba(0,0,0,.1); border-radius: .375rem; }
.sombra-sugestoes { box-shadow: 0 .25rem .75rem rgba(0,0,0,.08); }
.sug-card { border: 1px solid rgba(0,0,0,.12); border-radius: .5rem; overflow: hidden; background-color: #fff; }
.sug-item { padding: .625rem .875rem; }
.sug-item:hover { background-color: rgba(9, 152, 133, 0.08); }
:deep(.choices__placeholder) { color: #6c757d; }
</style>
