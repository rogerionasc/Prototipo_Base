<script>
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";

export default {
    components: { Layout, PageHeader },
    data() {
        return {
            isMobile: false,
            activeTab: null,
            tabs: [
                {
                    key: 'geral',
                    icon: 'ri-settings-3-line',
                    title: 'Configurações Gerais',
                    description: 'Altere o nome da aplicação, idioma e outras definições gerais.',
                    content: `
                        <div class="mb-3">
                            <label class="form-label">Nome do Sistema</label>
                            <input type="text" class="form-control" placeholder="Digite o nome do sistema" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Idioma Padrão</label>
                            <select class="form-select">
                                <option>Português (Brasil)</option>
                                <option>Inglês</option>
                                <option>Espanhol</option>
                            </select>
                        </div>`
                },
                {
                    key: 'usuarios',
                    icon: 'ri-user-settings-line',
                    title: 'Gerenciamento de Usuários',
                    description: 'Adicione, edite e configure as permissões dos usuários cadastrados.',
                    content: `
                        <ul>
                            <li>Adicionar novos usuários</li>
                            <li>Alterar permissões de acesso</li>
                            <li>Gerenciar perfis e papéis</li>
                        </ul>`
                },
                {
                    key: 'aparencia',
                    icon: 'ri-brush-line',
                    title: 'Configurações de Aparência',
                    description: 'Escolha o tema, cores e ajustes visuais do sistema.',
                    content: `
                        <div class="d-flex">
                            <div class="me-3">
                                <label class="form-label">Tema</label>
                                <select class="form-select">
                                    <option>Claro</option>
                                    <option>Escuro</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Cor Principal</label>
                                <input type="color" class="form-control form-control-color" value="#4f46e5" />
                            </div>
                        </div>`
                },
                {
                    key: 'notificacoes',
                    icon: 'ri-notification-3-line',
                    title: 'Preferências de Notificação',
                    description: 'Gerencie como e quando você deseja ser notificado.',
                    content: `
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                            <label class="form-check-label" for="emailNotif">Notificações por Email</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="pushNotif">
                            <label class="form-check-label" for="pushNotif">Notificações Push</label>
                        </div>`
                }
            ]
        };
    },
    mounted() {
        this.checkMobile();
        window.addEventListener("resize", this.checkMobile);
    },
    beforeUnmount() {
        window.removeEventListener("resize", this.checkMobile);
    },
    methods: {
        checkMobile() {
            this.isMobile = window.innerWidth < 768;
            if (!this.isMobile) this.activeTab = null;
        },
        selectTab(tab) {
            this.activeTab = tab;
        },
        goBack() {
            this.activeTab = null;
        },
        getTabContent(key) {
            return this.tabs.find(t => t.key === key);
        }
    }
};
</script>

<template>
    <BCol>
        <BCard no-body>
            <BCardBody>
                <BRow>
                    <!-- Mobile Mode -->
                    <div v-if="isMobile" class="w-100">
                        <div v-if="!activeTab">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                class="btn btn-outline-primary w-100 mb-2"
                                @click="selectTab(tab.key)"
                            >
                                <i :class="tab.icon + ' me-2'"></i> {{ tab.title.split(' ')[0] }}
                            </button>
                        </div>
                        <div v-else>
                            <button class="btn btn-link mb-3" @click="goBack">
                                <i class="ri-arrow-left-line me-1"></i> Voltar
                            </button>
                            <div v-html="getTabContent(activeTab)?.content"></div>
                        </div>
                    </div>

                    <!-- Desktop Mode -->
                    <BTabs
                        v-else
                        navClass="custom-verti-nav-pills text-center"
                        navWrapperClass="col-md-3"
                        contentClass="text-muted mt-3 mt-lg-0 col-md-9"
                        pills vertical justified
                    >
                        <BTab v-for="(tab, index) in tabs" :key="tab.key" :active="index === 0">
                            <template #title>
                                <i :class="tab.icon + ' d-block fs-3xl mb-1'"></i>{{ tab.title.split(' ')[0] }}
                            </template>
                            <h5 class="mb-3">{{ tab.title }}</h5>
                            <p>{{ tab.description }}</p>
                            <div v-html="tab.content"></div>
                        </BTab>
                    </BTabs>
                </BRow>
            </BCardBody>
        </BCard>
    </BCol>
</template>

<style scoped>
.tamp {
    position: absolute;
    top: 9px;
    right: 13px;
}
.hover {
    padding-left: 16px;
    padding-right: 16px;
    padding-bottom: 15px;
}
</style>
