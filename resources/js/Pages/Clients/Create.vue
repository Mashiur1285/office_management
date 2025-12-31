<template>
    <div class="py-8 space-y-6">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <p
                    class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-500"
                >
                    Clients
                </p>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ isEdit ? "Edit client" : "Add a client" }}
                </h1>
                <p class="text-sm text-gray-600">
                    {{ isEdit ? "Update passport, placement, payments, and status." : "Capture passport, placement, payments, and status in one place." }}
                </p>
            </div>
            <div class="flex gap-3">
                <Link
                    href="/clients"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    ← Back to list
                </Link>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? isEdit
                                ? "Updating..."
                                : "Saving..."
                            : isEdit
                              ? "Update Client"
                              : "Save Client"
                    }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Identity -->
            <section
                class="rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-6 py-4"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Identity & Documents
                        </h2>
                        <p class="text-sm text-gray-600">
                            Basic info plus NID and passport uploads.
                        </p>
                    </div>
                    <p class="text-xs text-gray-500">
                        Fields with * are required.
                    </p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup label="Client Name *" :error="form.errors.name">
                        <input
                            v-model="form.name"
                            :class="inputClass('name')"
                            placeholder="Ex: Abu Rahim"
                            required
                        />
                    </FormGroup>
                    <FormGroup
                        label="Mobile Number"
                        :error="form.errors.mobile"
                    >
                        <input
                            v-model="form.mobile"
                            :class="inputClass('mobile')"
                            placeholder="Ex: 01XXXXXXXXX"
                        />
                    </FormGroup>

                    <FormGroup
                        label="NID Number *"
                        :error="form.errors.nid_number"
                    >
                        <input
                            v-model="form.nid_number"
                            :class="inputClass('nid_number')"
                            required
                        />
                    </FormGroup>
                    <FormGroup
                        label="NID Upload"
                        :error="form.errors.nid_file"
                        hint="PDF or image"
                    >
                        <input
                            type="file"
                            :class="fileClass"
                            accept=".pdf,image/*"
                            @change="(e) => handleFile(e, 'nid_file')"
                        />
                    </FormGroup>

                    <FormGroup
                        label="Passport Number *"
                        :error="form.errors.passport_number"
                    >
                        <input
                            v-model="form.passport_number"
                            :class="inputClass('passport_number')"
                            required
                        />
                    </FormGroup>
                    <FormGroup
                        label="Passport Upload"
                        :error="form.errors.passport_file"
                        hint="PDF or image"
                    >
                        <input
                            type="file"
                            :class="fileClass"
                            accept=".pdf,image/*"
                            @change="(e) => handleFile(e, 'passport_file')"
                        />
                    </FormGroup>

                    <FormGroup
                        label="Date of Birth"
                        :error="form.errors.date_of_birth"
                    >
                        <VueDatePicker
                            v-model="form.date_of_birth"
                            :enable-time-picker="false"
                            model-type="yyyy-MM-dd"
                            :max-date="new Date()"
                            :input-class="dateInputClass('date_of_birth')"
                        />
                    </FormGroup>

                    <FormGroup label="Client Photo" :error="form.errors.photo">
                        <input
                            type="file"
                            :class="fileClass"
                            accept="image/*"
                            @change="(e) => handleFile(e, 'photo')"
                        />
                    </FormGroup>

                    <FormGroup label="District" :error="form.errors.district">
                        <input
                            v-model="form.district"
                            :class="inputClass('district')"
                        />
                    </FormGroup>
                    <FormGroup label="Address" :error="form.errors.address">
                        <input
                            v-model="form.address"
                            :class="inputClass('address')"
                        />
                    </FormGroup>
                </div>
            </section>

            <!-- Job & Placement -->
            <section
                class="rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Job & Placement
                    </h2>
                    <p class="text-sm text-gray-600">
                        Select job sector and foreign company details.
                    </p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <!-- Job Sector with Search and Add -->
                    <FormGroup
                        label="Job Sector"
                        :error="form.errors.job_sector"
                    >
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input
                                    v-model="jobSectorSearch"
                                    @focus="showJobSectorDropdown = true"
                                    @blur="hideJobSectorDropdown"
                                    type="text"
                                    :class="inputClass('job_sector')"
                                    :placeholder="
                                        selectedJobSectorName ||
                                        'Search or select job sector...'
                                    "
                                />
                                <div
                                    v-if="showJobSectorDropdown"
                                    class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto"
                                >
                                    <div
                                        v-for="sector in filteredJobSectors"
                                        :key="sector.id"
                                        @mousedown.prevent="
                                            selectJobSector(sector)
                                        "
                                        class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                    >
                                        <div class="font-medium text-gray-900">
                                            {{ sector.name }}
                                        </div>
                                    </div>
                                    <div
                                        v-if="filteredJobSectors.length === 0"
                                        class="px-4 py-3 text-sm text-gray-500 text-center"
                                    >
                                        No job sectors found
                                    </div>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="openAddJobSectorModal"
                                class="flex-shrink-0 inline-flex items-center justify-center rounded-lg bg-blue-600 p-2.5 text-white hover:bg-blue-700 transition"
                                title="Add new job sector"
                            >
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>
                            </button>
                        </div>
                    </FormGroup>

                    <FormGroup
                        v-if="availableSubSectors.length"
                        label="Job Sub-Sector"
                        :error="form.errors.job_sub_sector"
                    >
                        <select
                            v-model="form.job_sub_sector"
                            :class="inputClass('job_sub_sector')"
                        >
                            <option value="">Select option</option>
                            <option
                                v-for="sub in availableSubSectors"
                                :key="sub.id"
                                :value="sub.name"
                            >
                                {{ sub.name }}
                            </option>
                        </select>
                    </FormGroup>

                    <!-- Foreign Company Country Searchable Dropdown -->
                    <FormGroup
                        label="Foreign Company Country"
                        :error="form.errors.foreign_company_country"
                    >
                        <div class="relative">
                            <input
                                v-model="countrySearch"
                                @focus="showCountryDropdown = true"
                                @blur="hideCountryDropdown"
                                type="text"
                                :class="inputClass('foreign_company_country')"
                                :placeholder="
                                    selectedCountry ||
                                    'Search or select country...'
                                "
                            />
                            <div
                                v-if="showCountryDropdown"
                                class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    v-for="country in filteredCountries"
                                    :key="country"
                                    @mousedown.prevent="selectCountry(country)"
                                    class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ country }}
                                    </div>
                                </div>
                                <div
                                    v-if="filteredCountries.length === 0"
                                    class="px-4 py-3 text-sm text-gray-500 text-center"
                                >
                                    No countries found
                                </div>
                            </div>
                        </div>
                    </FormGroup>

                    <!-- Foreign Company Name Searchable Dropdown -->
                    <FormGroup
                        label="Foreign Company Name"
                        :error="form.errors.foreign_company_name"
                    >
                        <div class="relative">
                            <input
                                v-model="companySearch"
                                @focus="showCompanyDropdown = true"
                                @blur="hideCompanyDropdown"
                                type="text"
                                :class="inputClass('foreign_company_name')"
                                :placeholder="
                                    selectedCompanyName ||
                                    'Search or select company...'
                                "
                                :disabled="!selectedCountry"
                            />
                            <div
                                v-if="showCompanyDropdown && selectedCountry"
                                class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    v-for="company in filteredForeignCompanies"
                                    :key="company.id"
                                    @mousedown.prevent="
                                        selectForeignCompany(company)
                                    "
                                    class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ company.name }}
                                    </div>
                                    <div
                                        v-if="company.contact_person_name"
                                        class="text-xs text-gray-500"
                                    >
                                        Contact:
                                        {{ company.contact_person_name }}
                                    </div>
                                </div>
                                <div
                                    v-if="filteredForeignCompanies.length === 0"
                                    class="px-4 py-3 text-sm text-gray-500 text-center"
                                >
                                    No companies found for {{ selectedCountry }}
                                </div>
                            </div>
                        </div>
                    </FormGroup>

                    <FormGroup
                        label="Foreign Company Email"
                        :error="form.errors.foreign_company_email"
                    >
                        <input
                            v-model="form.foreign_company_email"
                            type="email"
                            :class="inputClass('foreign_company_email')"
                            placeholder="Email address (optional)"
                        />
                    </FormGroup>
                    <FormGroup
                        label="Foreign Company Mobile"
                        :error="form.errors.foreign_company_phone"
                    >
                        <input
                            v-model="form.foreign_company_phone"
                            :class="inputClass('foreign_company_phone')"
                            placeholder="Contact phone number"
                        />
                    </FormGroup>

                    <!-- Assign Agent Searchable Dropdown -->
                    <FormGroup
                        label="Assign Agent *"
                        :error="form.errors.agent_id"
                    >
                        <div class="relative">
                            <input
                                v-model="agentSearch"
                                @focus="showAgentDropdown = true"
                                @blur="hideAgentDropdown"
                                type="text"
                                :class="inputClass('agent_id')"
                                :placeholder="
                                    selectedAgentName ||
                                    'Search agent by name or phone...'
                                "
                                required
                            />
                            <div
                                v-if="showAgentDropdown"
                                class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    v-for="agent in filteredAgents"
                                    :key="agent.id"
                                    @mousedown.prevent="selectAgent(agent)"
                                    class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ agent.name }}
                                    </div>
                                    <div
                                        v-if="agent.mobile"
                                        class="text-xs text-gray-500"
                                    >
                                        Mobile: {{ agent.mobile }}
                                    </div>
                                </div>
                                <div
                                    v-if="filteredAgents.length === 0"
                                    class="px-4 py-3 text-sm text-gray-500 text-center"
                                >
                                    No agents found
                                </div>
                            </div>
                        </div>
                    </FormGroup>

                    <FormGroup
                        label="Agent Mobile"
                        :error="form.errors.agent_mobile"
                    >
                        <input
                            v-model="form.agent_mobile"
                            :class="inputClass('agent_mobile')"
                            placeholder="Auto-filled when agent selected"
                            readonly
                        />
                    </FormGroup>

                    <FormGroup
                        label="Agent District"
                        :error="form.errors.agent_district"
                    >
                        <input
                            v-model="form.agent_district"
                            :class="inputClass('agent_district')"
                            placeholder="Auto-filled when agent selected"
                            readonly
                        />
                    </FormGroup>

                    <FormGroup
                        label="Agent Address"
                        :error="form.errors.agent_address"
                    >
                        <textarea
                            v-model="form.agent_address"
                            :class="textareaClass"
                            rows="2"
                            placeholder="Auto-filled when agent selected"
                            readonly
                        ></textarea>
                    </FormGroup>
                </div>
            </section>

            <!-- Medical & Documents -->
            <section
                class="rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Medical & Documents
                    </h2>
                    <p class="text-sm text-gray-600">
                        Track medical fee, fit report, and where the papers are.
                    </p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup
                        label="Medical Fee"
                        :error="form.errors.medical_fee"
                    >
                        <input
                            v-model="form.medical_fee"
                            type="number"
                            step="0.01"
                            :class="inputClass('medical_fee')"
                        />
                    </FormGroup>
                    <FormGroup
                        label="Medical Report"
                        :error="form.errors.medical_report"
                        hint="PDF or image"
                    >
                        <input
                            type="file"
                            :class="fileClass"
                            accept=".pdf,image/*"
                            @change="(e) => handleFile(e, 'medical_report')"
                        />
                    </FormGroup>

                    <FormGroup
                        label="Fit / Unfit"
                        :error="form.errors.fit_status"
                    >
                        <select
                            v-model="form.fit_status"
                            :class="inputClass('fit_status')"
                        >
                            <option
                                v-for="option in fitStatusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </FormGroup>
                    <FormGroup
                        label="Fit/Unfit Report"
                        :error="form.errors.fit_report"
                        hint="PDF or image"
                    >
                        <input
                            type="file"
                            :class="fileClass"
                            accept=".pdf,image/*"
                            @change="(e) => handleFile(e, 'fit_report')"
                        />
                    </FormGroup>

                    <FormGroup
                        label="Documents Submitted To (BD Company)"
                        :error="form.errors.documents_submitted_to"
                    >
                        <div class="relative">
                            <input
                                v-model="documentsCompanySearch"
                                @focus="showDocumentsCompanyDropdown = true"
                                @blur="hideDocumentsCompanyDropdown"
                                type="text"
                                :class="inputClass('documents_submitted_to')"
                                :placeholder="
                                    selectedDocumentsCompanyName ||
                                    'Search BD company by name or phone...'
                                "
                            />
                            <div
                                v-if="showDocumentsCompanyDropdown"
                                class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg max-h-60 overflow-auto"
                            >
                                <div
                                    v-for="company in filteredDocumentsCompanies"
                                    :key="company.id"
                                    @mousedown.prevent="
                                        selectDocumentsCompany(company)
                                    "
                                    class="px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{ company.name }}
                                    </div>
                                    <div
                                        v-if="
                                            company.contact_person_phone ||
                                            company.owner_phone
                                        "
                                        class="text-xs text-gray-500"
                                    >
                                        Phone:
                                        {{
                                            company.contact_person_phone ||
                                            company.owner_phone
                                        }}
                                    </div>
                                </div>
                                <div
                                    v-if="filteredDocumentsCompanies.length === 0"
                                    class="px-4 py-3 text-sm text-gray-500 text-center"
                                >
                                    No BD companies found
                                </div>
                            </div>
                        </div>
                    </FormGroup>
                    <FormGroup
                        label="Company Phone"
                        :error="form.errors.documents_submission_phone"
                    >
                        <input
                            v-model="form.documents_submission_phone"
                            :class="inputClass('documents_submission_phone')"
                            placeholder="Auto-filled when BD company selected"
                        />
                    </FormGroup>

                    <DateField
                        label="Date of Submission"
                        v-model="form.date_of_submission"
                        :error="form.errors.date_of_submission"
                    />
                    <DateField
                        label="Expected Date to Collect"
                        v-model="form.expected_date_to_collect"
                        :error="form.errors.expected_date_to_collect"
                    />
                    <DateField
                        label="Documents Collected Date"
                        v-model="form.documents_collected_date"
                        :error="form.errors.documents_collected_date"
                    />
                </div>
            </section>

            <!-- Payments -->
            <section
                class="rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Payments
                    </h2>
                    <p class="text-sm text-gray-600">
                        Track total fee, dues, and instalments.
                    </p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <FormGroup
                        label="Current Fee *"
                        :error="form.errors.total_fee"
                        hint="Total amount to collect"
                    >
                        <input
                            v-model="form.total_fee"
                            type="number"
                            step="0.01"
                            :class="inputClass('total_fee')"
                            required
                        />
                    </FormGroup>
                    <FormGroup
                        label="Paid Amount"
                        :error="form.errors.partial_paid_amount"
                        hint="Add new payment to update due"
                    >
                        <input
                            v-model="form.partial_paid_amount"
                            type="number"
                            step="0.01"
                            :class="inputClass('partial_paid_amount')"
                        />
                    </FormGroup>
                    <FormGroup
                        label="Current Due *"
                        :error="form.errors.current_due"
                        hint="Auto-calculated"
                    >
                        <input
                            v-model="form.current_due"
                            type="number"
                            step="0.01"
                            :class="inputClass('current_due')"
                            readonly
                            required
                        />
                    </FormGroup>
                    <DateField
                        label="Date of Partial Payment"
                        v-model="form.partial_payment_date"
                        :error="form.errors.partial_payment_date"
                    />
                </div>
            </section>

            <!-- Notes -->
            <section
                class="rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Additional Notes
                    </h2>
                    <p class="text-sm text-gray-600">
                        Any extra details or comments.
                    </p>
                </div>
                <div class="p-6">
                    <FormGroup label="Notes" :error="form.errors.notes">
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            :class="textareaClass"
                            placeholder="Add any additional information about this client..."
                        ></textarea>
                    </FormGroup>
                </div>
            </section>

            <!-- Status -->
            <section
                class="rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Client Status
                    </h2>
                    <p class="text-sm text-gray-600">
                        Track online, calling, and e-visa progress.
                    </p>
                </div>
                <div class="grid gap-4 p-6 md:grid-cols-3">
                    <FormGroup
                        label="Online Status"
                        :error="form.errors.online_status"
                    >
                        <select
                            v-model="form.online_status"
                            :class="inputClass('online_status')"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </FormGroup>
                    <FormGroup
                        label="Calling Status"
                        :error="form.errors.calling_status"
                    >
                        <select
                            v-model="form.calling_status"
                            :class="inputClass('calling_status')"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </FormGroup>
                    <FormGroup
                        label="E-Visa Status"
                        :error="form.errors.evisa_status"
                    >
                        <select
                            v-model="form.evisa_status"
                            :class="inputClass('evisa_status')"
                        >
                            <option
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </FormGroup>
                </div>
            </section>

            <div class="flex items-center justify-end gap-3">
                <Link
                    href="/clients"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? isEdit
                                ? "Updating..."
                                : "Saving..."
                            : isEdit
                              ? "Update Client"
                              : "Save Client"
                    }}
                </button>
            </div>
        </form>

        <!-- Add Job Sector Modal -->
        <div
            v-if="showAddJobSectorModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="closeAddJobSectorModal"
        >
            <div
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
            >
                <!-- Modal Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">
                        Add New Job Sector
                    </h3>
                    <button
                        @click="closeAddJobSectorModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Job Sector Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="newJobSector.name"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': newJobSectorError }"
                            placeholder="e.g., Driver, Cleaner, etc."
                            @keyup.enter="submitNewJobSector"
                        />
                        <p
                            v-if="newJobSectorError"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ newJobSectorError }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Parent Category
                            <span class="text-gray-400 text-xs"
                                >(Optional)</span
                            >
                        </label>
                        <select
                            v-model="newJobSector.parent_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">None (Main Category)</option>
                            <option
                                v-for="sector in jobSectors"
                                :key="sector.id"
                                :value="sector.id"
                            >
                                {{ sector.name }}
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Select a parent if this is a sub-category
                        </p>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                        >
                            Description
                            <span class="text-gray-400 text-xs"
                                >(Optional)</span
                            >
                        </label>
                        <textarea
                            v-model="newJobSector.description"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="Brief description of this job sector..."
                        ></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex gap-3">
                    <button
                        @click="closeAddJobSectorModal"
                        type="button"
                        class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitNewJobSector"
                        type="button"
                        :disabled="addingJobSector"
                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ addingJobSector ? "Adding..." : "Add Job Sector" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, ref, watch } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import axios from "axios";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

