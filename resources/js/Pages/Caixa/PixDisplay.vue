<template>
  <Head title="PIX - Tela do Caixa" />
  <div class="pix-display">
    <div class="pix-content">
      <div v-if="paymentSuccess" class="success">
        <div class="success-icon rounded-circle bg-success-subtle">
          <i class="ri-check-fill text-success"></i>
        </div>
        <div class="success-title">Pagamento realizado com sucesso</div>
        <div class="success-info" v-if="successInfo.nome || successInfo.valor">
          <span v-if="successInfo.nome">{{ successInfo.nome }}</span>
          <span v-if="successInfo.valor"> — {{ formatCurrency(successInfo.valor) }}</span>
        </div>
      </div>
      <div v-else-if="pagamentoAtual" class="present">
        <div class="pix-card">
          <div class="pix-card-body">
            <div class="meta">
              <div class="meta-item">
                <div class="label">Paciente</div>
                <div class="value name">{{ pagamentoAtual.paciente || "—" }}</div>
              </div>
              <div class="meta-item">
                <div class="label">Valor</div>
                <div class="value total">{{ formatCurrency(pagamentoValor) }}</div>
              </div>
            </div>
            <div class="qr">
              <div v-if="mpLoading" class="qr-skeleton" :style="{ width: qrSizePx, height: qrSizePx }"></div>
              <img v-else :src="qrUrl" alt="QR Code PIX" :style="{ width: qrSizePx, height: qrSizePx }" />
            </div>
            <div class="actions" v-if="payload">
              <button class="btn btn-outline-primary btn-sm" type="button" @click="copiarPayload" :disabled="copiando">
                <i class="ri-file-copy-line align-bottom me-1"></i>Copiar código
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="empty">
        <div class="caixa-name">{{ caixaNome || "—" }}</div>
        <div class="headline">Nenhuma cobrança pendente</div>
        <div class="sub">Aguardando recebimentos via PIX para este caixa</div>
      </div>
    </div>
  </div>
  </template>

  <script setup>
import { Head } from "@inertiajs/vue3";
import { ref, toRef, computed, onMounted, onUnmounted, watch } from "vue";
import axios from "axios";

  const props = defineProps({
    caixas: { type: Array, default: () => [] },
    selectedCaixaId: { type: Number, default: null },
  });
  const caixasLocal = toRef(props, "caixas");
  const caixaId = ref(props.selectedCaixaId || Number(localStorage.getItem("pix_caixa_id") || 0) || null);
  const caixaAtual = computed(() => {
    const list = Array.isArray(caixasLocal.value) ? caixasLocal.value : [];
    const id = caixaId.value;
    return list.find(c => String(c.id) === String(id));
  });
  const caixaNome = computed(() => caixaAtual.value?.descricao || "");

const pagamentoAtual = ref(null);
const polling = ref(false);
let timer = null;
const mpQrText = ref("");
const mpQrBase64 = ref("");
const mpLoading = ref(false);
const mpPaymentId = ref(null);
const mpQrCreatedAt = ref(0);
const mpQrTtlMs = 12 * 60 * 1000;
const qrExpirado = computed(() => mpQrCreatedAt.value > 0 && (Date.now() - mpQrCreatedAt.value) > mpQrTtlMs);
const windowWidth = ref(typeof window !== "undefined" ? window.innerWidth : 360);
const paymentSuccess = ref(false);
const successInfo = ref({ nome: "", valor: 0 });
let successTimer = null;
const copiando = ref(false);
const qrSizePx = computed(() => {
  const w = windowWidth.value || 360;
  const size = Math.min(560, Math.max(240, Math.floor(w * 0.7)));
  return `${size}px`;
});
const qrDim = computed(() => {
  const w = windowWidth.value || 360;
  return Math.min(560, Math.max(240, Math.floor(w * 0.7)));
});

  function formatCurrency(n) {
    const v = Number(n || 0);
    try {
      return v.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
    } catch (e) {
      return `R$ ${v.toFixed(2)}`.replace(".", ",");
    }
  }

