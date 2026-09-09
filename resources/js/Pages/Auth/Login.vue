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

    <div class="container-fluid p-0">
        <div class="row g-0 vh-100">
            <!-- Left Side: Image/Brand -->
            <div class="col-lg-6 d-none d-lg-block bg-primary text-white position-relative overflow-hidden">
                <!-- Medical Tech Background Image -->
                <div class="position-absolute w-100 h-100 start-0 top-0" style="background-image: url('/images/medical_bg_3.jpg'); background-size: cover; background-position: center; mix-blend-mode: overlay; opacity: 0.6;"></div>
                <div class="position-relative d-flex flex-column justify-content-center h-100 p-5 z-1 bg-primary bg-opacity-50">
                    <div class="mb-auto mt-4 d-flex align-items-center gap-1">
                        <img src="/storage/logo-sistema/Logo-Top.svg" alt="Logo Top" height="50" style="filter: brightness(0) invert(1); margin-top: -10px;" />
                        <img src="/storage/logo-sistema/Logo-Bottom.svg" alt="Logo Bottom" height="80" style="filter: brightness(0) invert(1);" />
                    </div>
                    <div>
                        <h1 class="display-4 fw-bold mb-4 text-white">Evolua a forma como você gerencia sua clínica.</h1>
                        <p class="fs-5 opacity-75 text-white" style="max-width: 500px;">Acesse sua conta para continuar gerenciando sua clínica com eficiência e praticidade.</p>
                    </div>
                    <div class="mt-auto pb-4">
                        <p class="mb-0 opacity-75">&copy; {{ new Date().getFullYear() }} WCode. Todos os direitos reservados.</p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center bg-light z-2 position-relative">
                <div class="w-100 p-4 p-sm-5 bg-white rounded-4 shadow-sm border border-light-subtle" style="max-width: 580px;">
                    
                    <div class="text-center mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 72px; height: 72px;">
                            <i class="ri-heart-pulse-line fs-1"></i>
                        </div>
                        <h4 class="text-primary fw-bold mb-1">Acesso ao Sistema</h4>
                        <p class="text-muted mb-0">Insira suas credenciais para continuar</p>
                    </div>

                    <div v-if="status" class="alert alert-success text-success mb-4 rounded-3 border-0 bg-success bg-opacity-10">
                        <i class="ri-checkbox-circle-line me-1"></i> {{ status }}
                    </div>

                    <form @submit.prevent="submit">
                        
                        <div class="mb-4">
                            <InputLabel for="email" value="E-mail" class="fw-medium text-dark mb-2" />
                            <TextInput 
                                id="email" 
                                v-model="form.email" 
                                type="email" 
                                class="form-control form-control-lg bg-light border-0"
                                placeholder="exemplo@clinica.com.br" 
                                autocomplete="email" 
                                required
                                :class="{ 'is-invalid': form.errors.email }" 
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <InputLabel for="password" value="Senha" class="fw-medium text-dark mb-0" />
                                <Link v-if="canResetPassword" :href="route('password.request')" class="text-muted text-decoration-none small">
                                    Esqueceu a senha?
                                </Link>
                            </div>
                            
                            <div class="position-relative auth-pass-inputgroup">
                                <input 
                                    :type="togglePassword ? 'text' : 'password'"
                                    class="form-control form-control-lg bg-light border-0 pe-5" 
                                    placeholder="Digite sua senha"
                                    id="password-input" 
                                    v-model="form.password" 
                                    autocomplete="password"
                                    required 
                                    :class="{ 'is-invalid': form.errors.password }"
                                >
                                <BButton variant="link"
                                    class="position-absolute end-0 top-50 translate-middle-y text-decoration-none text-muted p-0 me-3"
                                    type="button" id="password-addon"
                                    @click="togglePassword = !togglePassword">
                                    <i :class="togglePassword ? 'ri-eye-off-line' : 'ri-eye-line'" class="fs-5"></i>
                                </BButton>
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>
                        </div>

                        <div class="form-check mb-4 d-flex align-items-center">
                            <Checkbox 
                                v-model:checked="form.remember" 
                                name="remember"
                                class="form-check-input mt-0" 
                                id="auth-remember-check" 
                                style="width: 1.2rem; height: 1.2rem;"
                            />
                            <label class="form-check-label text-muted ms-2 pt-1" for="auth-remember-check" style="cursor: pointer;">
                                Manter conectado
                            </label>
                        </div>

                        <div class="mt-5">
                            <BButton variant="success" class="w-100 btn-lg shadow-sm fw-bold rounded-3 py-2 d-flex justify-content-center align-items-center" type="submit"
                                :class="{ 'opacity-75': form.processing }" :disabled="form.processing">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Acessar Conta
                            </BButton>
                        </div>
                    </form>
                    
                    <div class="text-center mt-5 d-lg-none">
                        <p class="mb-0 text-muted small">&copy; {{ new Date().getFullYear() }} Todos os direitos reservados WCode</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
