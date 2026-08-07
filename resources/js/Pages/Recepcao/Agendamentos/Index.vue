<script>
import moment from "moment";
import Swal from "sweetalert2";
import simpleBar from "simplebar-vue"
import { CalendarIcon } from "@zhuowenli/vue-feather-icons";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Portuguese } from "flatpickr/dist/l10n/pt.js";
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
import Offcanvas from "@/Components/Offcanvas.vue";
import SuggestInput from "@/Components/SuggestInput.vue";
import SimpleTable from "@/Components/Tables/SimpleTable.vue";

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
                dayMaxEvents: 1,
                weekends: true,
                dateClick: this.cliqueNaData,
                eventClick: this.editarEvento,
                viewDidMount: this.aoMontarVisao,
                datesSet: this.aoDefinirDatas,
                eventContent: function (arg) {
                    let ev = arg.event;
                    let time = arg.timeText || "";
                    let title = String(ev.title || "").split('•')[0] || "Evento";
                    let ext = ev.extendedProps || {};
                    let proc = ext.procedimento_nome || "";
                    let status = ext.status || "";
                    let icon = "mdi mdi-calendar-clock";
                    if (status.toLowerCase().includes("atendido")) icon = "mdi mdi-check-circle";
                    else if (status.toLowerCase().includes("cancelado")) icon = "mdi mdi-close-circle";

                    let html = `
                        <div class="p-1 h-100 w-100 d-flex flex-column justify-content-start text-dark" style="font-size: 0.75rem; line-height: 1.1; overflow: hidden; white-space: normal; position: relative;">
                            <div class="d-flex align-items-center mb-1" style="opacity: 0.9; font-size: 0.7rem;">
                                <i class="${icon} me-1"></i>
                                <span>${time}</span>
                            </div>
                            <div class="fw-bold mb-1 text-truncate" title="${title}">${title}</div>
                            <div class="text-truncate opacity-75" style="font-size: 0.7rem;" title="${proc}">${proc}</div>
                        </div>
                    `;
                    return { html: html };
                },
            },
            eventosAtuais: [],
            modalAgendarVisivel: false,
            isEditMode: false,
            processandoAcoesCalendario: false,
            pacientesLocal: [],
            profissionaisLocal: [],
            profissionaisFiltradosCriacao: [],
            profissionaisFiltradosEdicao: [],
            ultimoProcedimentoIdCriacao: null,
            ultimoProcedimentoIdEdicao: null,
            ultimoProcedimentoAtCriacao: 0,
            ultimoProcedimentoAtEdicao: 0,
            procedimentosLocal: [],
            procedimentosFiltrados: [],
            procedimentosSelectRows: [],
            opcoesStatus: [],
            agendamentoForm: {
                id: null,
                paciente_id: null,
                convenio_id: null,
                pessoa_id: null,
                procedimento_id: null,
                data: "",
                hora: "",
                status_id: null,
                valor_cobrado: "",
                observacoes: "",
                is_retorno: false
            },
            conveniosPacienteCriacao: [],
            conveniosPacienteEdicao: [],
            carregandoConveniosPacienteCriacao: false,
            _restaurandoEdicao: false,
            sessoesCriacao: [],
            termoBuscaPaciente: "",
            mostrarSugestoesPaciente: false,
            processandoCriacao: false,
            opcoesFlatpickrData: {
                altInput: true,
                altFormat: "d M, Y",
                dateFormat: "Y-m-d",
                locale: "pt",
                minDate: "today",
            },
            opcoesFlatpickrDataEdicao: {
                altInput: true,
                altFormat: "d M, Y",
                dateFormat: "Y-m-d",
                locale: Portuguese,
            },
            opcoesFlatpickrHora: {
                enableTime: true,
                noCalendar: true,
                altInput: true,
                altFormat: "H:i",
                dateFormat: "H:i",
                time_24hr: true,
                locale: Portuguese,
            },
            agendasHoje: [],
            modalEventosDiaVisivel: false,
            eventosDiaModal: [],
            dataEventosDiaModal: null,
            chaveTabelaEventosDia: 0,
            acoesLoadingEventosDia: {},
            bloquearAcoesModalEventosDia: false,
            valorCobradoAutoCriacaoProcId: null,
            paletaMedicos: [
                { bg: "bg-success-subtle", text: "text-success" },
                { bg: "bg-info-subtle", text: "text-info" },
                { bg: "bg-warning-subtle", text: "text-warning" },
                { bg: "bg-danger-subtle", text: "text-danger" },
                { bg: "bg-primary-subtle", text: "text-primary" },
                { bg: "bg-secondary-subtle", text: "text-secondary" },
                { bg: "bg-teal-subtle", text: "text-teal" },
                { bg: "bg-purple-subtle", text: "text-purple" },
                { bg: "bg-pink-subtle", text: "text-pink" },
                { bg: "bg-orange-subtle", text: "text-orange" },
                { bg: "bg-indigo-subtle", text: "text-indigo" },
                { bg: "bg-brown-subtle", text: "text-brown" },
                { bg: "bg-lime-subtle", text: "text-lime" },
                { bg: "bg-sky-subtle", text: "text-sky" },
            ],
            carregandoAgendas: false,
            carregandoDadosOffcanvas: false,
            dataAgendaSelecionada: null,
            classeFundoDataSelecionada: null,
            coresDataSelecionada: [],
            mapaDiasSemanaSelecionados: {},
            mapaAgendasSemana: {},
            processandoEdicao: false,
            agendamentoEstaPago: false,
            termoBuscaPacienteEdicao: "",
            mostrarSugestoesPacienteEdicao: false,
            procedimentosFiltradosEdicao: [],
            modalCancelamento: false,
            chaveProcedimentoEdicao: 0,
            chaveRenderizacaoModalEvento: 0,
            renderProcedimentoEdicao: true,
            nomePacienteEdicao: "",
            eventoSelecionado: null,
            editandoNoModalDeCriacao: false,
            habilitarCorFundoCalendario: localStorage.getItem("habilitarCorFundoCalendario") === "true",
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
        Offcanvas,
        SuggestInput,
        SimpleTable
    },
    computed: {
        configFlatpickrAgendar() {
            return {
                ...this.opcoesFlatpickrData,
                minDate: this.editandoNoModalDeCriacao ? null : "today"
            };
        },
        agendamentosFiltrados() {
            try {
                if (!this.dataEventosDiaModal) return "Agendamentos do dia";
                return `Agendamentos de ${moment(this.dataEventosDiaModal, "YYYY-MM-DD", true).format("DD/MM/YYYY")}`;
            } catch (e) {
                return "Agendamentos do dia";
            }
        },
        tituloModalEventosDia() {
            try {
                if (!this.dataEventosDiaModal) return "Agendamentos do dia";
                return `Agendamentos de ${moment(this.dataEventosDiaModal, "YYYY-MM-DD", true).format("DD/MM/YYYY")}`;
            } catch (e) {
                return "Agendamentos do dia";
            }
        },
        tituloModalEditarAgendamento() {
            try {
                const isCancelado = this.podeReagendarAgendamentoCancelado();
                const prefix = isCancelado ? "Reagendar" : "Editar Agendamento";
                const n = this.eventoSelecionado?.extendedProps?.sessao_numero ?? null;
                const t = this.eventoSelecionado?.extendedProps?.sessao_total ?? null;

                if (n != null && t != null) return `${prefix} - Sessão ${n}/${t}`;
                if (n != null) return `${prefix} - Sessão ${n}`;

                return isCancelado ? "Reagendar Agendamento" : "Editar Agendamento";
            } catch (e) {
                return "Editar Agendamento";
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

                return this.pacientesLocal || [];
            } catch (e) {
                return this.pacientesLocal || [];
            }
        },
        listaProfissionaisCriacao() {
            try {
                const pid = this.agendamentoForm?.procedimento_id;
                if (!pid) return [];
                return Array.isArray(this.profissionaisFiltradosCriacao) ? this.profissionaisFiltradosCriacao : [];
            } catch (e) {
                return [];
            }
        },
        listaPacientesEdicao() {
            try {

                return this.pacientesLocal || [];
            } catch (e) {
                return this.pacientesLocal || [];
            }
        },
        listaProfissionaisEdicao() {
            try {
                const pid = this.editAgendamentoForm?.procedimento_id;
                if (!pid) return [];
                return Array.isArray(this.profissionaisFiltradosEdicao) ? this.profissionaisFiltradosEdicao : [];
            } catch (e) {
                return [];
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
        window.addEventListener("unhandledrejection", (event) => {
            try {
                const r = event?.reason;
                const s = typeof r === "string" ? r : String(r?.message || r || "");
                if (s && (s.includes("A listener indicated an asynchronous response by returning true") || s.includes("message channel closed"))) {
                    if (event && event.preventDefault) event.preventDefault();
                }
            } catch (e) { }
        });
        try {
            moment.locale("pt-br");
            const props = this.$page && this.$page.props ? this.$page.props : {};
            this.pacientesLocal = [...(props.pacientes || [])];
            this.profissionaisLocal = [...(props.profissionais || [])];
            this.procedimentosLocal = [...(props.procedimentos || [])];
            this.procedimentosFiltrados = [];
            this.opcoesStatus = [...(props.status || [])];
            this.agendasHoje = [...(props.agendasHoje || [])];
            this.dataAgendaSelecionada = moment().format("YYYY-MM-DD");
            if (this.agendasHoje.length > 0) {
                this.classeFundoDataSelecionada = this.paletaMedicos[0].bg;
                this.coresDataSelecionada = this.agendasHoje.map((_, idx) => this.paletaMedicos[idx % this.paletaMedicos.length].bg);
            }
            this.buscarMapaDiasSemanaSelecionados();
            this.buscarUltimosAgendamentos();

            // Lógica para abrir modal com dados pré-preenchidos via query params
            const params = new URLSearchParams(window.location.search);
            const prePacId = params.get('paciente');
            const preProcId = params.get('procedimento');
            const preTussId = params.get('tuss');
            if (prePacId) {
                this.abrirNovoAgendamento();
                setTimeout(() => {
                    this.agendamentoForm.paciente_id = parseInt(prePacId);
                    const pac = this.pacientesLocal.find(p => String(p.id) === String(prePacId));
                    if (pac) this.termoBuscaPaciente = pac.nome;
                    
                    if (preProcId) {
                        this.agendamentoForm.procedimento_id = parseInt(preProcId);
                    } else if (preTussId) {
                        this.agendamentoForm.procedimento_id = parseInt(preTussId); // Select de procedimentos lista TUSS também se convênio
                    }
                }, 300);
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        } catch (e) { }
    },
    watch: {
        habilitarCorFundoCalendario(nv) {
            localStorage.setItem("habilitarCorFundoCalendario", nv);
            this.aplicarTodasListras();
        },
        "agendamentoForm.paciente_id"(nv) {
            if (this._restaurandoEdicao) return;
            try { this.carregarConveniosPacienteCriacao(); } catch (_) { }
        },
        "agendamentoForm.convenio_id"(nv) {
            try {
                if (!this._restaurandoEdicao) {
                    this.carregarProcedimentosPorConvenio(nv);
                }
            } catch (_) { }
        },

        "agendamentoForm.pessoa_id"() {
            try { this.verificarAgendaProfissionalParaDia(); } catch (e) { }
        },
        "agendamentoForm.procedimento_id"(nv, ov) {
            try {
                const a = nv != null ? String(nv) : "";
                const b = ov != null ? String(ov) : "";
                if (a !== b) {
                    try {
                        if (!this._restaurandoEdicao) {
                            this.aoAlterarProcedimento();
                        }
                    } catch (_) { }
                }
                const p = (this.procedimentosLocal || []).find(x => String(x.id) === String(nv));
                const isTrat = !!p?.eh_tratamento;
                const total = isTrat ? Math.max(0, Number(p?.quantidade_sessoes || 0)) : 0;
                if (isTrat && total > 0) {
                    const baseData = this.agendamentoForm?.data || "";
                    const baseHora = this.agendamentoForm?.hora || "";
                    this.sessoesCriacao = Array.from({ length: total }).map((_, i) => ({
                        data: i === 0 ? baseData : "",
                        hora: i === 0 ? baseHora : ""
                    }));
                } else {
                    this.sessoesCriacao = [];
                }
            } catch (e) { }
        },
        "agendamentoForm.data"(nv) {
            try {
                if (Array.isArray(this.sessoesCriacao) && this.sessoesCriacao.length > 0) {
                    if (!this.sessoesCriacao[0].data) this.sessoesCriacao[0].data = nv || "";
                }
            } catch (e) { }
        },
        "agendamentoForm.hora"(nv) {
            try {
                if (Array.isArray(this.sessoesCriacao) && this.sessoesCriacao.length > 0) {
                    if (!this.sessoesCriacao[0].hora) this.sessoesCriacao[0].hora = nv || "";
                }
            } catch (e) { }
        },
    },
    methods: {

        reiniciarChoicesJs(refsArray, initMode = true) {
            refsArray.forEach(refName => {
                try {
                    const el = this.$refs[refName];
                    if (el) {
                        if (initMode === false) {
                            window.destroyChoiceEl(el);
                        } else if (initMode === true) {
                            window.initChoiceEl(el);
                        } else {
                            window.destroyChoiceEl(el);
                            window.initChoiceEl(el);
                        }
                    }
                } catch (_) {}
            });
        },


        onMoreLinkClick(arg) {
            const ds = this.obterDataDoMaisLink(arg);
            if (!ds) return false;
            this.abrirModalEventosDoDia(ds, arg?.jsEvent);
            return false;
        },
        async atribuirMedicoAEventosDia(ds) {
            try {
                const agendas = await this.buscarAgendasPorDataSomente(ds);
                const list = Array.isArray(agendas) ? agendas : [];
                (this.eventosDiaModal || []).forEach(ev => {
                    try {
                        if (ev?.extendedProps?.profissional_nome) {
                            return; // Já tem o profissional definido pelo backend
                        }
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
                                ev.setExtendedProp("pessoa_id", chosen.pessoa_id ?? null);
                            } else {
                                ev.extendedProps = { ...(ev.extendedProps || {}), profissional_nome: chosen.nome || "Profissional", pessoa_id: chosen.pessoa_id ?? null };
                            }
                        }
                    } catch (_) { }
                });
            } catch (e) { }
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
                return (this.eventosDiaModal || [])
                    .filter(ev => {
                        const st = String(ev?.extendedProps?.status || "").toLowerCase();
                        return !st.includes("atendido");
                    })
                    .map(ev => {
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

                        const sessN = ev?.extendedProps?.sessao_numero ?? null;
                        const sessT = ev?.extendedProps?.sessao_total ?? null;
                        if (sessN != null && sessT != null) {
                            procNome += ` (Sessão ${sessN}/${sessT})`;
                        } else if (sessN != null) {
                            procNome += ` (Sessão ${sessN})`;
                        }

                        return {
                            id: ev?.id,
                            paciente: pacNome || "Paciente",
                            procedimento: procNome || "Procedimento",
                            hora: this.formatHora24(ev?.start) || "--:--",
                            status: st || "Agendado",
                            medico: medNome || "Profissional"
                        };
                    });
            } catch (e) {
                return [];
            }
        },
        async cancelarAgendamentoPorId(row) {
            const id = row?.id ?? null;
            if (!id) return;
            const key = String(id);
            if (this.acoesLoadingEventosDia?.[key]?.delete) return;
            try {
                this.acoesLoadingEventosDia = { ...(this.acoesLoadingEventosDia || {}), [key]: { ...(this.acoesLoadingEventosDia?.[key] || {}), delete: true } };
                await window.axios.put(`/agendamentos/${id}/cancel`, { observacoes: null });
                const api = this.$refs.fullCalendar?.getApi?.();
                const ev = api?.getEventById?.(id);
                if (ev) {
                    ev.setExtendedProp("status", "Cancelado");
                    ev.setProp("classNames", ["bg-danger-subtle"]);
                }
                this.eventosDiaModal = (this.eventosDiaModal || []).map(e => {
                    if (String(e.id) === String(id)) {
                        try { e.extendedProps = { ...(e.extendedProps || {}), status: "Cancelado" }; } catch (_) { }
                    }
                    return e;
                });
                await this.buscarUltimosAgendamentos();
                await this.atualizarTabelaEventosDiaModal();
            } catch (e) {
            } finally {
                try {
                    const map = { ...(this.acoesLoadingEventosDia || {}) };
                    if (map[key]) {
                        const rowMap = { ...(map[key] || {}) };
                        delete rowMap.delete;
                        if (Object.keys(rowMap).length) map[key] = rowMap;
                        else delete map[key];
                    }
                    this.acoesLoadingEventosDia = map;
                } catch (_) { }
            }
        },
        async abrirReagendarSessaoDaLista(id) {
            const key = id != null ? String(id) : null;
            if (!key) return;
            if (this.acoesLoadingEventosDia?.[key]?.edit) return;
            try {
                this.bloquearAcoesModalEventosDia = true;
                this.acoesLoadingEventosDia = { ...(this.acoesLoadingEventosDia || {}), [key]: { ...(this.acoesLoadingEventosDia?.[key] || {}), edit: true } };
                const api = this.$refs.fullCalendar?.getApi?.();
                const ev = api?.getEventById?.(id);
                if (!ev) return;

                await this.editarEvento({ event: ev });
            } finally {
                this.bloquearAcoesModalEventosDia = false;
                try {
                    const map = { ...(this.acoesLoadingEventosDia || {}) };
                    if (map[key]) {
                        const rowMap = { ...(map[key] || {}) };
                        delete rowMap.edit;
                        if (Object.keys(rowMap).length) map[key] = rowMap;
                        else delete map[key];
                    }
                    this.acoesLoadingEventosDia = map;
                } catch (_) { }
            }
        },
        onBlurSugestoesPaciente() {
            setTimeout(() => { this.mostrarSugestoesPaciente = false; }, 150);
        },
        selecionarSugestaoPaciente(p) {
            const id = p?.id ?? null;
            this.agendamentoForm.paciente_id = id;
            this.termoBuscaPaciente = String(p?.nome || "");
            this.mostrarSugestoesPaciente = false;
        },
        async carregarConveniosPacienteCriacao() {
            const pid = this.agendamentoForm.paciente_id;
            const isRestaurando = this._restaurandoEdicao;
            if (!pid || pid === 'Selecione') {
                let arr = [];
                if (isRestaurando && this.convenioIdOriginalEdicao != null) {
                    arr.push({ id: this.convenioIdOriginalEdicao, descricao: `Convênio ` });
                    this.agendamentoForm.convenio_id = this.convenioIdOriginalEdicao;
                } else {
                    this.agendamentoForm.convenio_id = null;
                }
                this.conveniosPacienteCriacao = arr;
                await this.$nextTick();
                try {
                    const el0 = this.$refs.selConvenioCriacao;
                    if (el0) {
                        el0.disabled = false;
                        window.destroyChoiceEl(el0);
                        window.initChoiceEl(el0);
                        const cv = this.agendamentoForm.convenio_id;
                        if (cv != null && cv !== '') {
                            window.syncChoiceValue(el0, String(cv));
                        }
                    }
                } catch (_) { }
                return;
            }
            this.carregandoConveniosPacienteCriacao = true;
            try {
                const resp = await window.axios.get(`/pacientes/${pid}/convenios`);
                const arr = Array.isArray(resp?.data?.convenios) ? resp.data.convenios : [];
                
                if (isRestaurando && this.convenioIdOriginalEdicao != null) {
                    const exists = arr.some(c => String(c.id) === String(this.convenioIdOriginalEdicao));
                    if (!exists) {
                        arr.push({ id: this.convenioIdOriginalEdicao, descricao: `Convênio` });
                    }
                }

                this.conveniosPacienteCriacao = arr;
                const cid = this.agendamentoForm.convenio_id;
                if (!isRestaurando && cid && !arr.some((c) => String(c.id) === String(cid))) {
                    this.agendamentoForm.convenio_id = null;
                }
                
                // Gato: Se estiver restaurando e o backend não tiver enviado o convenio_id 
                // (ou seja, cid é nulo) mas o paciente tiver apenas 1 convênio ativo, 
                // selecionamos esse convênio automaticamente para não exibir "Selecione".
                if (isRestaurando && !this.agendamentoForm.convenio_id && arr.length === 1) {
                    this.agendamentoForm.convenio_id = arr[0].id;
                }
            } catch (e) {
                let arr = [];
                if (isRestaurando && this.convenioIdOriginalEdicao != null) {
                    arr.push({ id: this.convenioIdOriginalEdicao, descricao: `Convênio ` });
                    this.agendamentoForm.convenio_id = this.convenioIdOriginalEdicao;
                } else {
                    this.agendamentoForm.convenio_id = null;
                }
                this.conveniosPacienteCriacao = arr;
            } finally {
                this.carregandoConveniosPacienteCriacao = false;
                await this.$nextTick();
                try {
                    const el0 = this.$refs.selConvenioCriacao;
                    if (el0) {
                        el0.disabled = false;
                        window.destroyChoiceEl(el0);
                        window.initChoiceEl(el0);
                        const cv = this.agendamentoForm.convenio_id;
                        if (cv != null && cv !== '') {
                            window.syncChoiceValue(el0, String(cv));
                        }
                    }
                } catch (_) { }
            }
        },
        async carregarConveniosPacienteEdicao() {
            const pid = this.editAgendamentoForm.paciente_id;
            if (!pid || pid === 'Selecione') {
                this.conveniosPacienteEdicao = [];
                return;
            }
            try {
                const resp = await window.axios.get(`/pacientes/${pid}/convenios`);
                this.conveniosPacienteEdicao = Array.isArray(resp?.data?.convenios) ? resp.data.convenios : [];
            } catch (e) {
                this.conveniosPacienteEdicao = [];
            } finally {
                // Initialização do Choices.js foi movida para logo após modalEditarVisivel = true
            }
        },
        async carregarProcedimentosPorConvenio(cid) {
            const isRestaurando = this._restaurandoEdicao;
            if (!cid || cid === 'Selecione') {
                this.procedimentosSelectRows = [];
                this.procedimentosFiltrados = [];
                if (!isRestaurando) this.agendamentoForm.procedimento_id = null;
                await this.$nextTick();
                try {
                    const el2 = this.$refs.selProcedimentoCriacao;
                    if (el2) {
                        el2.disabled = true;
                        window.destroyChoiceEl(el2);
                        window.initChoiceEl(el2);
                    }
                } catch (_) { }
                return;
            }
            try {
                const resp = await window.axios.get(`/convenios/${cid}/procedimentos-orcamento`);
                const arr = Array.isArray(resp?.data?.procedimentos) ? resp.data.procedimentos : [];
                this.procedimentosSelectRows = arr;
                this.procedimentosFiltrados = arr.map(r => ({
                    id: r.id,
                    nome: r.nome,
                    descricao: r.descricao,
                    valor: r.valor,
                    eh_tratamento: r.eh_tratamento,
                    quantidade_sessoes: r.quantidade_sessoes,
                    especialidades: r.especialidades
                }));

                if (isRestaurando && this.procedimentosFiltradosEdicao && this.procedimentosFiltradosEdicao.length > 0) {
                    const scheduledProcIdInit = this.procedimentoIdOriginalEdicao;
                    if (scheduledProcIdInit != null) {
                        const exists = this.procedimentosFiltrados.some(p => String(p.id) === String(scheduledProcIdInit));
                        if (!exists) {
                            this.procedimentosFiltrados.push(this.procedimentosFiltradosEdicao[0]);
                        }
                    }
                }

                if (!isRestaurando) this.agendamentoForm.procedimento_id = null;
                await this.$nextTick();
                try {
                    const el2 = this.$refs.selProcedimentoCriacao;
                    if (el2) {
                        el2.disabled = false;
                        window.destroyChoiceEl(el2);
                        window.initChoiceEl(el2);
                        if (isRestaurando && this.procedimentoIdOriginalEdicao != null) {
                            window.syncChoiceValue(el2, String(this.procedimentoIdOriginalEdicao));
                        }
                    }
                } catch (_) { }
            } catch (e) {
                this.procedimentosSelectRows = [];
                this.procedimentosFiltrados = [];
                if (!isRestaurando) this.agendamentoForm.procedimento_id = null;
            }
        },
        onBlurSugestoesPacienteEdicao() {
            setTimeout(() => { this.mostrarSugestoesPacienteEdicao = false; }, 150);
        },
        selecionarSugestaoPacienteEdicao(p) {
            const id = p?.id ?? null;
            this.editAgendamentoForm.paciente_id = id;
            this.termoBuscaPacienteEdicao = String(p?.nome || "");
            this.mostrarSugestoesPacienteEdicao = false;
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
                if (moment(ds, "YYYY-MM-DD", true).isValid() && moment(ds, "YYYY-MM-DD", true).isBefore(moment(), "day")) {
                    try {
                        const fp = (this.$page?.props?.flash ?? {});
                        this.$page.props.flash = { ...fp, warning: "Não é permitido agendar em data passada." };
                    } catch (_) { }
                    return;
                }
                // Reset agendamentoForm and clear any flash warnings before showing modal
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    if (fp.warning) this.$page.props.flash = { ...fp, warning: null };
                } catch (_) { }

                this.agendamentoForm = { paciente_id: null, convenio_id: null, pessoa_id: null, procedimento_id: null, data: ds, hora: "", status_id: null, valor_cobrado: "", observacoes: "", is_retorno: false };
                this.termoBuscaPaciente = "";
                this.conveniosPacienteCriacao = [];
                this.procedimentosSelectRows = [];
                this.procedimentosFiltrados = [];
                this.buscarAgendasPorData(ds);
            } catch (e) { }
            this.editandoNoModalDeCriacao = false;
            try { const el0 = this.$refs.selConvenioCriacao; if (el0) window.destroyChoiceEl(el0); } catch (_) { }
            try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.destroyChoiceEl(el1); } catch (_) { }
            try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.destroyChoiceEl(el2); } catch (_) { }
            this.chaveProfissionalCriacao++;
            this.chaveProcedimentoCriacao++;
            try { if (window.resumeChoicesObserver) window.resumeChoicesObserver(); } catch (_) { }
            this.modalAgendarVisivel = true;
            this.$nextTick(() => {
                try { const elInit0 = this.$refs.selConvenioCriacao; if (elInit0) { elInit0.disabled = true; window.initChoiceEl(elInit0); } } catch (_) { }
                try { const elInit1 = this.$refs.selProfissionalCriacao; if (elInit1) { elInit1.disabled = false; window.initChoiceEl(elInit1); } } catch (_) { }
                try { const elInit2 = this.$refs.selProcedimentoCriacao; if (elInit2) { elInit2.disabled = true; window.initChoiceEl(elInit2); } } catch (_) { }
            });
        },
        abrirNovoAgendamento() {
            this.isEditMode = false;
            // Limpa avisos de flash anteriores ao abrir modal limpo
            try {
                const fp = (this.$page?.props?.flash ?? {});
                if (fp.warning) this.$page.props.flash = { ...fp, warning: null };
            } catch (_) { }
            this.agendamentoForm = { paciente_id: null, convenio_id: null, pessoa_id: null, procedimento_id: null, data: "", hora: "", status_id: null, valor_cobrado: "", observacoes: "", is_retorno: false };
            this.termoBuscaPaciente = "";
            this.mostrarSugestoesPaciente = false;

            this.conveniosPacienteCriacao = [];
            this.procedimentosSelectRows = [];
            this.procedimentosFiltrados = [];
            try { const el0 = this.$refs.selConvenioCriacao; if (el0) window.destroyChoiceEl(el0); } catch (_) { }
            try { const el1 = this.$refs.selProfissionalCriacao; if (el1) window.destroyChoiceEl(el1); } catch (_) { }
            try { const el2 = this.$refs.selProcedimentoCriacao; if (el2) window.destroyChoiceEl(el2); } catch (_) { }
            this.modalAgendarVisivel = true;
            this.$nextTick(() => {
                try { const elInit0 = this.$refs.selConvenioCriacao; if (elInit0) { elInit0.disabled = true; window.initChoiceEl(elInit0); } } catch (_) { }
                try { const elInit1 = this.$refs.selProfissionalCriacao; if (elInit1) { elInit1.disabled = false; window.initChoiceEl(elInit1); } } catch (_) { }
                try { const elInit2 = this.$refs.selProcedimentoCriacao; if (elInit2) { elInit2.disabled = true; window.initChoiceEl(elInit2); } } catch (_) { }
            });
        },
        cliqueNoDiaNavegacao(date, jsEvent) {
            try {
                if (jsEvent && jsEvent.preventDefault) jsEvent.preventDefault();
                if (jsEvent && jsEvent.stopPropagation) jsEvent.stopPropagation();
            } catch (e) { }
            try {
                const ds = moment(date).format("YYYY-MM-DD");
                this.buscarAgendasPorData(ds);
            } catch (e) { }
        },
        /**
         * Modal open for edit event
         */
        async editarEvento(info) {
            if (this.eCliqueNoTituloDoEvento(info?.jsEvent)) {
                const ds = this.obterDataDoEvento(info?.event);
                this.abrirModalEventosDoDia(ds, info?.jsEvent);
                return;
            }
            this.eventoSelecionado = info.event;
            const start = this.eventoSelecionado.start ? moment(this.eventoSelecionado.start) : null;
            const ds = start && start.isValid() ? start.format("YYYY-MM-DD") : "";
            const hs = start && start.isValid() ? start.format("HH:mm") : "";
            // Ativar flag para impedir que watchers limpem os valores durante restauração
            this._restaurandoEdicao = true;

            this.carregandoDadosOffcanvas = true;
            this.modalAgendarVisivel = true;

            let ag = null;
            if (this.eventoSelecionado.id) {
                try {
                    const resp = await window.axios.get(`/agendamentos/${this.eventoSelecionado.id}`);
                    ag = resp?.data?.agendamento ?? null;
                } catch (_) { }
            }

            this.agendamentoEstaPago = (ag?.status_pagamento === 'PAGO');
            this.agendamentoForm.id = this.eventoSelecionado.id || null;
            this.agendamentoForm.data = ds;
            this.agendamentoForm.hora = hs;
            this.agendamentoForm.observacoes = String(ag?.observacoes || (this.eventoSelecionado.extendedProps && this.eventoSelecionado.extendedProps.observacoes) ? this.eventoSelecionado.extendedProps.observacoes : "");
            this.agendamentoForm.status_id = null;
            this.agendamentoForm.is_retorno = false;
            
            const pacIdFromEP = ag?.paciente_id ?? this.eventoSelecionado?.extendedProps?.paciente_id ?? null;
            const procIdFromEP = ag?.procedimento_id ?? this.eventoSelecionado?.extendedProps?.procedimento_id ?? null;
            const savedConvenioId = ag?.convenio_id ?? this.eventoSelecionado?.extendedProps?.convenio_id ?? null;
            const savedPessoaId = ag?.pessoa_id ?? this.eventoSelecionado?.extendedProps?.pessoa_id ?? null;
            
            this.convenioIdOriginalEdicao = savedConvenioId;
            this.pessoaIdOriginalEdicao = savedPessoaId;

            if (savedConvenioId != null) this.agendamentoForm.convenio_id = savedConvenioId;
            if (savedPessoaId != null) this.agendamentoForm.pessoa_id = savedPessoaId;
            this.agendamentoForm.valor_cobrado = (ag?.valor_cobrado != null && ag?.valor_cobrado !== "")
                ? String(ag.valor_cobrado)
                : "";

            const title = String(this.eventoSelecionado.title || "");
            let pacNome = String(ag?.paciente_nome || "").trim();
            let procNome = String(ag?.procedimento_nome || "").trim();

            if (!pacNome || !procNome) {
                if (title.includes("•")) {
                    const parts = title.split("•");
                    if (!pacNome) pacNome = String(parts[0] || "").trim();
                    if (!procNome) procNome = String(parts[1] || "").trim();
                } else {
                    if (!pacNome) pacNome = title.trim();
                    if (!procNome) procNome = String(this.eventoSelecionado.extendedProps?.procedimento_nome || "").trim();
                }
            }

            this.nomePacienteEdicao = pacNome;
            this.termoBuscaPacienteEdicao = pacNome;
            this.termoBuscaPaciente = pacNome;
            if (pacIdFromEP !== null && pacIdFromEP !== "") {
                this.agendamentoForm.paciente_id = Number(pacIdFromEP);
            } else {
                const pac = this.encontrarPacientePorNome(pacNome);
                this.agendamentoForm.paciente_id = pac ? pac.id : null;
            }

            if (procIdFromEP !== null && procIdFromEP !== "") {
                this.agendamentoForm.procedimento_id = Number(procIdFromEP);
            } else {
                const proc = (this.procedimentosLocal || []).find(p => String(p.nome).trim() === procNome);
                this.agendamentoForm.procedimento_id = proc ? proc.id : null;
            }
            this.procedimentoIdOriginalEdicao = this.agendamentoForm.procedimento_id || null;
            const scheduledProcIdInit = this.agendamentoForm.procedimento_id;
            if (scheduledProcIdInit != null) {
                const scheduledProcInit = (this.procedimentosLocal || []).find(p => String(p.id) === String(scheduledProcIdInit));
                if (scheduledProcInit) {
                    this.procedimentosFiltradosEdicao = [{ ...scheduledProcInit, nome: scheduledProcInit.nome }];
                } else if (procNome) {
                    this.procedimentosFiltradosEdicao = [{ id: scheduledProcIdInit, nome: procNome }];
                } else {
                    this.procedimentosFiltradosEdicao = [];
                }
            } else {
                this.procedimentosFiltradosEdicao = [];
            }
            this.fecharPopoversCalendario();
            this.isEditMode = true;

            if (!this.agendamentoForm.pessoa_id) {
                await this.inferirProfissionalDaAgenda(ds, hs);
            }

            this.termoBuscaPaciente = this.termoBuscaPacienteEdicao;
            this.procedimentosFiltrados = [...this.procedimentosFiltradosEdicao];

            // Carregar dados em paralelo
            const promessas = [
                this.carregarConveniosPacienteCriacao()
            ];
            if (savedConvenioId) {
                promessas.push(this.carregarProcedimentosPorConvenio(savedConvenioId));
            }
            await Promise.all(promessas);

            // Re-aplicar IDs após os carregamentos
            let actualConvenioId = savedConvenioId;
            
            if (actualConvenioId == null && ag && ag.procedimento_id != null && ag.tuss_id == null) {
                const part = (this.conveniosPacienteCriacao || []).find(c => String(c.tipo).toUpperCase() === 'PARTICULAR');
                if (part) {
                    actualConvenioId = part.id;
                }
            }

            if (actualConvenioId == null && this.conveniosPacienteCriacao && this.conveniosPacienteCriacao.length === 1) {
                actualConvenioId = this.conveniosPacienteCriacao[0].id;
            }

            if (actualConvenioId != null && actualConvenioId !== savedConvenioId) {
                this.convenioIdOriginalEdicao = actualConvenioId;
                await this.carregarProcedimentosPorConvenio(actualConvenioId);
            }

            if (actualConvenioId != null) this.agendamentoForm.convenio_id = actualConvenioId;
            this.agendamentoForm.procedimento_id = procIdFromEP || this.procedimentoIdOriginalEdicao;
            if (savedPessoaId != null) this.agendamentoForm.pessoa_id = savedPessoaId;

            await this.aoAlterarProcedimento();

            this.carregandoDadosOffcanvas = false;
            
            this.$nextTick(() => {
                setTimeout(() => {
                    this._restaurandoEdicao = true;
                    
                    if (actualConvenioId != null) this.agendamentoForm.convenio_id = actualConvenioId;
                    this.agendamentoForm.procedimento_id = procIdFromEP || this.procedimentoIdOriginalEdicao;
                    if (savedPessoaId != null) this.agendamentoForm.pessoa_id = savedPessoaId;

                    try {
                        const el0 = this.$refs.selConvenioCriacao;
                        if (el0) window.syncChoiceValue(el0, this.agendamentoForm.convenio_id);
                        
                        const el1 = this.$refs.selProcedimentoCriacao;
                        if (el1) window.syncChoiceValue(el1, this.agendamentoForm.procedimento_id);
                        
                        const el2 = this.$refs.selProfissionalCriacao;
                        if (el2) window.syncChoiceValue(el2, this.agendamentoForm.pessoa_id);
                    } catch (_) {}
                    
                    this.$nextTick(() => {
                        this._restaurandoEdicao = false;
                    });
                }, 100);
            });
        },
        eCliqueNoTituloDoEvento(jsEvent) {
            const t = jsEvent?.target ?? null;
            const el = t && t.closest ? t.closest(".fc-event-title, .fc-event-title-container") : null;
            return !!el;
        },
        obterDataDoEvento(ev) {
            const m = ev?.start ? moment(ev.start) : null;
            return m && m.isValid() ? m.format("YYYY-MM-DD") : moment().format("YYYY-MM-DD");
        },
        obterDataDoMaisLink(arg) {
            const cell = arg?.dayEl?.closest?.("[data-date]") || arg?.dayEl || null;
            const attr = cell?.getAttribute?.("data-date") || null;
            if (attr) return String(attr).slice(0, 10);
            if (arg?.date) return moment.utc(arg.date).format("YYYY-MM-DD");
            return null;
        },
        abrirModalEventosDoDia(ds, jsEvent) {
            if (jsEvent?.preventDefault) jsEvent.preventDefault();
            if (jsEvent?.stopPropagation) jsEvent.stopPropagation();
            this.dataEventosDiaModal = ds;
            const api = this.$refs.fullCalendar?.getApi?.();
            const events = api?.getEvents?.() || [];
            const ini = moment(ds, "YYYY-MM-DD", true).startOf("day");
            const fim = moment(ds, "YYYY-MM-DD", true).endOf("day");
            this.eventosDiaModal = events.filter(ev => {
                const s = ev?.start ? moment(ev.start) : null;
                if (!s || !s.isValid()) return false;
                const e = ev?.end ? moment(ev.end) : s;
                return s.isSameOrBefore(fim) && e.isSameOrAfter(ini);
            });
            this.atribuirMedicoAEventosDia(ds).then(() => {
                this.chaveTabelaEventosDia += 1;
                this.modalEventosDiaVisivel = true;
            }).catch(() => {
                this.chaveTabelaEventosDia += 1;
                this.modalEventosDiaVisivel = true;
            });
            this.fecharPopoversCalendario();
            setTimeout(() => this.fecharPopoversCalendario(), 0);
        },

        async atualizarTabelaEventosDiaModal() {
            try {
                if (!this.modalEventosDiaVisivel) return;
                const ds = this.dataEventosDiaModal;
                if (!ds) return;
                const api = this.$refs.fullCalendar?.getApi?.();
                if (!api) return;

                const filtrar = () => {
                    const events = api?.getEvents?.() || [];
                    const ini = moment(ds, "YYYY-MM-DD", true).startOf("day");
                    const fim = moment(ds, "YYYY-MM-DD", true).endOf("day");
                    return (events || []).filter(ev => {
                        const s = ev?.start ? moment(ev.start) : null;
                        if (!s || !s.isValid()) return false;
                        const e = ev?.end ? moment(ev.end) : s;
                        return s.isSameOrBefore(fim) && e.isSameOrAfter(ini);
                    });
                };

                this.eventosDiaModal = filtrar();
                await this.atribuirMedicoAEventosDia(ds);
                this.eventosDiaModal = filtrar();
                this.chaveTabelaEventosDia += 1;
            } catch (e) { }
        },

        fecharPopoversCalendario() {
            try {
                const pops = document.querySelectorAll(".fc-popover");
                pops.forEach(el => el.remove());
            } catch (e) { }
        },

        async aoAlterarProcedimentoEdicao() {
            const pid = this.editAgendamentoForm.procedimento_id;
            if (!pid || pid === 'Selecione') {
                this.profissionaisFiltradosEdicao = [...this.profissionaisLocal];
                return;
            }
            const pidStr = pid != null ? String(pid) : "";
            const now = Date.now();
            if (pidStr && this.ultimoProcedimentoIdEdicao != null && String(this.ultimoProcedimentoIdEdicao) === pidStr && (now - (this.ultimoProcedimentoAtEdicao || 0)) < 300) {
                return;
            }
            this.ultimoProcedimentoIdEdicao = pidStr || null;
            this.ultimoProcedimentoAtEdicao = now;

            // Se houver procedimento selecionado, buscar profissionais por especialidade no back
            if (pid) {
                try {
                    const resp = await window.axios.get('/agendamentos/profissionais-por-procedimento', {
                        params: {
                            procedimento_id: pid,
                            convenio_id: this.editAgendamentoForm.convenio_id
                        }
                    });
                    const list = resp?.data?.profissionais;
                    this.profissionaisFiltradosEdicao = Array.isArray(list) ? list : [];
                } catch (e) {
                    this.profissionaisFiltradosEdicao = [];
                }
            } else {
                this.profissionaisFiltradosEdicao = [];
            }

            if (pid && (!this.profissionaisFiltradosEdicao || this.profissionaisFiltradosEdicao.length === 0)) {
                try {
                    // Na edição o convenio_id não está no editAgendamentoForm diretamente se não for alterável?
                    // Wait, this.editAgendamentoForm.convenio_id existe e eu passo na chamada axios
                    const convId = this.editAgendamentoForm.convenio_id;
                    // precisamos ver se tem os convenios disponíveis
                    // actually, we can just skip if convId is present because edit might not have conveniosPacienteCriacao
                    // wait, let's just check if there is a convId and assume we shouldn't fallback if there is one
                    const proc = (this.procedimentosLocal || []).find(p => String(p.id) === String(pid));
                    // se o procedimento não foi achado no local, é um TUSS, logo não deve rodar o fallback
                    if (proc) {
                        const esps = proc?.especialidades || [];
                        if (esps.length > 0) {
                            this.profissionaisFiltradosEdicao = (this.profissionaisLocal || []).filter(prof => {
                                return (prof.especialidades || []).some(pe => esps.some(e => String(e.id) === String(pe.id)));
                            });
                        }
                    }
                } catch (_) { }
            }

            try {
                const currentId = this.editAgendamentoForm.pessoa_id;
                const list = Array.isArray(this.profissionaisFiltradosEdicao) ? this.profissionaisFiltradosEdicao : [];
                const ok = currentId != null && list.some(p => String(p.id) === String(currentId));
                if (!ok) {
                    this.editAgendamentoForm.pessoa_id = (list.length === 1) ? (list[0]?.id ?? null) : null;
                }
            } catch (_) { }

            // Forçar atualização do select de profissional quando o procedimento mudar na edição
            this.$nextTick(async () => {
                try {
                    const el1 = this.$refs.selProfissionalEdicao;
                    if (el1) {
                        window.destroyChoiceEl(el1);
                        await this.$nextTick();
                        window.initChoiceEl(el1);
                    }
                } catch (e) { }
            });
        },
        async inferirProfissionalDaAgenda(ds, hs) {
            try {
                const d = ds || moment().format("YYYY-MM-DD");
                const resp = await window.axios.get("/agendas-medicas/by-date", { params: { data: d } });
                const arr = Array.isArray(resp?.data?.agendas) ? resp.data.agendas : [];
                const hhmm = String(hs || "").slice(0, 5);
                const m = (t) => {
                    const s = String(t || "").slice(0, 5);
                    const hh = Number(s.slice(0, 2));
                    const mm = Number(s.slice(3, 5));
                    return isNaN(hh) || isNaN(mm) ? null : hh * 60 + mm;
                };
                const mh = m(hhmm);
                let chosen = null;
                if (mh != null) {
                    arr.forEach(a => {
                        const mi = m(a.hora_inicio);
                        const mf = m(a.hora_fim);
                        if (mi == null || mf == null) return;
                        if (mh >= mi && mh <= mf && chosen == null) {
                            chosen = a.pessoa_id;
                        }
                    });
                }
                if (!chosen && arr.length > 0) {
                    chosen = arr[0].pessoa_id;
                }
                this.editAgendamentoForm.pessoa_id = chosen != null ? Number(chosen) : this.editAgendamentoForm.pessoa_id;
            } catch (e) { }
        },
        async salvarEdicaoAgendamento() {
            const payload = {
                paciente_id: this.agendamentoForm.paciente_id,
                pessoa_id: this.agendamentoForm.pessoa_id,
                procedimento_id: this.agendamentoForm.procedimento_id,
                data: this.agendamentoForm.data,
                hora: this.agendamentoForm.hora,
                status_id: this.agendamentoForm.status_id,
                valor_cobrado: this.agendamentoForm.valor_cobrado ? Number(String(this.agendamentoForm.valor_cobrado).replace(/[^\d.,-]/g, '').replace(',', '.')) : null,
                observacoes: this.agendamentoForm.observacoes,
            };
            this.processandoEdicao = true;
            try {
                if (this.agendamentoForm.id) {
                    try {
                        await window.axios.put(`/agendamentos/${this.agendamentoForm.id}`, payload);
                    } catch (e) { }
                }
                const api = this.$refs.fullCalendar?.getApi?.();
                const ev = api?.getEventById?.(this.agendamentoForm.id);
                if (ev) {
                    if (this.agendamentoForm.data && this.agendamentoForm.hora) {
                        ev.setStart(`${this.agendamentoForm.data}T${this.agendamentoForm.hora}:00`);
                    }
                    ev.setExtendedProp("observacoes", this.agendamentoForm.observacoes || "");
                    const pLocal = (this.procedimentosLocal || []).find(pr => String(pr.id) === String(this.agendamentoForm.procedimento_id));
                    const pFilt = (this.procedimentosFiltradosEdicao || []).find(pr => String(pr.id) === String(this.agendamentoForm.procedimento_id));
                    const procNome = pFilt?.nome || pLocal?.nome || null;
                    const pacNome = this.pacientesLocal.find(p => String(p.id) === String(this.agendamentoForm.paciente_id))?.nome || null;
                    ev.setExtendedProp("procedimento_id", this.agendamentoForm.procedimento_id || null);
                    if (procNome) ev.setExtendedProp("procedimento_nome", procNome);
                    if (pacNome || procNome) {
                        const title = `${pacNome || ev.title.split(" • ")[0].trim()} • ${procNome || (ev.title.includes(" • ") ? ev.title.split(" • ")[1].trim() : "Procedimento")}`;
                        ev.setProp("title", title);
                    }
                }
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, success: "Agendamento atualizado" };
                } catch (_) { }
                this.modalAgendarVisivel = false;
                await this.atualizarTabelaEventosDiaModal();
            } catch (e) {
                const msg = e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(' • ') : 'Falha ao atualizar';
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, error: String(msg || "Erro") };
                } catch (_) { }
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
                await this.atualizarTabelaEventosDiaModal();
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, success: "Agendamento cancelado" };
                } catch (_) { }
                this.modalCancelamento = false;
                this.modalEditarVisivel = false;
            } catch (e) {
                this.modalCancelamento = false;
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, error: "Falha ao cancelar" };
                } catch (_) { }
            }
        },

        podeReagendarSessaoTratamento() {
            try { return (this.eventoSelecionado?.extendedProps?.sessao_numero ?? null) != null; } catch (e) { return false; }
        },
        podeReagendarAgendamentoCancelado() {
            try {
                const st = String(this.eventoSelecionado?.extendedProps?.status || "").toLowerCase();
                return st.includes("cancel") || st.includes("reagend");
            } catch (e) {
                return false;
            }
        },
        async reagendarSessaoTratamento() {
            if (!this.agendamentoForm?.id) return;
            if (!this.podeReagendarAgendamentoCancelado()) return;
            const payload = {
                pessoa_id: this.agendamentoForm.pessoa_id,
                data: this.agendamentoForm.data,
                hora: this.agendamentoForm.hora,
            };
            this.processandoEdicao = true;
            try {
                const resp = await window.axios.put(`/agendamentos/${this.agendamentoForm.id}/reschedule-session`, payload);
                const ag = resp?.data?.agendamento;
                const api = this.$refs.fullCalendar?.getApi?.();
                const ev = api?.getEventById?.(this.agendamentoForm.id);
                if (ev && ag?.data && ag?.hora) {
                    ev.setStart(`${ag.data}T${String(ag.hora).slice(0, 5)}:00`);
                    ev.setExtendedProp("status", "");
                    ev.setProp("classNames", ["bg-primary-subtle"]);
                }
                await this.buscarUltimosAgendamentos();
                await this.atualizarTabelaEventosDiaModal();
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, success: "Agendamento reagendado" };
                } catch (_) { }
                this.modalAgendarVisivel = false;
            } catch (e) {
                const msg = e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(' • ') : 'Falha ao reagendar';
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, error: String(msg || "Falha ao reagendar") };
                } catch (_) { }
            } finally {
                this.processandoEdicao = false;
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
                const pid = this.agendamentoForm?.pessoa_id ?? null;
                const dataForm = this.agendamentoForm?.data;

                // Limpa o aviso se estiver presente para não persistir indevidamente
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    if (fp.warning === "Profissional não possui agenda para o dia selecionado.") {
                        this.$page.props.flash = { ...fp, warning: null };
                    }
                } catch (_) { }

                // Sem profissional selecionado: nada a verificar
                if (pid == null || pid === "") return;

                // Sem data selecionada no formulário: não é possível validar
                if (!dataForm) return;

                // Agendas ainda carregando: pode ter dado desatualizado, aguardar
                if (this.carregandoAgendas) return;

                // Agendas carregadas são de uma data diferente: não podemos validar com precisão
                if (this.dataAgendaSelecionada && dataForm !== this.dataAgendaSelecionada) return;

                const has = (this.agendasHoje || []).some(a => String(a.pessoa_id) === String(pid));
                if (!has) {
                    try {
                        const fp = (this.$page?.props?.flash ?? {});
                        this.$page.props.flash = { ...fp, warning: "Profissional não possui agenda para o dia selecionado." };
                    } catch (_) { }
                }
            } catch (e) { }
        },
        obterTituloAgenda() {
            const today = moment().format("YYYY-MM-DD");
            if (!this.dataAgendaSelecionada || this.dataAgendaSelecionada === today) {
                return "Médicos com agenda para hoje";
            }
            return `Médicos com agenda para data ${moment(this.dataAgendaSelecionada).format("DD/MM/YYYY")}`;
        },
        diaInteiro(ag) {
            const hi = typeof ag?.hora_inicio === "string" ? ag.hora_inicio.slice(0, 5) : null;
            const hf = typeof ag?.hora_fim === "string" ? ag.hora_fim.slice(0, 5) : null;
            return hi === "00:00" && hf === "23:59";
        },
        formatarIntervaloAgenda(ag) {
            const hi = typeof ag?.hora_inicio === "string" ? ag.hora_inicio.slice(0, 5) : "";
            const hf = typeof ag?.hora_fim === "string" ? ag.hora_fim.slice(0, 5) : "";
            return `${hi} às ${hf}`;
        },
        aplicarTodasListras() {
            try {
                this.limparListras();
                if (!this.habilitarCorFundoCalendario) return;
                
                if (!this.agendasHoje || this.agendasHoje.length === 0) {
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
            } catch (e) { }
        },
        limparListras() {
            try {
                const overlays = document.querySelectorAll(".agenda-stripes");
                overlays.forEach(el => el.remove());
            } catch (e) { }
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
                const max = (this.paletaMedicos && this.paletaMedicos.length) ? this.paletaMedicos.length : 6;
                const n = Math.max(1, Math.min(bgClasses.length || 0, max));
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
            } catch (e) { }
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
                const ids = (this.agendasHoje || []).map(a => a.pessoa_id).filter(v => v != null);
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
            const idx = arr.findIndex(a => String(a.pessoa_id) === String(id));
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
            const s = String(hhmm || "").slice(0, 5);
            const hh = Number(s.slice(0, 2));
            const mm = Number(s.slice(3, 5));
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
            } catch (e) { }
        },
        async aplicarListrasHorarioDia(ds) {
            try {
                const d = ds || moment().format("YYYY-MM-DD");
                const arr = await this.buscarAgendasPorDataSomente(d);
                this.criarListrasParaDataSemana(d, arr);
            } catch (e) { }
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
                    const c = this.corDoMedicoSelecionado(ag?.pessoa_id);
                    if (c && !allColors.includes(c)) allColors.push(c);
                });
                const max = (this.paletaMedicos && this.paletaMedicos.length) ? this.paletaMedicos.length : 6;
                const n = Math.max(1, Math.min(allColors.length || 0, max));
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
                    const c = this.corDoMedicoSelecionado(ag?.pessoa_id);
                    if (!c) return;
                    const idx = Math.max(0, allColors.indexOf(c));
                    const left = `${(idx * 100) / (n || 1)}%`;
                    const width = `${100 / (n || 1)}%`;
                    const hi = String(ag?.hora_inicio || "00:00").slice(0, 5);
                    const hf = String(ag?.hora_fim || "23:59").slice(0, 5);
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
            } catch (e) { }
        },

        async aoAlterarProcedimento() {
            const pid = this.agendamentoForm.procedimento_id;
            if (!pid || pid === 'Selecione') {
                this.profissionaisFiltrados = [...this.profissionaisLocal];
                if (!this._restaurandoEdicao) this.agendamentoForm.pessoa_id = null;
                await this.$nextTick();
                try {
                    const el1 = this.$refs.selProfissionalCriacao;
                    if (el1) {
                        el1.disabled = true;
                        window.destroyChoiceEl(el1);
                        window.initChoiceEl(el1);
                    }
                } catch (_) { }
                return;
            }
            const pidStr = pid != null ? String(pid) : "";
            const now = Date.now();
            if (pidStr && this.ultimoProcedimentoIdCriacao != null && String(this.ultimoProcedimentoIdCriacao) === pidStr && (now - (this.ultimoProcedimentoAtCriacao || 0)) < 300) {
                return;
            }
            this.ultimoProcedimentoIdCriacao = pidStr || null;
            this.ultimoProcedimentoAtCriacao = now;

            // Se houver procedimento selecionado, buscar profissionais por especialidade no back
            if (pid) {
                try {
                    const resp = await window.axios.get('/agendamentos/profissionais-por-procedimento', {
                        params: {
                            procedimento_id: pid,
                            convenio_id: this.agendamentoForm.convenio_id
                        }
                    });
                    const list = resp?.data?.profissionais;
                    let profs = Array.isArray(list) ? list : [];
                    
                    if (this._restaurandoEdicao && this.pessoaIdOriginalEdicao != null) {
                        const exists = profs.some(p => String(p.id) === String(this.pessoaIdOriginalEdicao));
                        if (!exists) {
                            const pLocal = (this.profissionaisLocal || []).find(p => String(p.id) === String(this.pessoaIdOriginalEdicao));
                            if (pLocal) {
                                profs.push({ ...pLocal, nome: pLocal.nome });
                            } else {
                                profs.push({ id: this.pessoaIdOriginalEdicao, nome: `Profissional` });
                            }
                        }
                    }
                    
                    this.profissionaisFiltradosCriacao = profs;
                } catch (e) {
                    this.profissionaisFiltradosCriacao = [];
                }
            } else {
                this.profissionaisFiltradosCriacao = [];
            }

            if (pid && (!this.profissionaisFiltradosCriacao || this.profissionaisFiltradosCriacao.length === 0)) {
                try {
                    const convId = this.agendamentoForm.convenio_id;
                    const conv = convId ? (this.conveniosPacienteCriacao || []).find(c => String(c.id) === String(convId)) : null;
                    const isParticular = !conv || String(conv.tipo || '').toUpperCase() === 'PARTICULAR';

                    if (isParticular) {
                        const proc = (this.procedimentosLocal || []).find(p => String(p.id) === String(pid));
                        const esps = proc?.especialidades || [];
                        if (esps.length > 0) {
                            let profs = (this.profissionaisLocal || []).filter(prof => {
                                return (prof.especialidades || []).some(pe => esps.some(e => String(e.id) === String(pe.id)));
                            });
                            
                            if (this._restaurandoEdicao && this.pessoaIdOriginalEdicao != null) {
                                const exists = profs.some(p => String(p.id) === String(this.pessoaIdOriginalEdicao));
                                if (!exists) {
                                    const pLocal = (this.profissionaisLocal || []).find(p => String(p.id) === String(this.pessoaIdOriginalEdicao));
                                    if (pLocal) {
                                        profs.push({ ...pLocal, nome: pLocal.nome });
                                    } else {
                                        profs.push({ id: this.pessoaIdOriginalEdicao, nome: `Profissional` });
                                    }
                                }
                            }
                            this.profissionaisFiltradosCriacao = profs;
                        }
                    }
                } catch (_) { }
            }

            try {
                const currentId = this.agendamentoForm.pessoa_id;
                const list = Array.isArray(this.profissionaisFiltradosCriacao) ? this.profissionaisFiltradosCriacao : [];
                const ok = currentId != null && list.some(p => String(p.id) === String(currentId));
                if (!ok && !this._restaurandoEdicao) {
                    this.agendamentoForm.pessoa_id = (list.length === 1) ? (list[0]?.id ?? null) : null;
                }
            } catch (_) { }

            // Forçar atualização do select de profissional quando o procedimento mudar
            if (!this._restaurandoEdicao) {
                this.$nextTick(async () => {
                    try {
                        const el1 = this.$refs.selProfissionalCriacao;
                        if (el1) {
                            el1.disabled = false;
                            window.destroyChoiceEl(el1);
                            await this.$nextTick();
                            window.initChoiceEl(el1);
                        }
                    } catch (e) { }
                });
            }
            try {
                const proc = pid ? ((this.procedimentosFiltrados || []).find(p => String(p.id) === String(pid)) || (this.procedimentosLocal || []).find(p => String(p.id) === String(pid))) : null;
                const v = proc?.valor ?? null;
                const deveAutopreencher = (this.agendamentoForm.valor_cobrado == null || String(this.agendamentoForm.valor_cobrado).trim() === "")
                    || (this.valorCobradoAutoCriacaoProcId != null);
                if (v != null && deveAutopreencher) {
                    this.agendamentoForm.valor_cobrado = String(Number(v).toFixed(2));
                    this.valorCobradoAutoCriacaoProcId = pid != null ? String(pid) : null;
                }
            } catch (_) { }
        },
        aoDigitarValorCobradoCriacao() {
            try { this.valorCobradoAutoCriacaoProcId = null; } catch (_) { }
        },
        async salvarAgendamento() {
            let basePayload = {
                paciente_id: this.agendamentoForm.paciente_id,
                pessoa_id: this.agendamentoForm.pessoa_id,
                procedimento_id: this.agendamentoForm.procedimento_id,
                convenio_id: this.agendamentoForm.convenio_id ? Number(this.agendamentoForm.convenio_id) : null,
                status_id: null,
                valor_cobrado: this.agendamentoForm.valor_cobrado ? Number(String(this.agendamentoForm.valor_cobrado).replace(/[^\d.,-]/g, '').replace(',', '.')) : null,
                observacoes: this.agendamentoForm.observacoes,
                is_retorno: this.agendamentoForm.is_retorno,
            };
            this.processandoCriacao = true;
            try {
                let sessions = [];
                const pSel = (this.procedimentosFiltrados || []).find(x => String(x.id) === String(this.agendamentoForm.procedimento_id)) || (this.procedimentosLocal || []).find(x => String(x.id) === String(this.agendamentoForm.procedimento_id));
                const isTrat = !!pSel?.eh_tratamento;
                if (isTrat && Array.isArray(this.sessoesCriacao) && this.sessoesCriacao.length > 0) {
                    sessions = this.sessoesCriacao.filter(s => (s.data && s.hora));
                }
                if (!sessions.length) {
                    sessions = [{ data: this.agendamentoForm.data, hora: this.agendamentoForm.hora }];
                }
                let createdCount = 0;
                for (const s of sessions) {
                    const payload = { ...basePayload, data: s.data, hora: s.hora };
                    const resp = await window.axios.post("/agendamentos", payload);
                    const ag = resp?.data?.agendamento;
                    if (ag) {
                        createdCount++;
                        const pLocal = (this.procedimentosLocal || []).find(pr => String(pr.id) === String(ag.procedimento_id));
                        const pFilt = (this.procedimentosFiltrados || []).find(pr => String(pr.id) === String(ag.procedimento_id));
                        const procNome = pFilt?.nome || pLocal?.nome || 'Procedimento';
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
                            extendedProps: { paciente_id: ag.paciente_id, procedimento_id: ag.procedimento_id, procedimento_nome: procNome, observacoes: this.agendamentoForm.observacoes || "", pessoa_id: ag.pessoa_id, profissional_nome: ag.medico }
                        });
                        try { calendarApi.gotoDate(ag.data); } catch (e) { }
                    }
                }
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, success: createdCount > 1 ? "Agendamentos criados" : "Agendamento criado" };
                } catch (_) { }
                this.modalAgendarVisivel = false;
                this.agendamentoForm = { paciente_id: null, pessoa_id: null, procedimento_id: null, data: "", hora: "", status_id: null, valor_cobrado: "", observacoes: "", is_retorno: false };
                this.valorCobradoAutoCriacaoProcId = null;
                this.procedimentosFiltrados = [];
                this.sessoesCriacao = [];
                await this.buscarUltimosAgendamentos();
                await this.atualizarTabelaEventosDiaModal();
            } catch (e) {
                const msg = e?.response?.data?.errors ? Object.values(e.response.data.errors).flat().join(' • ') : 'Falha ao agendar';
                try {
                    const fp = (this.$page?.props?.flash ?? {});
                    this.$page.props.flash = { ...fp, error: String(msg || "Falha ao agendar") };
                } catch (_) { }
            } finally {
                this.processandoCriacao = false;
            }
        },
        async salvarAgendarOuEditar() {
            if (this.isEditMode) {
                if (this.podeReagendarAgendamentoCancelado()) {
                    await this.reagendarSessaoTratamento();
                } else {
                    await this.salvarEdicaoAgendamento();
                }
                this.isEditMode = false;
                this.modalAgendarVisivel = false;
                return;
            }
            await this.salvarAgendamento();
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
                        extendedProps: {
                            paciente_id: r.paciente_id,
                            procedimento_id: r.procedimento_id,
                            procedimento_nome: r.procedimento || "",
                            status: r.status || "",
                            observacoes: r.observacoes || "",
                            createdEm: r.criado_em || "",
                            sessao_numero: r.sessao_numero ?? null,
                            sessao_total: r.sessao_total ?? null,
                            pessoa_id: r.pessoa_id ?? null,
                            profissional_nome: r.medico || ""
                        }
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
            } catch (e) { }
        },
        formatarHora(dataStr) {
            if (!dataStr) return "";
            try {
                const date = new Date(dataStr);
                return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return "";
            }
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
                if (ep?.procedimento_nome) {
                    const n = ep?.sessao_numero != null ? Number(ep.sessao_numero) : null;
                    const t = ep?.sessao_total != null ? Number(ep.sessao_total) : null;
                    if (n != null && t != null && t > 0) {
                        return `${ep.procedimento_nome} ${n}/${t}`;
                    }
                    return ep.procedimento_nome;
                }
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
        <PageHeader title="Agendamento" pageTitle="Recepção" />

        <BRow>
            <BCol cols="12">
                <BRow>
                    <BCol xl="3">
                        <BCard no-body class="card-h-100">
                            <BCardBody>
                                <BButton variant="primary" class="w-100 mb-3" id="btn-new-event"
                                    @click="abrirNovoAgendamento">
                                    <i class="mdi mdi-plus"></i> Novo Agendamento
                                </BButton>
                                
                                <div class="form-check form-switch form-switch-md mb-2" dir="ltr">
                                    <input type="checkbox" class="form-check-input" id="toggle-bg-calendario" v-model="habilitarCorFundoCalendario">
                                    <label class="form-check-label" for="toggle-bg-calendario">Exibir listras de fundo do calendário</label>
                                </div>

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
                                        <div v-if="agendasHoje.length === 0" class="text-muted small mb-2">Nenhum
                                            profissional com agenda nesta data.</div>
                                        <div v-for="(ag, idx) in agendasHoje" :key="`${ag.pessoa_id}-${idx}`"
                                            class="external-event fc-event d-flex align-items-center"
                                            :class="[paletaMedicos[idx % paletaMedicos.length].bg, paletaMedicos[idx % paletaMedicos.length].text]"
                                            :data-class="paletaMedicos[idx % paletaMedicos.length].bg">
                                            <i class="mdi mdi-checkbox-blank-circle me-2"></i>
                                            <div class="flex-grow-1" style="min-width:0">
                                                <div class="d-flex align-items-center" style="min-width:0">
                                                    <span class="text-truncate flex-grow-1" :title="ag.nome">{{ ag.nome
                                                    }}</span>
                                                    <span v-if="!diaInteiro(ag)"
                                                        class="ms-2 small text-muted text-nowrap">({{
                                                            formatarIntervaloAgenda(ag) }})</span>
                                                </div>
                                                <div v-if="ag.especialidades" class="text-muted small text-truncate">{{
                                                    ag.especialidades }}</div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </BCardBody>
                        </BCard>
                        <div>
                            <h5 class="mb-1">Últimos agendamentos</h5>
                            <p class="text-muted">Acompanhe os últimos agendamentos</p>
                            <simpleBar class="upcoming-events pe-2 me-n1 mb-3" data-simplebar="init"
                                style="height: 400px">
                                <BCard no-body class="mb-3" v-for="event in eventosAtuais" :key="event.id">
                                    <BCardBody>
                                        <div class="d-flex mb-3">
                                            <div class="flex-grow-1">
                                                <i
                                                    :class="`mdi mdi-checkbox-blank-circle me-2 ${event.classNames}`"></i><span
                                                    class="fw-medium">{{ formatarData(event.start) }}</span>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="fs-10 text-muted ms-auto">{{ obterTextoBadgeEvento(event)
                                                }}</span>
                                            </div>
                                        </div>
                                        <h6 class="card-title fs-16">{{ event.title }}</h6>
                                        <p class="text-muted text-truncate-two-lines mb-0">{{
                                            obterNomeProcedimentoEvento(event) }}</p>
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

        <Offcanvas size="xl" :modelValue="modalAgendarVisivel"
            :title="isEditMode ? 'Editar Agendamento' : 'Novo Agendamento'"
            :nameButton="isEditMode ? (podeReagendarAgendamentoCancelado() ? 'Reagendar' : 'Salvar') : 'Agendar'"
            :processing="isEditMode ? processandoEdicao : processandoCriacao"
            :z-index="2000" :backdropZIndex="1990"
            @update:modelValue="modalAgendarVisivel = $event" @save="salvarAgendarOuEditar">
            <div v-if="carregandoDadosOffcanvas" key="skeleton" class="placeholder-wave">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Paciente</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Convênio</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Procedimento</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Profissional</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Data *</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hora</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Valor Cobrado</label>
                        <div class="placeholder col-12 rounded " style="height: 38px; background-color: #adb5bd;"></div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observações</label>
                        <div class="placeholder col-12 rounded " style="height: 86px; background-color: #adb5bd;"></div>
                    </div>
                </div>
            </div>
            <div v-else :key="chaveRenderizacaoModalEvento" class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Paciente</label>
                    <SuggestInput v-model="termoBuscaPaciente" :suggestions="pacientesSugestoesCriacao" :loading="false"
                        :show="mostrarSugestoesPaciente" :disabled="agendamentoEstaPago" placeholder="Buscar paciente por nome ou CPF"
                        keyPrefix="sug-pac" primaryTextProp="nome" secondaryTextProp="cpf"
                        @focus="mostrarSugestoesPaciente = true" @blur="onBlurSugestoesPaciente"
                        @select="selecionarSugestaoPaciente" />
                </div>

                <div class="col-md-12">
                    <label class="form-label">Convênio</label>
                    <select ref="selConvenioCriacao" data-choices v-model="agendamentoForm.convenio_id" class="form-select"
                        :disabled="!agendamentoForm.paciente_id || agendamentoEstaPago"
                        @change="agendamentoForm.convenio_id = $event.detail ? $event.detail.value : $event.target.value">
                        <option :value="null">Selecione</option>
                        <option v-for="c in conveniosPacienteCriacao" :key="c.id" :value="c.id">{{ c.descricao }}
                        </option>
                    </select>
                    <div v-if="agendamentoForm.paciente_id && !carregandoConveniosPacienteCriacao && (!conveniosPacienteCriacao || conveniosPacienteCriacao.length === 0)"
                        class="text-muted small mt-1">
                        Paciente sem convênio ativo
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Procedimento</label>
                    <select ref="selProcedimentoCriacao" data-choices v-model="agendamentoForm.procedimento_id" class="form-select"
                        :disabled="!agendamentoForm.convenio_id || agendamentoEstaPago"
                        @change="agendamentoForm.procedimento_id = $event.detail ? $event.detail.value : $event.target.value; aoAlterarProcedimento()">
                        <option :value="null">Selecione</option>
                        <option v-for="p in procedimentosFiltrados" :key="p.id" :value="p.id">{{ p.nome }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Profissional</label>
                    <select ref="selProfissionalCriacao" data-choices v-model="agendamentoForm.pessoa_id" class="form-select"
                        :disabled="!agendamentoForm.procedimento_id"
                        @change="agendamentoForm.pessoa_id = $event.detail ? $event.detail.value : $event.target.value">
                        <option :value="null">Selecione</option>
                        <option v-for="d in listaProfissionaisCriacao" :key="d.id" :value="d.id">{{ d.nome }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data *</label>
                    <div v-if="!isEditMode">
                        <flatPickr v-model="agendamentoForm.data" class="form-control" :config="opcoesFlatpickrData"
                            placeholder="Selecione a data" />
                    </div>
                    <div v-else>
                        <flatPickr v-model="agendamentoForm.data" class="form-control" :config="opcoesFlatpickrDataEdicao"
                            placeholder="Selecione a data" />
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hora</label>
                    <flatPickr v-model="agendamentoForm.hora" class="form-control" :config="opcoesFlatpickrHora"
                        placeholder="Selecione a hora" />
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor Cobrado</label>
                    <input v-model="agendamentoForm.valor_cobrado" @input="aoDigitarValorCobradoCriacao"
                        @blur="onBlurInputValorCobrado" @focus="$event.target.select()" type="text" class="form-control"
                        placeholder="0,00" :disabled="agendamentoForm.is_retorno || agendamentoEstaPago" />
                    <div v-if="!isEditMode" class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="isRetornoCheckbox"
                            v-model="agendamentoForm.is_retorno">
                        <label class="form-check-label" for="isRetornoCheckbox">É Retorno?</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Observações</label>
                    <textarea v-model="agendamentoForm.observacoes" class="form-control" rows="3" maxlength="500"
                        placeholder="Anotações gerais" :disabled="isEditMode && podeReagendarAgendamentoCancelado()"></textarea>
                </div>
                <template v-if="!isEditMode && sessoesCriacao && sessoesCriacao.length">
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ri-calendar-line text-primary"></i>
                            <span class="fw-medium">Sessões do tratamento</span>
                            <span class="text-muted">({{ sessoesCriacao.length }} sessões)</span>
                        </div>
                    </div>
                    <div class="col-12">
                        <template v-for="(s, idx) in sessoesCriacao" :key="'sess-row-' + idx">
                            <div v-if="idx > 0" class="session-item">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label"><span class="session-badge">Sessão {{ idx + 1 }}/{{
                                            sessoesCriacao.length }}</span> Data</label>
                                        <flatPickr v-model="s.data" class="form-control" :config="opcoesFlatpickrData"
                                            placeholder="Selecione a data" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Hora</label>
                                        <flatPickr v-model="s.hora" class="form-control" :config="opcoesFlatpickrHora"
                                            placeholder="Selecione a hora" />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </Offcanvas>

        <Modal :modelValue="modalCancelamento" title="Cancelar agendamento" nameButton="Sim, cancelar"
            @update:modelValue="modalCancelamento = $event" @save="confirmarCancelamentoAgendamento" :z-index="1060"
            :backdrop-z-index="1055">
            <p>Cancelar agendamento? Esta ação marcará o agendamento como cancelado.</p>
        </Modal>

        <Modal :modelValue="modalEventosDiaVisivel" :title="tituloModalEventosDia" :nameButton="'Fechar'"
            :processing="false" :disableClose="bloquearAcoesModalEventosDia" size="xl" customWidth="95vw"
            @update:modelValue="modalEventosDiaVisivel = $event">
            <SimpleTable :key="chaveTabelaEventosDia" title="Lista de agendamentos" :items="eventosDiaGrid()"
                :columns="[{ key: 'id', label: 'ID', width: '50px' }, { key: 'paciente', label: 'Paciente' }, { key: 'procedimento', label: 'Procedimento' }, { key: 'medico', label: 'Médico' }, { key: 'hora', label: 'Hora' }, { key: 'status', label: 'Status' }]"
                has-actions :searchable="true" searchPlaceholder="Buscar agendamento..."
                :searchFields="['paciente', 'procedimento', 'medico']" emptyTitle="Nenhum agendamento encontrado">



                <template #actions="{ item }">
                    <button type="button" class="btn btn-sm btn-light" title="Reagendar"
                        :disabled="bloquearAcoesModalEventosDia || acoesLoadingEventosDia?.[item.id]?.edit"
                        @click="abrirReagendarSessaoDaLista(item.id)">
                        <i class="ri-edit-line me-1"></i> Reagendar
                    </button>
                    <button v-if="!String(item.status || '').toLowerCase().includes('cancel')" type="button"
                        class="btn btn-sm btn-danger" title="Cancelar"
                        :disabled="bloquearAcoesModalEventosDia || acoesLoadingEventosDia?.[item.id]?.delete"
                        @click="cancelarAgendamentoPorId(item)">
                        <i class="ri-close-line me-1"></i> Cancelar
                    </button>
                </template>
            </SimpleTable>
        </Modal>


    </Layout>
</template>
<style>
.fc-theme-standard {
    --fc-today-bg-color: transparent;
}

.fc .fc-day-today {
    background-color: transparent !important;
}

.fc .fc-daygrid-day.fc-day-today,
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-frame,
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-bg,
.fc .fc-timegrid-col.fc-day-today,
.fc .fc-timegrid-col.fc-day-today .fc-timegrid-col-frame,
.fc .fc-list-day.fc-day-today,
.fc .fc-list-day-cushion.fc-day-today {
    background-color: transparent !important;
}

.fc-popover {
    display: none !important;
}

.session-item {
    background-color: rgba(9, 152, 133, 0.1) !important;
    border: 1px solid rgba(9, 152, 133, 0.15);
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
}

.session-item .form-label {
    font-size: 12px;
    color: #6c757d;
}

.session-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    background: rgba(9, 152, 133, 0.12);
    color: #099885;
    font-weight: 500;
    font-size: 11px;
    margin-right: 8px;
}

