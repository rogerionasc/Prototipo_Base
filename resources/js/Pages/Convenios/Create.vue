<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    ref="formEl"
    @submit.prevent="submit"
  >
    <div class="row g-3 mt-2">
      <div class="col-md-6">
        <label for="descricao" class="form-label">Descrição</label>
        <span class="text-danger ms-1">*</span>
        <input
          v-model="form.descricao"
          type="text"
          class="form-control"
          id="descricao"
          placeholder="Nome do convênio"
          required
        />
        <div class="invalid-feedback">Informe a descrição.</div>
      </div>

      <div class="col-md-6">
        <label for="tipo" class="form-label">Tipo</label>
        <select
          data-choices
          ref="tipoSelect"
          v-model="form.tipo"
          class="form-select"
          id="tipo"
        >
          <option disabled value="">Selecione...</option>
          <option value="Convenio">Convênio</option>
          <option value="Particular">Particular</option>
        </select>
      </div>

      <div class="col-md-6">
        <label for="empresa" class="form-label">Empresa</label>
        <select
          v-model="form.empresa_id"
          data-choices
          class="form-select"
          id="empresa"
        >
          <option disabled value="">Selecione...</option>
          <option
            v-for="c in contas"
            :key="c.id"
            :value="c.id"
          >
            {{ c.nome }}
          </option>
        </select>
      </div>

      <div class="col-md-2">
        <label for="ans" class="form-label">ANS</label>
        <input
          v-model.number="form.ans"
          type="number"
          class="form-control"
          id="ans"
          placeholder="Código ANS"
        />
      </div>

      <div class="col-md-2">
        <label for="diasReceb" class="form-label">Dias Receb.</label>
        <input
          v-model.number="form.dias_recebimento"
          type="number"
          class="form-control"
          id="diasReceb"
          placeholder="Ex.: 30"
        />
      </div>

      <div class="col-md-2">
        <label for="diasRet" class="form-label">Dias Retorno</label>
        <input
          v-model.number="form.dias_retorno"
          type="number"
          class="form-control"
          id="diasRet"
          placeholder="Ex.: 7"
        />
      </div>
    </div>
  </form>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, defineExpose, onMounted, nextTick, watch, toRef } from "vue";

const formEl = ref(null);
const tipoSelect = ref(null);

let tipoChoices = null;

const form = useForm({
  descricao: "",
  tipo: "",
  empresa_id: "",
  ans: null,
  dias_recebimento: null,
  dias_retorno: null,
});

/* ======================
   GET CHOICES INSTANCE
====================== */
const getChoicesInstance = () => {
  return tipoSelect.value?._choicesInstance || tipoSelect.value?.choices || null;
};

/* ======================
   SYNC VUE → CHOICES
====================== */
watch(
  () => form.tipo,
  async (value) => {
    await nextTick();
    if (window.syncChoiceValue && tipoSelect.value) {
      window.syncChoiceValue(tipoSelect.value, value || "");
    }
  },
  { immediate: true }
);

/* ======================
   SYNC CHOICES → VUE
====================== */
const onTipoChange = (e) => {
  form.tipo = e?.target?.value ?? form.tipo;
};

onMounted(async () => {
  await nextTick();

  // Inicialização automática do tema
  if (window.initChoices) {
    window.initChoices();
  }

  // Captura a instância após init
  await nextTick();
  tipoChoices = getChoicesInstance();

  // Escuta mudanças do Choices
  if (tipoSelect.value) {
    tipoSelect.value.addEventListener("change", onTipoChange);
  }

  // Força valor inicial (update)
  if (window.syncChoiceValue && tipoSelect.value) {
    window.syncChoiceValue(tipoSelect.value, form.tipo || "");
  }
});

const submit = (onSuccess, hooks = {}) => {
  if (formEl.value && !formEl.value.checkValidity()) {
    formEl.value.classList.add("was-validated");
    return;
  }
  form.post("/convenios", {
    onStart: () => { try { hooks.onStart?.(); } catch (_) {} },
    onFinish: () => { try { hooks.onFinish?.(); } catch (_) {} },
    onSuccess: () => {
      formEl.value?.classList.remove("was-validated");
      if (onSuccess) onSuccess();
      form.reset();
    },
  });
};
const submitUpdate = (id, onSuccess, hooks = {}) => {
  if (formEl.value && !formEl.value.checkValidity()) {
    formEl.value.classList.add("was-validated");
    return;
  }
  form.put(`/convenios/${id}`, {
    onStart: () => { try { hooks.onStart?.(); } catch (_) {} },
    onFinish: () => { try { hooks.onFinish?.(); } catch (_) {} },
    onSuccess: () => {
      formEl.value?.classList.remove("was-validated");
      if (onSuccess) onSuccess();
    },
  });
};

defineExpose({ form, submit, submitUpdate, processingRef: toRef(form, "processing") });

</script>

<style scoped>
.choices {
  margin-bottom: 0 !important;
}
</style>
