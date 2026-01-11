<template>
  <div class="position-relative">
    <input :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" @focus="$emit('focus')" @blur="$emit('blur')" type="text" class="form-control mb-2" :placeholder="placeholder" :disabled="disabled" />
    <div v-if="show && modelValue" class="card sug-card sombra-sugestoes position-absolute w-100" style="top: 100%; left: 0; max-height: 240px; overflow: auto; z-index: 1020;">
      <div class="list-group list-group-flush">
        <button v-for="o in suggestions" :key="itemKeyComputed(o)" type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center sug-item" @mousedown.prevent="$emit('select', o)">
          <slot name="item" :item="o">
            <span class="text-truncate">{{ primaryText(o) }}</span>
            <span v-if="secondaryText(o)" class="text-muted small ms-3 text-truncate">{{ secondaryText(o) }}</span>
          </slot>
        </button>
        <div v-if="loading" class="list-group-item text-muted">Buscando...</div>
        <div v-else-if="!loading && (!suggestions || suggestions.length === 0)" class="list-group-item text-muted">Nenhum resultado</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SuggestInput",
  props: {
    modelValue: { type: String, default: "" },
    suggestions: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    show: { type: Boolean, default: false },
    placeholder: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
    keyPrefix: { type: String, default: "sug" },
    itemKeyProp: { type: String, default: "id" },
    itemKeyFn: { type: Function, default: null },
    primaryTextProp: { type: String, default: "" },
    secondaryTextProp: { type: String, default: "" },
    primaryTextFn: { type: Function, default: null },
    secondaryTextFn: { type: Function, default: null },
  },
  emits: ["update:modelValue", "focus", "blur", "select"],
  methods: {
    itemKeyComputed(o) {
      let id = null;
      if (typeof this.itemKeyFn === "function") {
        try { id = this.itemKeyFn(o); } catch (_) {}
      }
      if (id == null) {
        const prop = this.itemKeyProp || "id";
        id = o && o[prop] != null ? o[prop] : Math.random();
      }
      return `${this.keyPrefix}-${id}`;
    },
    primaryText(o) {
      if (typeof this.primaryTextFn === "function") {
        try { return String(this.primaryTextFn(o) || ""); } catch (_) { return ""; }
      }
      const prop = this.primaryTextProp;
      if (prop) return String((o && o[prop]) || "");
      const candidates = ["label", "nome", "name", "descricao", "numero"];
      for (const k of candidates) {
        const v = o && o[k];
        if (v != null && String(v).trim() !== "") return String(v);
      }
      return "";
    },
    secondaryText(o) {
      if (typeof this.secondaryTextFn === "function") {
        try { return String(this.secondaryTextFn(o) || ""); } catch (_) { return ""; }
      }
      const prop = this.secondaryTextProp;
      if (prop) return String((o && o[prop]) || "");
      const candidates = ["paciente", "cpf", "email", "descricao_secundaria"];
      for (const k of candidates) {
        const v = o && o[k];
        if (v != null && String(v).trim() !== "") return String(v);
      }
      return "";
    },
  },
};
</script>

<style scoped>
.sug-card { border: 1px solid rgba(0,0,0,.12); border-radius: .5rem; overflow: hidden; background-color: #fff; }
.sug-item { padding: .625rem .875rem; }
.sug-item:hover { background-color: rgba(9, 152, 133, 0.08); }
</style>