.bg-teal-subtle {
    background-color: rgba(32, 201, 151, 0.14) !important;
}

.text-teal {
    color: #20c997 !important;
}

.agenda-stripe.bg-teal-subtle {
    background-color: rgb(32, 201, 151);
}

.bg-purple-subtle {
    background-color: rgba(111, 66, 193, 0.14) !important;
}

.text-purple {
    color: #6f42c1 !important;
}

.agenda-stripe.bg-purple-subtle {
    background-color: rgb(111, 66, 193);
}

.bg-pink-subtle {
    background-color: rgba(214, 51, 132, 0.14) !important;
}

.text-pink {
    color: #d63384 !important;
}

.agenda-stripe.bg-pink-subtle {
    background-color: rgb(214, 51, 132);
}

.bg-orange-subtle {
    background-color: rgba(253, 126, 20, 0.14) !important;
}

.text-orange {
    color: #fd7e14 !important;
}

.agenda-stripe.bg-orange-subtle {
    background-color: rgb(253, 126, 20);
}

.bg-indigo-subtle {
    background-color: rgba(102, 16, 242, 0.12) !important;
}

.text-indigo {
    color: #6610f2 !important;
}

.agenda-stripe.bg-indigo-subtle {
    background-color: rgb(102, 16, 242);
}

