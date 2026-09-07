<template>
    <Layout>
        <Head title="Testes de Cobrança Bancária" />
        <PageHeader title="Testes de Cobrança Bancária" pageTitle="Financeiro" />

        <div class="p-6">
            <h1 class="text-2xl font-bold mb-6">Modelo de Cobrança Bancária Desacoplado</h1>

            <div class="max-w-3xl">
                <!-- SEÇÃO DE GERAR TOKEN BB -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">Gerar Token BB (Teste)</h2>
                    <form @submit.prevent="gerarTokenBb">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                            <input v-model="formToken.client_id" type="text" class="form-input w-full border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                            <input v-model="formToken.client_secret" type="password" class="form-input w-full border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Grant Type</label>
                            <input v-model="formToken.grant_type" type="text" class="form-input w-full border-gray-300 rounded-md" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Scope</label>
                            <input v-model="formToken.scope" type="text" class="form-input w-full border-gray-300 rounded-md" required>
                        </div>
                        
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700" :disabled="formToken.processing">
                            Gerar Token
                        </button>
                    </form>

                    <div v-if="tokenResponse" class="mt-6 p-4 bg-gray-50 rounded border">
                        <h3 class="font-semibold text-gray-800 mb-2">Retorno (Token):</h3>
                        <pre class="text-xs text-gray-700 whitespace-pre-wrap overflow-x-auto">{{ tokenResponse }}</pre>
                    </div>
                </div>
                
                <!-- SEÇÃO DE GERAR BOLETO BB -->
                <div class="bg-white p-6 rounded-lg shadow mt-6">
                    <h2 class="text-xl font-semibold mb-4">Gerar Boleto BB (Teste)</h2>
                    <form @submit.prevent="gerarBoletoBb">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Token & App Key -->
                            <div class="md:col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Token de Acesso</label>
                                <input v-model="formBoleto.token" type="text" class="form-input w-full border-gray-300 rounded-md" placeholder="Insira o access_token gerado acima" required>
                            </div>
                            <div class="md:col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">App Key (gw-dev-app-key)</label>
                                <input v-model="formBoleto.app_key" type="text" class="form-input w-full border-gray-300 rounded-md" placeholder="Sua App Key do portal de desenvolvedores" required>
                            </div>

                            <div class="md:col-span-12 mt-4">
                                <h3 class="font-semibold text-gray-700 border-b pb-2">Dados do Boleto</h3>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número Convênio</label>
                                <input v-model="formBoleto.numeroConvenio" type="number" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número Carteira</label>
                                <input v-model="formBoleto.numeroCarteira" type="number" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Variação Carteira</label>
                                <input v-model="formBoleto.numeroVariacaoCarteira" type="number" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data Emissão</label>
                                <input v-model="formBoleto.dataEmissao" type="text" placeholder="DD.MM.YYYY" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data Vencimento</label>
                                <input v-model="formBoleto.dataVencimento" type="text" placeholder="DD.MM.YYYY" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Original (R$)</label>
                                <input v-model="formBoleto.valorOriginal" type="number" step="0.01" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Abatimento (R$)</label>
                                <input v-model="formBoleto.valorAbatimento" type="number" step="0.01" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            
                            <!-- Opções Adicionais -->
                            <div class="md:col-span-12 mt-4">
                                <h3 class="font-semibold text-gray-700 border-b pb-2">Configurações Adicionais</h3>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cód. Modalidade</label>
                                <input v-model="formBoleto.codigoModalidade" type="number" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dias Protesto</label>
                                <input v-model="formBoleto.quantidadeDiasProtesto" type="number" placeholder="Protesto" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dias Negativação</label>
                                <input v-model="formBoleto.quantidadeDiasNegativacao" type="number" placeholder="Negativ." class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dias Limite Receb.</label>
                                <input v-model="formBoleto.numeroDiasLimiteRecebimento" type="number" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-12">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem Boleto</label>
                                <input v-model="formBoleto.mensagemBloquetoOcorrencia" type="text" class="form-input w-full border-gray-300 rounded-md">
                            </div>

                            <!-- Descontos / Juros / Multa -->
                            <div class="md:col-span-12 mt-4">
                                <h3 class="font-semibold text-gray-700 border-b pb-2">Encargos</h3>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Juros (Tipo)</label>
                                <input v-model="formBoleto.jurosMora_tipo" type="number" placeholder="Juros Tipo" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Multa (Tipo)</label>
                                <input v-model="formBoleto.multa_tipo" type="number" placeholder="Multa Tipo" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desc. 1 (Tipo)</label>
                                <input v-model="formBoleto.desconto_tipo" type="number" placeholder="Tipo" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desc. 1 (%)</label>
                                <input v-model="formBoleto.desconto_porcentagem" type="number" step="0.01" placeholder="%" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desc. 1 (R$)</label>
                                <input v-model="formBoleto.desconto_valor" type="number" step="0.01" placeholder="R$" class="form-input w-full border-gray-300 rounded-md">
                            </div>

                            <!-- Dados do Pagador -->
                            <div class="md:col-span-12 mt-4">
                                <h3 class="font-semibold text-gray-700 border-b pb-2">Dados do Pagador</h3>
                            </div>
                            <div class="md:col-span-8">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Pagador</label>
                                <input v-model="formBoleto.pagador_nome" type="text" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ (só números)</label>
                                <input v-model="formBoleto.pagador_numeroInscricao" type="text" class="form-input w-full border-gray-300 rounded-md" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                                <input v-model="formBoleto.pagador_cep" type="text" placeholder="CEP" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-9">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                                <input v-model="formBoleto.pagador_endereco" type="text" placeholder="Endereço" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                                <input v-model="formBoleto.pagador_bairro" type="text" placeholder="Bairro" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                                <input v-model="formBoleto.pagador_cidade" type="text" placeholder="Cidade" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                                <input v-model="formBoleto.pagador_uf" type="text" placeholder="UF" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                <input v-model="formBoleto.pagador_telefone" type="text" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                            <div class="md:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                <input v-model="formBoleto.pagador_email" type="email" class="form-input w-full border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 mt-4" :disabled="formBoleto.processing">
                            Registrar Boleto
                        </button>
                    </form>

                    <div v-if="boletoResponse" class="mt-6 p-4 bg-gray-50 rounded border">
                        <h3 class="font-semibold text-gray-800 mb-2">Retorno (Boleto):</h3>
                        <pre class="text-xs text-gray-700 whitespace-pre-wrap overflow-x-auto">{{ boletoResponse }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import { ref } from 'vue';

const props = defineProps({
    configuracoes: Array,
    contasReceber: Array
});

// Formulário para Gerar Token BB
const formToken = useForm({
    client_id: '',
    client_secret: '',
    grant_type: 'client_credentials',
    scope: 'cobrancas.boletos-info cobrancas.boletos-requisicao'
});
const tokenResponse = ref(null);

const gerarTokenBb = () => {
    tokenResponse.value = null;
    axios.post(route('financeiro.configuracoes.cobranca.bb_token'), formToken.data())
        .then(response => {
            tokenResponse.value = response.data;
            if (response.data.access_token) {
                formBoleto.token = response.data.access_token; // Auto-fill token
            }
        })
        .catch(error => {
            tokenResponse.value = error.response ? error.response.data : error.message;
        });
};

// Formulário para Gerar Boleto BB
const formBoleto = useForm({
    token: '',
    app_key: '',
    numeroConvenio: '',
    numeroCarteira: '',
    numeroVariacaoCarteira: '',
    codigoModalidade: 1,
    dataEmissao: '',
    dataVencimento: '',
    valorOriginal: '',
    valorAbatimento: 0,
    quantidadeDiasProtesto: 0,
    quantidadeDiasNegativacao: 0,
    orgaoNegativador: 0,
    indicadorAceiteTituloVencido: 'S',
    numeroDiasLimiteRecebimento: 30,
    codigoAceite: 'A',
    codigoTipoTitulo: 2,
    descricaoTipoTitulo: 'Duplicata Mercantil',
    indicadorPermissaoRecebimentoParcial: 'N',
    numeroTituloBeneficiario: '12345678',
    campoUtilizacaoBeneficiario: 'LOTE000125CONVENIO',
    mensagemBloquetoOcorrencia: 'Teste de emissao BB API',
    
    // Descontos / Juros / Multa
    desconto_tipo: 0,
    desconto_porcentagem: 0,
    desconto_valor: 0,
    segundoDesconto_porcentagem: 0,
    segundoDesconto_valor: 0,
    terceiroDesconto_porcentagem: 0,
    terceiroDesconto_valor: 0,
    jurosMora_tipo: 0,
    multa_tipo: 0,
    
    // Pagador
    pagador_nome: '',
    pagador_numeroInscricao: '',
    pagador_endereco: 'Rua Teste 123',
    pagador_cep: '70000000',
    pagador_cidade: 'Brasilia',
    pagador_bairro: 'Centro',
    pagador_uf: 'DF',
    pagador_telefone: '6199999999',
    pagador_email: 'teste@teste.com'
});
const boletoResponse = ref(null);

const gerarBoletoBb = () => {
    boletoResponse.value = null;
    axios.post(route('financeiro.configuracoes.cobranca.bb_gerar'), formBoleto.data())
        .then(response => {
            boletoResponse.value = response.data;
        })
        .catch(error => {
            boletoResponse.value = error.response ? error.response.data : error.message;
        });
};
</script>
