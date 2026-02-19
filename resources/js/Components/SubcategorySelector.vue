<template>
    <div class="relative" ref="dropdownRef">
        <!-- Custom Dropdown Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-left flex items-center justify-between"
        >
            <span v-if="selectedSubcategory" class="text-gray-900">
                {{ selectedSubcategory.name }} (VAT: {{ selectedSubcategory.vat_rate }}%)
            </span>
            <span v-else class="text-gray-500">Select {{ label.toLowerCase() }}</span>
            <i class="fa-solid fa-chevron-down text-gray-400"></i>
        </button>

        <!-- Custom Dropdown Menu -->
        <div
            v-if="showDropdown"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-80 overflow-auto"
        >
            <!-- Subcategory Options -->
            <div
                v-for="sub in subcategories"
                :key="sub.id"
                class="group flex items-center justify-between px-4 py-2 hover:bg-green-50 cursor-pointer border-b border-gray-100"
            >
                <div
                    @click="selectSubcategory(sub)"
                    class="flex-1"
                >
                    <span class="text-gray-900">{{ sub.name }}</span>
                    <span class="text-gray-500 text-sm ml-2">(VAT: {{ sub.vat_rate }}%)</span>
                </div>
                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        type="button"
                        @click.stop="openEditModal(sub)"
                        class="px-2 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-xs font-semibold"
                        title="Edit"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        @click.stop="openDeleteModal(sub)"
                        class="px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-xs font-semibold"
                        title="Delete"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <!-- Add New Option -->
            <div
                @click="openAddModal"
                class="flex items-center gap-2 px-4 py-2 hover:bg-green-50 cursor-pointer text-green-600 font-medium"
            >
                <i class="fa-solid fa-plus"></i>
                <span>Add New {{ label }}</span>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ editingSubcategory ? `Edit ${label}` : `Add New ${label}` }}
                    </h2>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="e.g., Medical Fee"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%) *</label>
                        <input
                            v-model.number="form.vat_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="0"
                        />
                    </div>
                    <div class="flex gap-3 justify-end pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            {{ editingSubcategory ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Delete {{ label }}</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this {{ label.toLowerCase() }}? This action cannot be undone.</p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="showDeleteConfirm = false"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteSubcategory"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    modelValue: String,
    subcategories: Array,
    type: {
        type: String,
        required: true,
    },
    category: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: 'Cost Head',
    },
});

const emit = defineEmits(['update:modelValue', 'update:vatRate', 'subcategoriesUpdated']);

const showDropdown = ref(false);
const showModal = ref(false);
const showDeleteConfirm = ref(false);
const editingSubcategory = ref(null);
const deletingSubcategory = ref(null);
const form = ref({
    name: '',
    vat_rate: 0,
});

const selectedSubcategory = computed(() => {
    if (!props.modelValue) return null;
    return props.subcategories.find(s => s.name === props.modelValue);
});

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const selectSubcategory = (subcategory) => {
    emit('update:modelValue', subcategory.name);
    emit('update:vatRate', subcategory.vat_rate);
    showDropdown.value = false;
};

const openAddModal = () => {
    showDropdown.value = false;
    editingSubcategory.value = null;
    form.value = {
        name: '',
        vat_rate: 0,
    };
    showModal.value = true;
};

const openEditModal = (subcategory) => {
    showDropdown.value = false;
    editingSubcategory.value = subcategory;
    form.value = {
        name: subcategory.name,
        vat_rate: subcategory.vat_rate,
    };
    showModal.value = true;
};

const openDeleteModal = (subcategory) => {
    showDropdown.value = false;
    deletingSubcategory.value = subcategory;
    showDeleteConfirm.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingSubcategory.value = null;
    form.value = {
        name: '',
        vat_rate: 0,
    };
};

// Close dropdown when clicking outside
const dropdownRef = ref(null);

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const submitForm = async () => {
    try {
        if (editingSubcategory.value) {
            // Update existing subcategory
            await axios.put(`/accounting/subcategories/${editingSubcategory.value.id}`, form.value);
        } else {
            // Create new subcategory
            await axios.post('/accounting/subcategories', {
                ...form.value,
                type: props.type,
                category: props.category,
            });
        }

        closeModal();
        // Reload the page to get updated subcategories
        router.reload({ only: ['subcategories'] });
        emit('subcategoriesUpdated');
    } catch (error) {
        console.error('Error saving subcategory:', error);
        alert('Failed to save subcategory. Please try again.');
    }
};

const deleteSubcategory = async () => {
    if (!deletingSubcategory.value) return;

    const subcategoryToDelete = deletingSubcategory.value;

    try {
        await axios.delete(`/accounting/subcategories/${subcategoryToDelete.id}`);
        showDeleteConfirm.value = false;
        // Clear the selected value if it was the deleted one
        if (props.modelValue === subcategoryToDelete.name) {
            emit('update:modelValue', '');
            emit('update:vatRate', 0);
        }
        deletingSubcategory.value = null;
        // Reload the page to get updated subcategories
        router.reload({ only: ['subcategories'] });
        emit('subcategoriesUpdated');
    } catch (error) {
        console.error('Error deleting subcategory:', error);
        alert('Failed to delete subcategory. It may be in use by existing entries.');
        showDeleteConfirm.value = false;
        deletingSubcategory.value = null;
    }
};
</script>