const props = defineProps({
    jobSectors: {
        type: Array,
        default: () => [],
    },
    fitStatusOptions: {
        type: Array,
        default: () => [],
    },
    agents: {
        type: Array,
        default: () => [],
    },
    bdCompanies: {
        type: Array,
        default: () => [],
    },
    foreignCompanies: {
        type: Array,
        default: () => [],
    },
    countries: {
        type: Array,
        default: () => [],
    },
    client: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: "create",
    },
});

const isEdit = computed(() => !!props.client);

const statusOptions = [
    { value: "pending", label: "Pending" },
    { value: "ok", label: "OK" },
    { value: "rejected", label: "Reject" },
];

const buildFormState = () => ({
    name: props.client?.name ?? "",
    photo: null,
    nid_number: props.client?.nid_number ?? "",
    nid_file: null,
    passport_number: props.client?.passport_number ?? "",
    passport_file: null,
    date_of_birth: props.client?.date_of_birth ?? "",
    mobile: props.client?.mobile ?? "",
    district: props.client?.district ?? "",
    address: props.client?.address ?? "",
    job_sector: props.client?.job_sector ?? "",
    job_sub_sector: props.client?.job_sub_sector ?? "",
    foreign_company_country: props.client?.foreign_company_country ?? "",
    foreign_company_name: props.client?.foreign_company_name ?? "",
    foreign_company_email: props.client?.foreign_company_email ?? "",
    foreign_company_phone: props.client?.foreign_company_phone ?? "",
    agent_mobile: props.client?.agent_mobile ?? "",
    agent_address: props.client?.agent_address ?? "",
    agent_district: props.client?.agent_district ?? "",
    medical_fee: props.client?.medical_fee ?? "",
    medical_report: null,
    fit_status: props.client?.fit_status ?? "pending",
    fit_report: null,
    documents_submitted_to: props.client?.documents_submitted_to ?? "",
    documents_submission_phone:
        props.client?.documents_submission_phone ?? "",
    date_of_submission: props.client?.date_of_submission ?? "",
    expected_date_to_collect: props.client?.expected_date_to_collect ?? "",
    documents_collected_date: props.client?.documents_collected_date ?? "",
    total_fee: props.client?.total_fee ?? "0",
    current_due: props.client?.current_due ?? "0",
    partial_paid_amount: props.client?.partial_paid_amount ?? "0",
    partial_payment_date: props.client?.partial_payment_date ?? "",
    notes: props.client?.notes ?? "",
    online_status: props.client?.online_status ?? "pending",
    calling_status: props.client?.calling_status ?? "pending",
    evisa_status: props.client?.evisa_status ?? "pending",
    agent_id: props.client?.agent_id ?? "",
    bd_company_id: props.client?.bd_company_id ?? "",
    foreign_company_id: props.client?.foreign_company_id ?? "",
});