async function carregarAtual() {
  if (!caixaId.value) { pagamentoAtual.value = null; return; }
  try {
    const resp = await axios.get("/pix/current", { params: { caixa_id: caixaId.value } });
    pagamentoAtual.value = resp.data?.pagamento || null;
    await tentarConfirmarPorMP();
  } catch (e) {
    pagamentoAtual.value = null;
  }
}
async function carregarMpQr() {
  mpQrText.value = "";
  mpQrBase64.value = "";
  if (!pagamentoAtual.value?.id) return;
  mpLoading.value = true;
  try {
    const resp = await axios.post('/pix/mp/checkout', { pagamento_id: pagamentoAtual.value.id });
    mpQrText.value = resp.data?.qr_code || "";
    mpQrBase64.value = resp.data?.qr_code_base64 || "";
    mpPaymentId.value = resp.data?.payment_id || null;
    mpQrCreatedAt.value = Date.now();
  } catch (e) {
  } finally {
    mpLoading.value = false;
  }
}
async function gerarNovoQr() {
  await carregarMpQr();
}
async function tentarConfirmarPorMP() {
  if (!pagamentoAtual.value?.id) return;
  try {
    const resp = await axios.post('/pix/mp/status-check', { pagamento_id: pagamentoAtual.value.id, mp_payment_id: mpPaymentId.value, simulate: true });
    if (resp.data?.success) {
      const p = pagamentoAtual.value;
      if (p) {
        successInfo.value = { nome: p.paciente || "", valor: Number(p.valor || 0) };
      } else {
        successInfo.value = { nome: "", valor: 0 };
      }
      paymentSuccess.value = true;
      if (successTimer) { clearTimeout(successTimer); successTimer = null; }
      successTimer = setTimeout(() => { paymentSuccess.value = false; }, 6000);
      await carregarAtual();
    }
  } catch (e) { /* noop */ }
}
async function copiarPayload() {
  try {
    if (!payload.value) return;
    copiando.value = true;
    await navigator.clipboard?.writeText(payload.value);
  } finally {
    copiando.value = false;
  }
}

  // PIX payload helpers
  function sanitizeText(s, max) {
    const t = (String(s || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')).toUpperCase();
    return t.slice(0, max || t.length);
  }
  function emvField(id, value) {
    const v = String(value || '');
    const len = String(v.length).padStart(2, '0');
    return `${id}${len}${v}`;
  }
  function sanitizeTxid(s) {
    const t = String(s || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
    return t.slice(0, 25);
  }
  function crc16(payload) {
    let crc = 0xFFFF;
    for (let i = 0; i < payload.length; i++) {
      crc ^= payload.charCodeAt(i) << 8;
      for (let j = 0; j < 8; j++) {
        if ((crc & 0x8000) !== 0) {
          crc = (crc << 1) ^ 0x1021;
        } else {
          crc <<= 1;
        }
        crc &= 0xFFFF;
      }
    }
    return crc.toString(16).toUpperCase().padStart(4, '0');
  }
  function buildPixPayload({ chave, recebedor_nome, recebedor_cidade, txid, valor }) {
    const merchantAccountInfo = emvField('00', 'BR.GOV.BCB.PIX') + emvField('01', String(chave || ''));
    const mai = emvField('26', merchantAccountInfo);
    const amount = Number(valor || 0).toFixed(2);
    const payloadSemCRC =
      emvField('00','01') +
      emvField('01','11') +
      mai +
      emvField('52','0000') +
      emvField('53','986') +
      emvField('54', amount) +
      emvField('58','BR') +
      emvField('59', sanitizeText(recebedor_nome || 'RECEBEDOR', 25)) +
      emvField('60', sanitizeText(recebedor_cidade || 'CIDADE', 15)) +
      emvField('62', emvField('05', String(txid || 'TX')) ) +
      '6304';
    const crc = crc16(payloadSemCRC);
    return payloadSemCRC + crc;
  }
  const pixConfig = ref({ chave: '', nome: '', cidade: '' });
  async function carregarPixConfig() {
    try {
      const resp = await axios.get('/config/pix');
      const cfg = resp.data || {};
      pixConfig.value = {
        chave: String(cfg?.chave || '').trim(),
        nome: cfg?.nome || cfg?.recebedor_nome || '',
        cidade: cfg?.cidade || cfg?.recebedor_cidade || '',
      };
    } catch (e) {
      pixConfig.value = { chave: '', nome: '', cidade: '' };
    }
  }
const pagamentoValor = computed(() => Number(pagamentoAtual.value?.valor || 0));
const payload = computed(() => {
  if (mpQrText.value) return mpQrText.value;
  const cfg = pixConfig.value;
  const pag = pagamentoAtual.value;
  if (!cfg?.chave || !pag?.id) return "";
  const txid = sanitizeTxid(`PAG${String(pag.id)}`);
  return buildPixPayload({
    chave: cfg.chave,
    recebedor_nome: cfg.nome || '',
    recebedor_cidade: cfg.cidade || '',
    txid,
    valor: Number(pag.valor || 0),
  });
});
const qrUrl = computed(() => {
  if (mpQrBase64.value) return `data:image/png;base64,${mpQrBase64.value}`;
  if (payload.value) return `https://api.qrserver.com/v1/create-qr-code/?size=${qrDim.value}x${qrDim.value}&data=${encodeURIComponent(payload.value)}`;
  return "";
});

  // Sem confirmação manual: a página apenas aguarda o webhook confirmar

  function startPolling() {
    if (timer) clearInterval(timer);
    timer = setInterval(async () => {
      await carregarAtual();
      if (qrExpirado.value && !mpLoading.value && pagamentoAtual.value?.id) {
        await carregarMpQr();
      }
    }, 2000);
    polling.value = true;
  }
  function stopPolling() {
    if (timer) { clearInterval(timer); timer = null; }
    polling.value = false;
  }

  onMounted(async () => {
    await carregarPixConfig();
    await carregarAtual();
    await carregarMpQr();
    await tentarConfirmarPorMP();
    startPolling();
  });
watch(pagamentoAtual, async (nv, ov) => {
  if (!nv || (ov && nv.id === ov.id)) return;
  await carregarMpQr();
});
  onUnmounted(() => stopPolling());
  function onResize() {
    if (typeof window !== "undefined") {
      windowWidth.value = window.innerWidth;
    }
  }
  if (typeof window !== "undefined") {
    window.addEventListener("resize", onResize);
  }
  onUnmounted(() => {
    if (typeof window !== "undefined") {
      window.removeEventListener("resize", onResize);
    }
  });
  </script>

  <style scoped>
  .pix-display {
    min-height: 100vh;
    padding: 32px 24px;
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .pix-content {
    width: 100%;
    max-width: 800px;
    text-align: center;
  }
  .success {
    margin: 0 auto 20px auto;
    max-width: 800px;
    padding: 18px 20px;
    border-radius: 12px;
    background: rgba(25,135,84,0.12);
    color: #146c43;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    box-shadow: 0 8px 18px rgba(25,135,84,0.08);
  }
  .success-icon {
    width: 72px;
    height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    box-shadow: 0 8px 18px rgba(25,135,84,0.18);
  }
  .success-icon i {
    font-size: 28px;
  }
  .success-title {
    font-weight: 700;
    font-size: clamp(1.25rem, 2.5vw, 1.5rem);
  }
  .success-info {
    margin-top: 4px;
    font-size: clamp(1rem, 2vw, 1.125rem);
  }
  .empty .headline {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700;
    letter-spacing: -0.02em;
  }
  .empty .caixa-name {
    color: #6c757d;
    font-weight: 600;
    font-size: clamp(1.25rem, 2.5vw, 1.5rem);
    margin-bottom: 4px;
  }
  .empty .sub {
    color: #6c757d;
    font-size: clamp(1rem, 2vw, 1.25rem);
    margin-top: .25rem;
  }
  .pix-card {
    margin: 0 auto;
    max-width: 800px;
    border-radius: 16px;
    background: #ffffff;
    box-shadow:
      0 24px 48px rgba(16,24,40,0.12),
      0 8px 16px rgba(16,24,40,0.08);
    overflow: hidden;
  }
  .pix-card-body {
    padding: 20px;
  }
  .meta {
    margin-bottom: 8px;
  }
  .meta .label {
    color: #6c757d;
    font-size: 1rem;
    letter-spacing: .02em;
  }
  .meta-item {
    text-align: center;
  }
  .meta .value.name {
    font-weight: 600;
    font-size: clamp(1.25rem, 2.5vw, 1.75rem);
  }
  .meta .value.total {
    font-weight: 700;
    font-size: clamp(1.75rem, 3.5vw, 2.75rem);
  }
  .qr {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .qr img {
    display: block;
    border-radius: 12px;
    box-shadow:
      0 12px 28px rgba(16, 24, 40, 0.12),
      0 2px 8px rgba(16, 24, 40, 0.08);
    max-width: 100%;
    height: auto;
    background: #fff;
  }
  .qr-skeleton {
    border-radius: 12px;
    background: linear-gradient(90deg, rgba(0,0,0,0.06) 25%, rgba(0,0,0,0.08) 37%, rgba(0,0,0,0.06) 63%);
    background-size: 400% 100%;
    animation: placeholderShimmer 1.4s ease infinite;
  }
  @keyframes placeholderShimmer {
    0% { background-position: 100% 0; }
    100% { background-position: 0 0; }
  }
  .actions {
    margin-top: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  </style>
