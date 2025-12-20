<script setup>
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: (props.user.name ?? `${props.user.nome ?? ''} ${props.user.sobrenome ?? ''}`).trim(),
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const initials = computed(() => {
    const u = props.user || {};
    const parts = u.name
        ? String(u.name).trim().split(/\s+/)
        : [u.nome, u.sobrenome].filter(Boolean).map(s => String(s).trim());
    return parts
        .filter(Boolean)
        .map(p => p[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            photoPreview.value = null;
            router.reload({ only: ['auth'], preserveScroll: true });
        },
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
            router.reload({ only: ['auth'], preserveScroll: true });
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation" title="Informações do Perfil" description="Atualize as informações do perfil da sua conta e o endereço de e-mail..">

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="mb-3">
                <!-- Profile Photo File Input -->
                <div class="mb-2">
                    <input ref="photoInput" type="file" class="d-none form-control" @change="updatePhotoPreview">
                    <InputLabel for="photo" value="Foto" />
                </div>

                <div class="mb-2">
                    <!-- Current Profile Photo -->
                    <div v-show="!photoPreview">
                        <img v-if="user.profile_photo_path" :src="user.profile_photo_url" :alt="user.name || user.nome" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover; object-position: center;">
                        <div v-else class="rounded-circle d-inline-flex align-items-center justify-content-center bg-light-subtle text-body border" style="width: 100px; height: 100px; font-weight: 600; font-size: 32px;">
                            {{ initials }}
                        </div>
                    </div>

                    <!-- New Profile Photo Preview -->
                    <div v-show="photoPreview">
                        <div class="d-inline-block rounded-circle" :style="'width: 100px; height: 100px; background-image: url(' + photoPreview + '); background-size: cover; background-position: center; background-repeat: no-repeat;'" />
                    </div>
                </div>

                <BButton variant="primary" class="me-2 btn-sm" type="button" @click.prevent="selectNewPhoto">Selecione uma foto</BButton>
                <BButton v-if="user.profile_photo_path" variant="danger" type="button" class="btn-sm" @click.prevent="deletePhoto">Remover foto</BButton>

                <div class="text-danger mt-2">
                    <span>{{ form.errors.photo }}</span>
                </div>
            </div>

            <!-- Name -->
            <div class="mb-3">
                <InputLabel for="name" value="Nome" />
                <TextInput id="name" v-model="form.name" type="text" required autocomplete="name" :class="{ 'is-invalid': form.errors.name }" />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="mb-3">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" required autocomplete="username" :class="{ 'is-invalid': form.errors.email }" />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2 text-muted">
                        Your email address is unverified.

                        <Link :href="route('verification.send')" method="post" as="button" class="btn btn-sm btn-warning" @click.prevent="sendEmailVerification">
                        Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="alert alert-success text-success">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>
        </template>

        <template #actions>
            <BButton variant="success w-100" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salvar</BButton>
            <!-- <p v-if="form.recentlySuccessful" class="alert alert-info mt-3">Perfil salvo.</p> -->
        </template>
    </FormSection>
</template>