const form = useForm(buildFormState());

const computeDue = () => {
    const total = parseFloat(form.total_fee);
    const paid = parseFloat(form.partial_paid_amount);

    if (Number.isNaN(total)) {
        form.current_due = "";
        return;
    }

    const paidValue = Number.isNaN(paid) ? 0 : paid;
    const due = Math.max(total - paidValue, 0);
    form.current_due = Number.isFinite(due) ? due.toFixed(2) : "";
};

watch(
    () => [form.total_fee, form.partial_paid_amount],
    () => computeDue()
);

// initialize due on load
computeDue();

// Job Sector Search and Modal State
const jobSectorSearch = ref("");
const showJobSectorDropdown = ref(false);
const selectedJobSectorId = ref(null);
const selectedJobSectorName = ref("");
const showAddJobSectorModal = ref(false);
const addingJobSector = ref(false);
const newJobSectorError = ref("");
const newJobSector = ref({
    name: "",
    parent_id: "",
    description: "",
});

// Filtered job sectors based on search
const filteredJobSectors = computed(() => {
    if (!jobSectorSearch.value) {
        return props.jobSectors || [];
    }
    const query = jobSectorSearch.value.toLowerCase();
    return (props.jobSectors || []).filter((sector) =>
        sector.name.toLowerCase().includes(query)
    );
});

