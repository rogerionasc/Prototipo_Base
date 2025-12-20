<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

    <div class="auth-page-wrapper pt-5">
        <div class="auth-page-content">
            <BContainer>
                <BRow class="justify-content-center">
                    <BCol md="8" lg="6" xl="5">
                        <BCard no-body class="mt-4">

                            <BCardBody class="p-4">
                                <div class="mb-4">
                                    <div class="avatar-lg mx-auto">
                                        <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                            <i class="ri-mail-line"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-2 mt-4">
                                    <div class="text-muted text-center mb-4 mx-lg-3">
                                        <h4 class="">Verificar seu e-mail</h4>
                                        <div class="text-sm text-muted">
                                            Verifique seu e-mail clicando no link enviado. Se não recebeu, podemos reenviar.
                                        </div>
                                    </div>

                                    <div v-if="verificationLinkSent" class="mb-4 alert alert-success text-sm text-success">
                                        Enviamos um novo link de verificação para seu e-mail.
                                    </div>

                                    <form @submit.prevent="submit">
                                        <div class="w-100 mb-3">
                                            <BButton variant="success" type="submit" class="w-100" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Reenviar verificação</BButton>
                                        </div>
                                    </form>
                                </div>
                            </BCardBody>
                        </BCard>

                    </BCol>
                </BRow>
            </BContainer>
        </div>

        <footer class="footer">
            <BContainer>
                <BRow>
                    <BCol lg="12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; {{ new Date().getFullYear() }} Todos os direitos
                                reservados WCode</p>
                        </div>
                    </BCol>
                </BRow>
            </BContainer>
        </footer>
    </div>
</template>