.bg-brown-subtle {
    background-color: rgba(121, 85, 72, 0.14) !important;
}

.text-brown {
    color: #795548 !important;
}

.agenda-stripe.bg-brown-subtle {
    background-color: rgb(121, 85, 72);
}

.bg-lime-subtle {
    background-color: rgba(132, 204, 22, 0.16) !important;
}

.text-lime {
    color: #3f6212 !important;
}

.agenda-stripe.bg-lime-subtle {
    background-color: rgb(132, 204, 22);
}

.bg-sky-subtle {
    background-color: rgba(56, 189, 248, 0.14) !important;
}

.text-sky {
    color: #0284c7 !important;
}

.agenda-stripe.bg-sky-subtle {
    background-color: rgb(56, 189, 248);
}

:deep(.fc-event) {
    border: none !important;
    border-radius: 6px !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.1s ease, box-shadow 0.1s ease;
}

:deep(.fc-event:hover) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 5 !important;
}

:deep(.fc-timegrid-slot) {
    height: 2.5em !important;
}

:deep(.fc-col-header-cell) {
    padding: 8px 0 !important;
    background-color: #f8f9fa !important;
    font-weight: 600 !important;
    text-transform: capitalize;
}
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

.agenda-stripe.bg-success-subtle {
    background-color: rgb(25, 135, 84);
}

.agenda-stripe.bg-info-subtle {
    background-color: rgb(13, 202, 240);
}

.agenda-stripe.bg-warning-subtle {
    background-color: rgb(255, 193, 7);
}

.agenda-stripe.bg-danger-subtle {
    background-color: rgb(220, 53, 69);
}

.agenda-stripe.bg-primary-subtle {
    background-color: rgb(53, 119, 241);
}

.agenda-stripe.bg-secondary-subtle {
    background-color: rgb(108, 117, 125);
}

.sombra-sugestoes {
    box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .08);
}

.sug-card {
    border: 1px solid rgba(0, 0, 0, .12);
    border-radius: .5rem;
    overflow: hidden;
    background-color: #fff;
}

.sug-item {
    padding: .625rem .875rem;
}

.sug-item:hover {
    background-color: rgba(9, 152, 133, 0.08);
}

:deep(.choices__placeholder) {
    color: #6c757d;
}

:deep(.choices__list--dropdown .choices__item) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(.choices__inner) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