// Available sub-sectors when a parent sector is selected
const availableSubSectors = computed(() => {
    const sector = props.jobSectors.find(
        (item) => item.id === selectedJobSectorId.value
    );
    return sector?.children || [];
});

// Select a job sector from dropdown
const selectJobSector = (sector) => {
    selectedJobSectorId.value = sector.id;
    selectedJobSectorName.value = sector.name;
    form.job_sector = sector.name;
    jobSectorSearch.value = "";
    showJobSectorDropdown.value = false;

    // Reset sub-sector if parent changes
    form.job_sub_sector = "";
};

// Hide dropdown with delay to allow click
const hideJobSectorDropdown = () => {
    setTimeout(() => {
        showJobSectorDropdown.value = false;
    }, 200);
};

// Open add job sector modal
const openAddJobSectorModal = () => {
    showAddJobSectorModal.value = true;
    newJobSector.value = {
        name: jobSectorSearch.value || "",
        parent_id: "",
        description: "",
    };
    newJobSectorError.value = "";
};

// Close add job sector modal
const closeAddJobSectorModal = () => {
    showAddJobSectorModal.value = false;
    newJobSector.value = {
        name: "",
        parent_id: "",
        description: "",
    };
    newJobSectorError.value = "";
};

// Submit new job sector
const submitNewJobSector = async () => {
    if (!newJobSector.value.name.trim()) {
        newJobSectorError.value = "Job sector name is required";
        return;
    }

    addingJobSector.value = true;
    newJobSectorError.value = "";

    try {
        const response = await axios.post("/job-sectors", {
            name: newJobSector.value.name.trim(),
            parent_id: newJobSector.value.parent_id || null,
            description: newJobSector.value.description || null,
        });

        // Add the new sector to the list
        const newSector = response.data.jobSector;

        // If it's a child, add to parent's children
        if (newSector.parent_id) {
            const parent = props.jobSectors.find(
                (s) => s.id === newSector.parent_id
            );
            if (parent) {
                parent.children = parent.children || [];
                parent.children.push({
                    id: newSector.id,
                    name: newSector.name,
                });
            }
        } else {
            // Add as new parent sector
            props.jobSectors.push({
                id: newSector.id,
                name: newSector.name,
                children: [],
            });
        }

        // Select the newly added sector if it's a parent
        if (!newSector.parent_id) {
            selectJobSector({
                id: newSector.id,
                name: newSector.name,
            });
        }

        closeAddJobSectorModal();

        // Show success message (optional)
        alert("Job sector added successfully!");
    } catch (error) {
        if (error.response?.status === 422) {
            newJobSectorError.value =
                error.response.data.message || "This job sector already exists";
        } else {
            newJobSectorError.value =
                "Failed to add job sector. Please try again.";
        }
    } finally {
        addingJobSector.value = false;
    }
};

