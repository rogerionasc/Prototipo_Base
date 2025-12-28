<template>
    <form class="row g-3 needs-validation" novalidate ref="formEl" @submit.prevent="submit">
        <BTabs nav-class="nav-tabs-custom text-muted">
            <BTab title="Dados Pessoais">
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome</label>
                        <span class="text-danger ms-1">*</span>
                        <input v-model="form.nome" type="text" class="form-control" id="nome"
                            placeholder="Nome completo" required>
                        <div class="invalid-feedback">
                            Por favor, insira o nome completo.
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <span class="text-danger ms-1">*</span>
                        <input v-model="form.cpf" v-mask="'###.###.###-##'" type="text" class="form-control"
                            :class="{ 'is-invalid': form.errors.cpf }" id="cpf" placeholder="000.000.000-00" required>
                        <div class="invalid-feedback">
                            {{ form.errors.cpf || 'Por favor, insira o CPF.' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="rg" class="form-label">RG</label>
                        <input v-model="form.rg" v-mask="'###.###.###'" type="text" class="form-control" id="rg"
                            placeholder="000.000.000">
                    </div>
                    <div class="col-md-3">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select v-model="form.sexo" data-choices class="form-select mb-0" id="sexo" ref="sexoSelect">
                            <option selected disabled value="">Selecione...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="dataNascimento" class="form-label">Data de Nascimento</label>
                        <flatPickr v-model="form.data_nascimento" id="dataNascimento" class="form-control"
                            :config="flatpickrOptions" placeholder="Selecione a data" />
                    </div>
                    <div class="col-md-3">
                        <label for="naturalidade" class="form-label">Naturalidade</label>
                        <input v-model="form.naturalidade" type="text" class="form-control" id="naturalidade"
                            placeholder="Cidade/UF">
                    </div>
                    <div class="col-md-3">
                        <label for="estadoCivil" class="form-label">Estado Civil</label>
                        <select v-model="form.estado_civil_id" class="form-select mb-0" id="estadoCivil" data-choices
                            ref="estadoCivilSelect" :class="{ 'is-invalid': form.errors.estado_civil_id }">
                            <option selected disabled value="">Selecione...</option>
                            <option v-for="ec in estadosCivis" :key="ec.id" :value="ec.id">{{ ec.descricao }}</option>
                        </select>
                        <div class="invalid-feedback">
                            {{ form.errors.estado_civil_id }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="convenio" class="form-label">Convênio</label>
                        <select v-model="form.convenio_id" data-choices class="form-select" id="convenio"
                            ref="convenioSelect">
                            <option selected disabled value="">Selecione...</option>
                            <option v-for="cv in convenios" :key="cv.id" :value="cv.id">{{ cv.descricao }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="altura" class="form-label">Altura (m)</label>
                        <input v-model.number="form.altura" type="number" step="0.01" class="form-control" id="altura"
                            placeholder="1.75">
                    </div>
                    <div class="col-md-2">
                        <label for="peso" class="form-label">Peso (kg)</label>
                        <input v-model.number="form.peso" type="number" step="0.1" class="form-control" id="peso"
                            placeholder="70.5">
                    </div>
                    <div class="col-md-2">
                        <label for="corPele" class="form-label">Cor da Pele</label>
                        <input v-model="form.cor_pele" type="text" class="form-control" id="corPele"
                            placeholder="Cor da pele">
                    </div>
                    <div class="col-md-3">
                        <label for="tipoSanguineo" class="form-label">Tipo Sanguíneo</label>
                        <select v-model="form.tipo_sanguineo_id" data-choices class="form-select" id="tipoSanguineo"
                            ref="tipoSanguineoSelect" :class="{ 'is-invalid': form.errors.tipo_sanguineo_id }">
                            <option selected disabled value="">Selecione...</option>
                            <option v-for="ts in tiposSanguineos" :key="ts.id" :value="ts.id">{{ ts.descricao }}
                            </option>
                        </select>
                        <div class="invalid-feedback">
                            {{ form.errors.tipo_sanguineo_id }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input v-model="form.telefone" v-mask="'(##) ##### ####'" type="text" class="form-control"
                            id="telefone" placeholder="(00) 0000-0000">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input v-model="form.email" type="email" class="form-control" id="email"
                            placeholder="email@dominio.com">
                    </div>
                    <div class="col-md-3">
                        <label for="celular" class="form-label">Celular</label>
                        <input v-model="form.celular" v-mask="'(##) #####-####'" type="text" class="form-control"
                            id="celular" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-3">
                        <label for="profissao" class="form-label">Profissão</label>
                        <input v-model="form.profissao" type="text" class="form-control" id="profissao"
                            placeholder="Profissão">
                    </div>
                    <div class="col-md-3">
                        <label for="escolaridade" class="form-label">Escolaridade</label>
                        <input v-model="form.escolaridade" type="text" class="form-control" id="escolaridade"
                            placeholder="Escolaridade">
                    </div>

                    <div class="col-md-3">
                        <label for="canalAviso" class="form-label">Canal de Aviso</label>
                        <select v-model="form.canal_aviso_id" data-choices class="form-select" id="canalAviso"
                            ref="canalAvisoSelect" :class="{ 'is-invalid': form.errors.canal_aviso_id }">
                            <option selected disabled value="">Selecione...</option>
                            <option v-for="ca in canaisAviso" :key="ca.id" :value="ca.id">{{ ca.nome }}</option>
                        </select>
                        <div class="invalid-feedback">
                            {{ form.errors.canal_aviso_id }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="receberAvisos" class="form-label">Receber Avisos</label>
                        <div class="form-check">
                            <input v-model="form.receber_avisos" class="form-check-input" type="checkbox"
                                id="receberAvisos">
                            <label class="form-check-label" for="receberAvisos">Sim</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="nomeMae" class="form-label">Nome da Mãe</label>
                        <input v-model="form.nome_mae" type="text" class="form-control" id="nomeMae"
                            placeholder="Nome da mãe">
                    </div>
                    <div class="col-md-6">
                        <label for="nomePai" class="form-label">Nome do Pai</label>
                        <input v-model="form.nome_pai" type="text" class="form-control" id="nomePai"
                            placeholder="Nome do pai">
                    </div>
                    <div class="col-md-12">
                        <label for="temResponsavel" class="form-label">Paciente possui responsável?</label>
                        <div class="form-check">
                            <input v-model="form.tem_responsavel" class="form-check-input" type="checkbox"
                                id="temResponsavel">
                            <label class="form-check-label" for="temResponsavel">Sim</label>
                        </div>
                    </div>

                    <div v-if="form.tem_responsavel" class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="responsavelNome" class="form-label">Nome do Responsável</label>
                            <span class="text-danger ms-1">*</span>
                            <input v-model="form.responsavel_nome" :required="form.tem_responsavel" type="text" class="form-control" :class="{ 'is-invalid': form.errors.responsavel_nome }" id="responsavelNome" placeholder="Nome completo do responsável">
                            <div class="invalid-feedback">
                                {{ form.errors.responsavel_nome || 'Campo obrigatório quando há responsável.' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="responsavelParentesco" class="form-label">Parentesco</label>
                            <span class="text-danger ms-1">*</span>
                            <select v-model="form.responsavel_parentesco_id" :required="form.tem_responsavel" data-choices class="form-select" id="responsavelParentesco" ref="parentescoSelect" :class="{ 'is-invalid': form.errors.responsavel_parentesco_id }">
                                <option selected disabled value="">Selecione...</option>
                                <option v-for="p in parentescos" :key="p.id" :value="p.id">{{ p.descricao }}</option>
                            </select>
                            <div class="invalid-feedback">
                                {{ form.errors.responsavel_parentesco_id || 'Campo obrigatório quando há responsável.' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="responsavelDataNascimento" class="form-label">Data de Nascimento</label>
                            <flatPickr v-model="form.responsavel_data_nascimento" id="responsavelDataNascimento" class="form-control" :config="flatpickrOptions" placeholder="Selecione a data" />
                        </div>
                        <div class="col-md-3">
                            <label for="responsavelCpf" class="form-label">CPF</label>
                            <input v-model="form.responsavel_cpf" v-mask="'###.###.###-##'" type="text" class="form-control" id="responsavelCpf" placeholder="000.000.000-00">
                        </div>
                        <div class="col-md-3">
                            <label for="responsavelRg" class="form-label">RG</label>
                            <input v-model="form.responsavel_rg" v-mask="'###.###.###'" type="text" class="form-control" id="responsavelRg" placeholder="000.000.000">
                        </div>
                        <div class="col-md-3">
                            <label for="responsavelCelular" class="form-label">Celular</label>
                            <input v-model="form.responsavel_celular" v-mask="'(##) #####-####'" type="text" class="form-control" id="responsavelCelular" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-3">
                            <label for="responsavelTelefone" class="form-label">Telefone</label>
                            <input v-model="form.responsavel_telefone" v-mask="'(##) ##### ####'" type="text" class="form-control" id="responsavelTelefone" placeholder="(00) 0000-0000">
                        </div>
                        <div class="col-md-6">
                            <label for="responsavelEmail" class="form-label">E-mail</label>
                            <input v-model="form.responsavel_email" type="email" class="form-control" id="responsavelEmail" placeholder="email@dominio.com">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea v-model="form.observacoes" class="form-control" id="observacoes" rows="4"
                            placeholder="Anotações gerais"></textarea>
                    </div>
                </div>
            </BTab>
            <BTab title="Endereço">
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label for="cep" class="form-label">CEP</label>
                        <input v-model="form.cep" type="text" class="form-control" id="cep" placeholder="00000-000">
                    </div>
                    <div class="col-md-6">
                        <label for="endereco" class="form-label">Endereço</label>
                        <input v-model="form.endereco" type="text" class="form-control" id="endereco"
                            placeholder="Rua, Avenida, Travessa">
                    </div>
                    <div class="col-md-3">
                        <label for="numero" class="form-label">Número</label>
                        <input v-model="form.numero" type="text" class="form-control" id="numero" placeholder="Número">
                    </div>
                    <div class="col-md-4">
                        <label for="bairro" class="form-label">Bairro</label>
                        <input v-model="form.bairro" type="text" class="form-control" id="bairro" placeholder="Bairro">
                    </div>
                    <div class="col-md-4">
                        <label for="cidade" class="form-label">Cidade</label>
                        <input v-model="form.cidade" type="text" class="form-control" id="cidade" placeholder="Cidade">
                    </div>
                    <div class="col-md-4">
                        <label for="complemento" class="form-label">Complemento</label>
                        <input v-model="form.complemento" type="text" class="form-control" id="complemento"
                            placeholder="Apartamento, Bloco, etc.">
                    </div>
                </div>
            </BTab>
        </BTabs>
    </form>
</template>
<script setup>
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/l10n/pt.js";
const flatpickrOptions = { altInput: true, altFormat: "d M, Y", dateFormat: "Y-m-d", locale: "pt" };
import { useForm } from "@inertiajs/vue3";
import { ref, defineExpose, onMounted, nextTick, watch, toRef } from "vue";
const { estadosCivis, tiposSanguineos, canaisAviso, convenios, parentescos } = defineProps({
    estadosCivis: { type: Array, default: () => [] },
    tiposSanguineos: { type: Array, default: () => [] },
    canaisAviso: { type: Array, default: () => [] },
    convenios: { type: Array, default: () => [] },
    parentescos: { type: Array, default: () => [] },
});
const formEl = ref(null);
const sexoSelect = ref(null);
const estadoCivilSelect = ref(null);
const tipoSanguineoSelect = ref(null);
const canalAvisoSelect = ref(null);
const convenioSelect = ref(null);
const parentescoSelect = ref(null);
const form = useForm({
    nome: "",
    cpf: "",
    rg: "",
    sexo: "",
    data_nascimento: "",
    naturalidade: "",
    estado_civil_id: "",
    convenio_id: "",
    altura: null,
    peso: null,
    cor_pele: "",
    tipo_sanguineo_id: "",
    telefone: "",
    email: "",
    celular: "",
    profissao: "",
    escolaridade: "",
    canal_aviso_id: "",
    receber_avisos: false,
    nome_mae: "",
    nome_pai: "",
    observacoes: "",
    cep: "",
    endereco: "",
    numero: "",
    bairro: "",
    cidade: "",
    complemento: "",
    tem_responsavel: false,
    responsavel_nome: "",
    responsavel_parentesco_id: "",
    responsavel_cpf: "",
    responsavel_rg: "",
    responsavel_data_nascimento: "",
    responsavel_celular: "",
    responsavel_telefone: "",
    responsavel_email: "",
});
watch(() => form.sexo, async (v) => { await nextTick(); if (window.syncChoiceValue && sexoSelect.value) window.syncChoiceValue(sexoSelect.value, v || ""); }, { immediate: true });
watch(() => form.estado_civil_id, async (v) => { await nextTick(); if (window.syncChoiceValue && estadoCivilSelect.value) window.syncChoiceValue(estadoCivilSelect.value, v != null ? String(v) : ""); }, { immediate: true });
watch(() => form.tipo_sanguineo_id, async (v) => { await nextTick(); if (window.syncChoiceValue && tipoSanguineoSelect.value) window.syncChoiceValue(tipoSanguineoSelect.value, v != null ? String(v) : ""); }, { immediate: true });
watch(() => form.canal_aviso_id, async (v) => { await nextTick(); if (window.syncChoiceValue && canalAvisoSelect.value) window.syncChoiceValue(canalAvisoSelect.value, v != null ? String(v) : ""); }, { immediate: true });
watch(() => form.convenio_id, async (v) => { await nextTick(); if (window.syncChoiceValue && convenioSelect.value) window.syncChoiceValue(convenioSelect.value, v != null ? String(v) : ""); }, { immediate: true });
watch(() => form.responsavel_parentesco_id, async (v) => { await nextTick(); if (window.syncChoiceValue && parentescoSelect.value) window.syncChoiceValue(parentescoSelect.value, v != null ? String(v) : ""); }, { immediate: true });
const submit = (onSuccess, hooks = {}) => {
    if (formEl.value && !formEl.value.checkValidity()) {
        formEl.value.classList.add('was-validated');
        return;
    }
    form.post("/pacientes", {
        onStart: () => { try { hooks.onStart?.(); } catch (_) {} },
        onFinish: () => { try { hooks.onFinish?.(); } catch (_) {} },
        onSuccess: async () => {
            formEl.value?.classList.remove('was-validated');
            form.clearErrors();
            form.reset();
            await nextTick();
            await syncChoices();
            if (onSuccess) onSuccess();
        },
    });
};
const submitUpdate = (id, onSuccess, hooks = {}) => {
    if (formEl.value && !formEl.value.checkValidity()) {
        formEl.value.classList.add('was-validated');
        return;
    }
    form.put(`/pacientes/${id}`, {
        onStart: () => { try { hooks.onStart?.(); } catch (_) {} },
        onFinish: () => { try { hooks.onFinish?.(); } catch (_) {} },
        onSuccess: () => {
            formEl.value?.classList.remove('was-validated');
            if (onSuccess) onSuccess();
        },
    });
};
const syncChoices = async () => {
    await nextTick();
    if (window.syncChoiceValue && sexoSelect.value) window.syncChoiceValue(sexoSelect.value, form.sexo || "");
    if (window.syncChoiceValue && estadoCivilSelect.value) window.syncChoiceValue(estadoCivilSelect.value, form.estado_civil_id != null && form.estado_civil_id !== '' ? String(form.estado_civil_id) : "");
    if (window.syncChoiceValue && tipoSanguineoSelect.value) window.syncChoiceValue(tipoSanguineoSelect.value, form.tipo_sanguineo_id != null && form.tipo_sanguineo_id !== '' ? String(form.tipo_sanguineo_id) : "");
    if (window.syncChoiceValue && canalAvisoSelect.value) window.syncChoiceValue(canalAvisoSelect.value, form.canal_aviso_id != null && form.canal_aviso_id !== '' ? String(form.canal_aviso_id) : "");
    if (window.syncChoiceValue && convenioSelect.value) window.syncChoiceValue(convenioSelect.value, form.convenio_id != null && form.convenio_id !== '' ? String(form.convenio_id) : "");
    if (window.syncChoiceValue && parentescoSelect.value) window.syncChoiceValue(parentescoSelect.value, form.responsavel_parentesco_id != null && form.responsavel_parentesco_id !== '' ? String(form.responsavel_parentesco_id) : "");
};
defineExpose({ submit, submitUpdate, form, syncChoices, processingRef: toRef(form, "processing") });
onMounted(async () => {
    await nextTick();
    if (window.initChoices) window.initChoices();
    await nextTick();
    if (sexoSelect.value) sexoSelect.value.addEventListener("change", (e) => { form.sexo = e?.target?.value ?? form.sexo; });
    if (estadoCivilSelect.value) estadoCivilSelect.value.addEventListener("change", (e) => { form.estado_civil_id = e?.target?.value ?? form.estado_civil_id; });
    if (tipoSanguineoSelect.value) tipoSanguineoSelect.value.addEventListener("change", (e) => { form.tipo_sanguineo_id = e?.target?.value ?? form.tipo_sanguineo_id; });
    if (canalAvisoSelect.value) canalAvisoSelect.value.addEventListener("change", (e) => { form.canal_aviso_id = e?.target?.value ?? form.canal_aviso_id; });
    if (convenioSelect.value) convenioSelect.value.addEventListener("change", (e) => { form.convenio_id = e?.target?.value ?? form.convenio_id; });
    if (parentescoSelect.value) parentescoSelect.value.addEventListener("change", (e) => { form.responsavel_parentesco_id = e?.target?.value ?? form.responsavel_parentesco_id; });
    if (window.syncChoiceValue && sexoSelect.value) window.syncChoiceValue(sexoSelect.value, form.sexo || "");
    if (window.syncChoiceValue && estadoCivilSelect.value) window.syncChoiceValue(estadoCivilSelect.value, form.estado_civil_id != null && form.estado_civil_id !== '' ? String(form.estado_civil_id) : "");
    if (window.syncChoiceValue && tipoSanguineoSelect.value) window.syncChoiceValue(tipoSanguineoSelect.value, form.tipo_sanguineo_id != null && form.tipo_sanguineo_id !== '' ? String(form.tipo_sanguineo_id) : "");
    if (window.syncChoiceValue && canalAvisoSelect.value) window.syncChoiceValue(canalAvisoSelect.value, form.canal_aviso_id != null && form.canal_aviso_id !== '' ? String(form.canal_aviso_id) : "");
    if (window.syncChoiceValue && convenioSelect.value) window.syncChoiceValue(convenioSelect.value, form.convenio_id != null && form.convenio_id !== '' ? String(form.convenio_id) : "");
    if (window.syncChoiceValue && parentescoSelect.value) window.syncChoiceValue(parentescoSelect.value, form.responsavel_parentesco_id != null && form.responsavel_parentesco_id !== '' ? String(form.responsavel_parentesco_id) : "");
});
</script>
<style scoped>
.choices {
    margin-bottom: 0 !important;
}
</style>
