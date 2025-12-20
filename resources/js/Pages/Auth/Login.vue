<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<script>
export default {
    data() {
        return {
            togglePassword: false
        }
    }
}
</script>

<template>

    <Head title="Log in" />

    <div class="auth-page-wrapper pt-5">
        <div class="auth-page-content">
            <BContainer>
                <BRow class="justify-content-center">
                    <BCol md="8" lg="6" xl="5">
                        <BCard no-body class="mt-4">

                            <BCardBody class="p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Entrar no Sistema</h5>
                                    <p class="text-muted">Faça seu login</p>
                                </div>
                                <div v-if="status" class="alert alert-success text-success">
                                    {{ status }}
                                </div>
                                <div class="p-2 mt-3">
                                    <form @submit.prevent="submit">

                                        <div class="mb-3">
                                            <InputLabel for="email" value="Email" />
                                            <TextInput id="email" v-model="form.email" type="email" class="form-control"
                                                autofocus placeholder="Insira seu email" autocomplete="email" required
                                                :class="{ 'is-invalid': form.errors.email }" />
                                            <InputError :message="form.errors.email" />
                                        </div>

                                        <div class="mb-3">
                                            <div class="float-end">
                                                <Link v-if="canResetPassword" :href="route('password.request')"
                                                    class="text-muted">Esqueceu sua senha?</Link>
                                            </div>
                                            <InputLabel for="password" value="Senha" />
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input :type="togglePassword ? 'text' : 'password'"
                                                    class="form-control pe-5" placeholder="Insira sua senha"
                                                    id="password-input" v-model="form.password" autocomplete="password"
                                                    required :class="{ 'is-invalid': form.errors.password }">
                                                <BButton variant="link"
                                                    class="position-absolute end-0 top-0 text-decoration-none text-muted"
                                                    type="button" id="password-addon"
                                                    @click="togglePassword = !togglePassword">
                                                    <i class="ri-eye-fill align-middle"></i>
                                                </BButton>
                                                <InputError :message="form.errors.password" />
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <Checkbox v-model:checked="form.remember" name="remember"
                                                class="form-check-input" id="auth-remember-check" />
                                            <label class="form-check-label" for="auth-remember-check">Lembre-me</label>
                                        </div>

                                        <div class="my-4 ">
                                            <BButton variant="success" class="w-100" type="submit"
                                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                Entrar</BButton>
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