// Foreign Company Country and Company Search State
const countrySearch = ref("");
const showCountryDropdown = ref(false);
const selectedCountry = ref("");
const companySearch = ref("");
const showCompanyDropdown = ref(false);
const selectedCompanyId = ref(null);
const selectedCompanyName = ref("");

// Filtered countries based on search
const filteredCountries = computed(() => {
    if (!countrySearch.value) {
        return props.countries || [];
    }
    const query = countrySearch.value.toLowerCase();
    return (props.countries || []).filter((country) =>
        country.toLowerCase().includes(query)
    );
});

// Filtered foreign companies based on selected country and search
const filteredForeignCompanies = computed(() => {
    let companies = (props.foreignCompanies || []).filter(
        (company) => company.country === selectedCountry.value
    );

    if (companySearch.value) {
        const query = companySearch.value.toLowerCase();
        companies = companies.filter((company) =>
            company.name.toLowerCase().includes(query)
        );
    }

    return companies;
});

// Select a country
const selectCountry = (country) => {
    selectedCountry.value = country;
    form.foreign_company_country = country;
    countrySearch.value = "";
    showCountryDropdown.value = false;

    // Reset company selection when country changes
    selectedCompanyId.value = null;
    selectedCompanyName.value = "";
    form.foreign_company_name = "";
    form.foreign_company_email = "";
    form.foreign_company_phone = "";
    form.foreign_company_id = "";
};

