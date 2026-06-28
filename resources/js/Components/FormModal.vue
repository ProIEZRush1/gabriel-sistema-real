<script setup>
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    show: { type: Boolean, default: false },
    titulo: { type: String, default: '' },
    procesando: { type: Boolean, default: false },
    maxWidth: { type: String, default: '2xl' },
});

const emit = defineEmits(['close', 'submit']);
</script>

<template>
    <Modal :show="show" :max-width="maxWidth" @close="emit('close')">
        <form @submit.prevent="emit('submit')" class="p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">{{ titulo }}</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <slot />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton type="button" @click="emit('close')">Cancelar</SecondaryButton>
                <PrimaryButton :disabled="procesando">Guardar</PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
