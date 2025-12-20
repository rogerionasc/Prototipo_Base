<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<script>
export default {
    data() {
        return {
            togglePassword: false,
            togglePassword_conf: false,
        }
    }
}
</script>

<template>
    <Head title="Register" />

    <div class="auth-page-wrapper pt-5">


        <div class="auth-page-content">
            <BContainer>

                <BRow class="justify-content-center">
                    <BCol md="8" lg="6" xl="5">
                        <BCard no-body class="mt-4">

                            <BCardBody class="p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Registrar uma nova conta</h5>
                                    <p class="text-muted">Preencha os campos para criar uma nova conta</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <form @submit.prevent="submit" class="needs-validation" novalidate>

                                        <div class="mb-3">
                                            <InputLabel for="name" value="Nome completo" />
                                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus autocomplete="name" placeholder="Insira seu nome completo" :class="{ 'is-invalid': form.errors.name }" />
                                            <InputError :message="form.errors.name" />
                                        </div>
                                        <div class="mb-3">
                                            <InputLabel for="email" value="Email" />
                                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autocomplete="email" :class="{ 'is-invalid': form.errors.email }" placeholder="Insira seu email" />
                                            <InputError :message="form.errors.email" />
                                        </div>
                                        <div class="mb-3">
                                            <InputLabel for="password" value="Senha" />
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input :type="togglePassword ? 'text' : 'password'" class="form-control pe-5 password-input" placeholder="Insira sua senha" id="password" required v-model="form.password" :class="{ 'is-invalid': form.errors.password }" autocomplete="new-password">
                                                <BButton variant="link" class="position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" @click="togglePassword = !togglePassword"><i class="ri-eye-fill align-middle"></i></BButton>
                                                <InputError :message="form.errors.password" />
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <InputLabel for="password_confirmation" value="Confirmar senha" />
                                            <div class="position-relative auth-pass-inputgroup">
                                                <input :type="togglePassword_conf ? 'text' : 'password'" class="form-control pe-5 password-input" placeholder="Insira sua senha" id="password_confirmation" required v-model="form.password_confirmation" :class="{ 'is-invalid': form.errors.password_confirmation }" autocomplete="new-password">
                                                <BButton variant="link" class="position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" @click="togglePassword_conf = !togglePassword_conf"><i class="ri-eye-fill align-middle"></i></BButton>
                                                <InputError :message="form.errors.password_confirmation" />
                                            </div>
                                        </div>

                                        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mb-3 form-check">
                                            <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="form-check-input me-1" :class="{ 'is-invalid': form.errors.terms }" />
                                            <label class="form-check-label mb-0 fs-12 text-muted fst-italic" for="auth-remember-check">
                                                Eu concordo com os <BLink target="_blank" :href="route('terms.show')" class="">Termos de Serviços</BLink> e <BLink target="_blank" :href="route('policy.show')" class="">Politicas de Privacidades</BLink>
                                            </label>
                                            <InputError :message="form.errors.terms" />
                                        </div>
                                        <div class="mt-4">
                                            <BButton variant="success" class="w-100" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Sign
                                                Up</BButton>
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