// Hide country dropdown with delay
const hideCountryDropdown = () => {
    setTimeout(() => {
        showCountryDropdown.value = false;
    }, 200);
};

// Select a foreign company and auto-fill details
const selectForeignCompany = (company) => {
    selectedCompanyId.value = company.id;
    selectedCompanyName.value = company.name;
    form.foreign_company_name = company.name;
    form.foreign_company_id = company.id;

    // Auto-fill phone (prefer contact person phone, fallback to owner phone)
    form.foreign_company_phone =
        company.contact_person_phone || company.owner_phone || "";

    // Email field doesn't exist in database, so we keep it empty for manual entry
    form.foreign_company_email = "";

    companySearch.value = "";
    showCompanyDropdown.value = false;
};

// Hide company dropdown with delay
const hideCompanyDropdown = () => {
    setTimeout(() => {
        showCompanyDropdown.value = false;
    }, 200);
};

// Agent Search State
const agentSearch = ref("");
const showAgentDropdown = ref(false);
const selectedAgentId = ref(null);
const selectedAgentName = ref("");

// Filtered agents based on search (by name or phone)
const filteredAgents = computed(() => {
    if (!agentSearch.value) {
        return props.agents || [];
    }
    const query = agentSearch.value.toLowerCase();
    return (props.agents || []).filter(
        (agent) =>
            agent.name?.toLowerCase().includes(query) ||
            agent.mobile?.toLowerCase().includes(query)
    );
});

