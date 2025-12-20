<script setup>
import { Link, Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Lottie from "@/Components/widgets/lottie.vue";
import animationData from "@/Components/widgets/rhvddzym.json";

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<script>

export default {
    components: {
        lottie: Lottie
    },
    data() {
        return {
            defaultOptions: {
                animationData: animationData
            },
        };
    }
}

</script>

<template>

    <Head title="Forgot Password" />

    <div class="auth-page-wrapper pt-5">


        <div class="auth-page-content">
            <BContainer>
                <BRow class="justify-content-center">
                    <BCol md="8" lg="6" xl="5">
                        <BCard no-body class="mt-4">

                            <BCardBody class="p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Redefinir senha</h5>
                                    <p class="text-muted">Esqueceu a senha? Informe seu e-mail e enviaremos um link para
                                        criar uma nova.</p>

                                    <lottie class="avatar-xl some-class" colors="primary:#0ab39c"
                                        :options="defaultOptions" :height="120" :width="120" />

                                </div>

                                <BAlert v-if="status" :model-value="true" variant="warning"
                                    class="mb-2 mx-2 border-0 text-center">{{ status }}</BAlert>

                                <div class="p-2">
                                    <form @submit.prevent="submit">
                                        <div class="mb-4">
                                            <InputLabel for="email" value="Email" />
                                            <TextInput id="email" v-model="form.email" type="email"
                                                class="mt-1 block w-full" required autofocus autocomplete="email"
                                                placeholder="Insira seu e-mail"
                                                :class="{ 'is-invalid': form.errors.email }" />
                                            <InputError :message="form.errors.email" />
                                        </div>

                                        <div class="text-center mt-4">
                                            <b-button variant="success" class="w-100" type="submit"
                                                :class="{ 'opacity-25': form.processing }"
                                                :disabled="form.processing">Enviar link</b-button>
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
