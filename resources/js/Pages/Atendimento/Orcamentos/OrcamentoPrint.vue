<template>
  <div class="paper">
    <div class="header">
      <div class="left">
        <div class="doc-title">Orçamento</div>
        <div class="doc-number">{{ orcamento?.numero || '' }}</div>
      </div>
      <div class="right">
        <div class="meta">
          <div class="meta-item">
            <div class="meta-label">Emissão</div>
            <div class="meta-value">{{ orcamento?.data_emissao || '' }}</div>
          </div>
          <div class="meta-item">
            <div class="meta-label">Validade</div>
            <div class="meta-value">{{ orcamento?.validade || '' }}</div>
          </div>
        </div>
        <div class="status" :class="orcamento?.aprovado ? 'approved' : 'pending'">
          {{ orcamento?.aprovado ? 'Aprovado' : 'Aguardando aprovação' }}
        </div>
        <div v-if="orcamento?.faturamento_previsto" class="billing">Faturamento Previsto</div>
      </div>
    </div>

    <div class="party">
      <div class="party-label">Paciente</div>
      <div class="party-value">
        <span>{{ orcamento?.paciente_nome || '-' }}</span>
        <span v-if="orcamento?.paciente_cpf"> • CPF: {{ orcamento?.paciente_cpf }}</span>
      </div>
    </div>

    <table class="items">
      <thead>
        <tr>
          <th>Procedimento</th>
          <th class="text-end">Qtd</th>
          <th class="text-end">Valor Unit.</th>
          <th class="text-end">Valor Total</th>
          <th v-if="hasObs">Observações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="it in itens" :key="`${it.id || ''}-${it.procedimento_id}-${it.quantidade}`">
          <td>{{ it.procedimento_nome || '-' }}</td>
          <td class="text-end">{{ Number(it.quantidade || 0) }}</td>
          <td class="text-end">{{ formatCurrency(Number(it.valor_unitario || 0)) }}</td>
          <td class="text-end">{{ formatCurrency(Number(it.valor_total || 0)) }}</td>
          <td v-if="hasObs" class="obs-cell">{{ it.observacoes || '-' }}</td>
        </tr>
      </tbody>
    </table>

    <div class="totals">
      <div class="row">
        <div>Valor Bruto</div>
        <div class="val">{{ formatCurrency(Number(orcamento?.valor_bruto || 0)) }}</div>
      </div>
      <div class="row">
        <div>Desconto</div>
        <div class="val">- {{ formatCurrency(Number(orcamento?.desconto || 0)) }}</div>
      </div>
      <div class="row total">
        <div>Total</div>
        <div class="val">{{ formatCurrency(Number(orcamento?.valor_total || 0)) }}</div>
      </div>
    </div>

    <div class="signatures">
      <div class="sig">
        <div class="line"></div>
        <div class="label">Paciente</div>
      </div>
      <div class="sig">
        <div class="line"></div>
        <div class="label">Responsável</div>
      </div>
    </div>
  </div>
  <div class="print-trigger"></div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
const props = defineProps({
  orcamento: { type: Object, default: () => ({}) },
  itens: { type: Array, default: () => [] },
  autoPrint: { type: Boolean, default: true },
});
const hasObs = computed(() => {
  try {
    return Array.isArray(props.itens) && props.itens.some(it => String(it?.observacoes || '').trim() !== '');
  } catch (e) { return false; }
});
function formatCurrency(v) {
  const n = Number(v || 0);
  return n.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}
onMounted(() => {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    const skipAuto = urlParams.get('skipAutoPrint') === 'true';
    if (props.autoPrint && !skipAuto) {
      setTimeout(() => {
        try { window.print() } catch (e) {}
      }, 200);
    }
    window.getPrintHtml = () => {
      const el = document.querySelector('.paper');
      return el ? el.outerHTML : '';
    };
  } catch (e) {}
});
</script>

<style scoped>
.section-title {
  margin-top: 18px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #6b7280;
}
.paper {
  width: 190mm;
  margin: 0 auto;
  padding: 20px;
  color: #111;
  background: #fff;
  font-family: Arial, Helvetica, sans-serif;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
  padding-bottom: 10px;
  border-bottom: 1px solid #e5e7eb;
}
.header .left .doc-title {
  font-size: 22px;
  font-weight: 700;
  letter-spacing: 0.3px;
}
.header .left .doc-number {
  margin-top: 2px;
  font-size: 13px;
  color: #6b7280;
}
.header .right {
  text-align: right;
}
.header .right .meta {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}
.meta-item .meta-label {
  font-size: 12px;
  color: #6b7280;
}
.meta-item .meta-value {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
}
.status {
  display: inline-block;
  margin-top: 4px;
  padding: 3px 8px;
  font-size: 12px;
  border-radius: 999px;
  border: 1px solid #e5e7eb;
}
.status.approved {
  color: #166534;
  background: #ecfdf5;
  border-color: #34d399;
}
.status.pending {
  color: #92400e;
  background: #fffbeb;
  border-color: #fbbf24;
}
.billing {
  display: inline-block;
  margin-top: 4px;
  padding: 3px 8px;
  font-size: 12px;
  border-radius: 999px;
  border: 1px solid #93c5fd;
  color: #1d4ed8;
  background: #eff6ff;
}
.party {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 6px;
  align-items: center;
  margin-top: 10px;
}
.party-label {
  font-size: 12px;
  color: #6b7280;
}
.party-value {
  font-size: 14px;
  color: #111827;
}

.items {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  table-layout: fixed;
}
.items th, .items td {
  border: 1px solid #e5e7eb;
  padding: 8px;
  font-size: 13px;
  line-height: 1.25;
}
.items th {
  background: #f9fafb;
  text-align: left;
  font-weight: 600;
}
.items th:nth-child(2), .items td:nth-child(2) { width: 18mm; }
.items th:nth-child(3), .items td:nth-child(3) { width: 36mm; }
.items th:nth-child(4), .items td:nth-child(4) { width: 36mm; }
.items tbody tr:nth-child(even) {
  background: #fcfcfc;
}
.text-end {
  text-align: right;
  font-variant-numeric: tabular-nums;
}
.obs-cell {
  max-width: 240px;
}

.totals {
  margin-top: 8px;
  width: 340px;
  margin-left: auto;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px;
  background: #fafafa;
  font-size: 12px;
}
.totals .row {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  padding: 3px 0;
}
.totals .row.total {
  border-top: 1px dashed #d1d5db;
  margin-top: 4px;
  padding-top: 4px;
  font-weight: 700;
}
.totals .val {
  text-align: right;
  white-space: nowrap;
  line-height: 1.1;
}

.signatures {
  display: flex;
  gap: 24px;
  margin-top: 24px;
}
.sig {
  flex: 1;
  text-align: center;
}
.sig .line {
  border-bottom: 1px solid #d1d5db;
  height: 28px;
}
.sig .label {
  margin-top: 8px;
  font-size: 12px;
  color: #6b7280;
}
@media print {
  .paper {
    width: 100%;
    padding: 0;
    box-shadow: none;
  }
  * {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }
  tr, th, td { page-break-inside: avoid; }
  .print-trigger { display: none; }
}
@page {
  size: A4;
  margin: 10mm;
}
</style>