// Select an agent and auto-fill details
const selectAgent = (agent) => {
    selectedAgentId.value = agent.id;
    selectedAgentName.value = agent.name;
    form.agent_id = agent.id;
    form.agent_mobile = agent.mobile || "";
    form.agent_district = agent.district || "";
    form.agent_address = agent.address || "";

    agentSearch.value = agent.name;
    showAgentDropdown.value = false;
};

// Hide agent dropdown with delay
const hideAgentDropdown = () => {
    setTimeout(() => {
        showAgentDropdown.value = false;
    }, 200);
};

// Documents Submitted To - BD Company Search State
const documentsCompanySearch = ref("");
const showDocumentsCompanyDropdown = ref(false);
const selectedDocumentsCompanyName = ref("");

// Filtered BD companies for documents submission (by name or phone)
const filteredDocumentsCompanies = computed(() => {
    if (!documentsCompanySearch.value) {
        return props.bdCompanies || [];
    }
    const query = documentsCompanySearch.value.toLowerCase();
    return (props.bdCompanies || []).filter(
        (company) =>
            company.name?.toLowerCase().includes(query) ||
            company.contact_person_phone?.toLowerCase().includes(query) ||
            company.owner_phone?.toLowerCase().includes(query)
    );
});

// Select BD company for documents submission and auto-fill phone
const selectDocumentsCompany = (company) => {
    selectedDocumentsCompanyName.value = company.name;
    form.documents_submitted_to = company.name;
    form.documents_submission_phone =
        company.contact_person_phone || company.owner_phone || "";
    form.bd_company_id = company.id;

    documentsCompanySearch.value = "";
    showDocumentsCompanyDropdown.value = false;
};

// Hide documents dropdown with delay
const hideDocumentsCompanyDropdown = () => {
    setTimeout(() => {
        showDocumentsCompanyDropdown.value = false;
    }, 200);
};

const initializeSelectionsFromClient = () => {
    if (!props.client) return;

    selectedJobSectorName.value = props.client.job_sector || "";
    const sector = props.jobSectors.find(
        (item) => item.name === props.client.job_sector
    );
    if (sector) {
        selectedJobSectorId.value = sector.id;
    }

    selectedCountry.value = props.client.foreign_company_country || "";

    const foreignCompany = (props.foreignCompanies || []).find(
        (company) => company.id === props.client.foreign_company_id
    );
    if (foreignCompany) {
        selectedCompanyId.value = foreignCompany.id;
        selectedCompanyName.value = foreignCompany.name;
    } else if (props.client.foreign_company_name) {
        selectedCompanyName.value = props.client.foreign_company_name;
    }

    const agent = (props.agents || []).find(
        (item) => item.id === props.client.agent_id
    );
    if (agent) {
        selectedAgentId.value = agent.id;
        selectedAgentName.value = agent.name;
        agentSearch.value = agent.name;
        form.agent_mobile = form.agent_mobile || agent.mobile || "";
        form.agent_district = form.agent_district || agent.district || "";
        form.agent_address = form.agent_address || agent.address || "";
    }

    if (props.client.documents_submitted_to) {
        selectedDocumentsCompanyName.value =
            props.client.documents_submitted_to;
    }
};

