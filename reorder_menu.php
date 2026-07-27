<?php
$file = __DIR__ . '/resources/js/Components/menu.vue';
$content = file_get_contents($file);

$startStr = "menuItems: [";
$endStr = "            ]\n        };\n    },\n    computed:";

$start = strpos($content, $startStr);
$end = strpos($content, $endStr);

if ($start !== false && $end !== false) {
    $newMenu = <<<'MENU'
menuItems: [
                { title: this.$t("t-menu"), type: "title" },
                {
                    icon: "ri-dashboard-2-fill",
                    title: this.$t("t-dashboards"),
                    type: "link",
                    target: "#/"
                },
                {
                    icon: "ri-calendar-check-line",
                    title: this.$t("t-receptionist"),
                    type: "link",
                    target: "#atendimentos",
                    children: [
                        { href: "/orcamentos", label: this.$t("t-estimate") },
                        { href: "/agendamentos", label: "Agendamento" },
                        { href: "/recepcao/fila", label: "Fila" },
                    ]
                },
                {
                    icon: "ri-hospital-line",
                    title: "Consultório",
                    type: "link",
                    target: "#consultorio",
                    children: [
                        { href: "/atendimentos", label: "Atendimentos" },
                    ]
                },
                {
                    icon: "ri-money-dollar-box-line",
                    title: this.$t("t-till"),
                    type: "link",
                    target: "#caixa",
                    children: [
                        { href: "/movimentacoes-caixa", label: "Abertura" },
                        { href: "/cadastro-caixa", label: "Cadastro" },
                        { href: "/pagamentos-recusados", label: "Pagamentos Recusados" },
                        { href: "#", label: "Sangria" },
                    ]
                },
                {
                    icon: "ri-line-chart-line",
                    title: this.$t("t-finance"),
                    type: "link",
                    target: "#financeiro",
                    children: [
                        { href: "/contas-receber", label: "Contas a Receber" },
                        { href: "#", label: "Contas a Pagar" },
                        { href: "#", label: "Fluxo de Caixa" },
                        { href: "#", label: "Relatórios" }
                    ]
                },
                {
                    icon: "ri-shield-cross-line",
                    title: "Convênio",
                    type: "link",
                    target: "#convenios",
                    children: [
                        { href: "/convenios", label: "Cadastros" },
                        { href: "/convenios/autorizacoes", label: "Autorizações" },
                    ]
                },
                {
                    icon: "ri-user-heart-fill",
                    title: this.$t("t-patient"),
                    type: "link",
                    target: "/pacientes",
                },
                {
                    icon: "ri-stethoscope-line",
                    title: this.$t("t-doctor"),
                    type: "link",
                    target: "/medicos",
                },
                {
                    icon: "ri-user-fill",
                    title: this.$t("t-user"),
                    type: "link",
                    target: "/usuarios",
                },
                {
                    icon: "ri-building-line",
                    title: "Clínica",
                    type: "link",
                    target: "#clinica",
                    children: [
                        { href: "/clinica/salas", label: "Salas" },
                        { href: "/clinica/guiches", label: "Guichês" },
                        { href: "/clinica/totens", label: "Totens" },
                        { href: "/clinica/paineis", label: "Painéis" },
                    ]
                },
                {
                    icon: "ri-first-aid-kit-line",
                    title: "Especialidades",
                    type: "link",
                    target: "/configuracao/especialidades",
                },
                {
                    icon: "ri-file-list-3-line",
                    title: "Procedimentos",
                    type: "link",
                    target: "/configuracao/procedimentos",
                },
                {
                    icon: "ri-layout-grid-line",
                    title: "Tabela TUSS",
                    type: "link",
                    target: "/configuracao/tuss",
                },
                {
                    icon: "ri-heart-pulse-line",
                    title: "Tabela CID",
                    type: "link",
                    target: "/configuracao/cid",
                },
                {
                    icon: "ri-equalizer-line",
                    title: "Parametrização",
                    type: "link",
                    target: "/configuracao/parametrizacao",
                },
                {
                    icon: "ri-layout-grid-fill",
                    title: this.$t("t-components"),
                    type: "link",
                    target: "/componentes",
                },
                {
                    icon: "ri-account-circle-line",
                    title: this.$t("t-authentication"),
                    type: "link",
                    target: "#sidebarAuth",
                    children: [
                        {
                            title: this.$t("t-signin"),
                            target: "#sidebarSignIn",
                            children: [
                                { href: "/auth/signin-basic", label: this.$t("t-basic") },
                                { href: "/auth/signin-cover", label: this.$t("t-cover") }
                            ]
                        },
                        {
                            title: this.$t("t-signup"),
                            target: "#sidebarSignUp",
                            children: [
                                { href: "/auth/signup-basic", label: this.$t("t-basic") },
                                { href: "/auth/signup-cover", label: this.$t("t-cover") }
                            ]
                        },
                        {
                            title: this.$t("t-password-reset"),
                            target: "#sidebarResetPass",
                            children: [
                                { href: "/auth/reset-pwd-basic", label: this.$t("t-basic") },
                                { href: "/auth/reset-pwd-cover", label: this.$t("t-cover") }
                            ]
                        },
                        {
                            title: this.$t("t-password-create"),
                            target: "#sidebarcreatepassword",
                            children: [
                                { href: "/auth/create-pwd-basic", label: this.$t("t-basic") },
                                { href: "/auth/create-pwd-cover", label: this.$t("t-cover") }
                            ]
                        },
                        {
                            title: this.$t("t-lock-screen"),
                            target: "#sidebarLockScreen",
                            children: [
                                { href: "/auth/lockscreen-basic", label: this.$t("t-basic") },
                                { href: "/auth/lockscreen-cover", label: this.$t("t-cover") }
                            ]
                        }
                    ]
                }
MENU;

    $content = substr_replace($content, $newMenu, $start, $end - $start);
    file_put_contents($file, $content);
    echo "Menu reordered successfully.\n";
} else {
    echo "Could not find menu boundaries.\n";
}