if (isEdit.value) {
    initializeSelectionsFromClient();
}

const baseInput =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";
const baseFile =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500";
const baseTextarea =
    "w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 bg-white";

const inputClass = (field) =>
    [
        baseInput,
        form.errors[field]
            ? "border-red-400 focus:border-red-500 focus:ring-red-500"
            : "",
    ]
        .filter(Boolean)
        .join(" ");

const dateInputClass = (field) =>
    [
        "!rounded-lg",
        "!px-3",
        "!py-2",
        "!text-sm",
        "!border",
        form.errors[field]
            ? "!border-red-400 focus:!border-red-500 focus:!ring-red-500"
            : "!border-gray-200",
        "!bg-white",
        "!text-gray-900",
    ]
        .filter(Boolean)
        .join(" ");

const fileClass = baseFile;
const textareaClass = baseTextarea;

const handleFile = (event, field) => {
    const files = event.target?.files;
    form[field] = files && files.length ? files[0] : null;
};

const resetSelections = () => {
    selectedJobSectorId.value = null;
    selectedJobSectorName.value = "";
    selectedCountry.value = "";
    selectedCompanyId.value = null;
    selectedCompanyName.value = "";
    selectedAgentId.value = null;
    selectedAgentName.value = "";
    agentSearch.value = "";
    selectedDocumentsCompanyName.value = "";
};

const submit = () => {
    form.clearErrors();

    const requiredFields = [
        { key: "name", message: "Client name is required." },
        { key: "nid_number", message: "NID number is required." },
        { key: "passport_number", message: "Passport number is required." },
        { key: "total_fee", message: "Current fee is required." },
        { key: "current_due", message: "Current due is required." },
        { key: "agent_id", message: "Agent selection is required." },
    ];

    let hasError = false;
    requiredFields.forEach((field) => {
        const value = (form[field.key] || "").toString().trim();
        if (!value) {
            form.setError(field.key, field.message);
            hasError = true;
        }
    });

    if (hasError) return;

    const url = isEdit.value && props.client ? `/clients/${props.client.id}` : "/clients";

    if (isEdit.value) {
        form.put(url, {
            forceFormData: true,
            preserveScroll: true,
            onError: () => {},
        });
    } else {
        form.post(url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                resetSelections();
            },
        });
    }
};

const FormGroup = defineComponent({
    name: "FormGroup",
    props: {
        label: String,
        error: String,
        hint: String,
    },
    setup(props, { slots }) {
        return () =>
            h(
                "div",
                { class: "space-y-2", role: "group" },
                [
                    h(
                        "label",
                        { class: "text-sm font-medium text-gray-700" },
                        props.label
                    ),
                    slots.default ? slots.default() : null,
                    props.hint
                        ? h("p", { class: "text-xs text-gray-500" }, props.hint)
                        : null,
                    props.error
                        ? h("p", { class: "text-xs text-red-600" }, props.error)
                        : null,
                ].filter(Boolean)
            );
    },
});

const DateField = defineComponent({
    props: {
        label: String,
        modelValue: String,
        error: String,
    },
    emits: ["update:modelValue"],
    setup(props, { emit }) {
        const update = (value) => emit("update:modelValue", value);
        return () =>
            h("div", { class: "space-y-2" }, [
                h(
                    "label",
                    { class: "text-sm font-medium text-gray-700" },
                    props.label
                ),
                h(VueDatePicker, {
                    modelType: "yyyy-MM-dd",
                    enableTimePicker: false,
                    maxDate: new Date("2100-01-01"),
                    modelValue: props.modelValue,
                    "onUpdate:modelValue": update,
                    inputClass:
                        "!rounded-lg !px-3 !py-2 !text-sm !border !border-gray-200 !bg-white !text-gray-900 focus:!border-blue-500 focus:!ring-blue-500",
                }),
                props.error
                    ? h("p", { class: "text-xs text-red-600" }, props.error)
                    : null,
            ]);
    },
});
</script>
