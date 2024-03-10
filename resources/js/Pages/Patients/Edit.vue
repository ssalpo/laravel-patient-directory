<template>
    <Head>
        <title>{{patient?.id ? 'Обновление данных пациента' : 'Новый пациент'}}</title>
    </Head>

    <div class="content-header">
        <div class="container">
            <h1 class="m-0">{{ patient?.id ? 'Обновление данных пациента' : 'Новый пациент' }}</h1>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="card card-primary">
                <div class="card-body">
                    <form-input
                        label="Ф.И.О"
                        required
                        v-model.trim="form.name"
                        :validation-error="errors.name"
                    />

                    <div class="form-group">
                        <form-select-locations
                            ref="selectLocations"
                            v-model="form.location_id"
                            :invalid-text="errors.location_id"
                            label="Место проживания"
                            @selected="selectedLocation = $event"
                        />

                        <new-location-modal @success="setLocation" />
                    </div>

                    <form-input
                        label="Номер телефона"
                        required
                        v-maska data-maska="+############"
                        placeholder="пример: +992929927233"
                        v-model.trim="form.phone"
                        :validation-error="errors.phone"
                    />

                    <form-input
                        label="Дата рождения"
                        required
                        v-maska data-maska="##.##.####"
                        placeholder="ДД.ММ.ГГГГ"
                        v-model.trim="form.birthday"
                        :validation-error="errors.birthday"
                    />

                    <div class="form-group">
                        <label>Пол</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       :class="{'is-invalid': errors.gender}"
                                       type="radio" v-model="form.gender" name="gender"
                                       :value="1">
                                <label class="form-check-label">Мужской</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       :class="{'is-invalid': errors.gender}"
                                       type="radio" v-model="form.gender" name="gender"
                                       :value="0">
                                <label class="form-check-label">Женский</label>
                            </div>
                        </div>

                        <div v-if="errors.gender" class="error invalid-feedback">
                            {{ errors.gender }}
                        </div>
                    </div>

                    <form-input
                        label="Дата/время забора образца"
                        required
                        v-maska data-maska="##.##.#### ##:##"
                        placeholder="ДД.ММ.ГГГГ ЧЧ:ММ"
                        v-model.trim="form.sampling_date"
                        :validation-error="errors.sampling_date"
                    />

                    <form-input
                        label="Дата/время получения образца"
                        required
                        v-maska data-maska="##.##.#### ##:##"
                        placeholder="ДД.ММ.ГГГГ ЧЧ:ММ"
                        v-model.trim="form.sample_receipt_date"
                        :validation-error="errors.sample_receipt_date"
                    />

                    <form-textarea
                        label="Анамнез"
                        v-model.trim="form.anamnes"
                        :validation-error="errors.anamnes"
                    />

                    <div class="form-group">
                        <label class="form-asterisk">Тип/Место забора образца</label>

                        <div v-for="(category, index) in form.categories">
                            <div class="row mt-2">
                                <div class="col-3 col-sm-2 pb-2 pb-sm-0">
                                    <input type="text" v-model="category.code" class="form-control form-control-sm"
                                           :data-some="category?.biopsyCustom"
                                           placeholder="Введите код, например A1 или B1">
                                </div>
                                <div class="col-9 col-sm-4 pb-2 pb-sm-0">
                                    <div class="row" v-if="category?.biopsy !== 'свой вариант'">
                                        <div class="col-12">
                                            <select @change="biopsyCustomToggle(category, $event.target.value === 'свой вариант')" class="form-control form-control-sm" v-model="category.biopsy">
                                                <option value="" disabled>Выберите тип биопсии</option>
                                                <option :value="biopsy" v-for="biopsy in biopsyTypes">{{biopsy}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row" v-else>
                                        <div class="col-10">
                                            <input type="text" class="form-control form-control-sm" v-model="category.biopsyCustomValue">
                                        </div>
                                        <div class="col-2">
                                            <button class="btn btn-sm" @click="biopsyCustomToggle(category, false)">
                                                <span class="fa fa-times"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-10 col-sm-4">
                                    <input type="text" v-model="category.description"
                                           class="form-control form-control-sm"
                                           placeholder="Введите описание, например рука">
                                </div>
                                <div class="col-2">
                                    <button type="button" @click="removeCategory(index)" class="btn btn-sm btn-warning">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>


                            <div class="row mt-2" v-if="errors[`categories.${index}.code`] || errors[`categories.${index}.description`] || errors[`categories.${index}.biopsy`] || errors[`categories.${index}.biopsyCustomValue`] || errors[`categories.${index}.biopsyCustom`]">
                                <div class="col-3 col-sm-2 pb-2 pb-sm-0">
                                    <div v-if="errors[`categories.${index}.code`]" class="invalid-feedback-simple">
                                        {{errors[`categories.${index}.code`]}}
                                    </div>
                                </div>
                                <div class="col-9 col-sm-4 pb-2 pb-sm-0">
                                    <div v-if="errors[`categories.${index}.biopsy`] || errors[`categories.${index}.biopsyCustomValue`] || errors[`categories.${index}.biopsyCustom`]" class="invalid-feedback-simple">
                                        {{errors[`categories.${index}.biopsy`] || errors[`categories.${index}.biopsyCustomValue`] || errors[`categories.${index}.biopsyCustom`]}}
                                    </div>
                                </div>
                                <div class="col-10 col-sm-4">
                                    <div v-if="errors[`categories.${index}.description`]" class="invalid-feedback-simple">
                                        {{errors[`categories.${index}.description`]}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="button" @click="addCategory" class="btn btn-sm btn-info mt-2">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <form-select-doctors
                            label-required
                            v-if="$page.props.shared.userPermissions.includes('select_doctor_patients')"
                            ref="selectDoctors"
                            v-model="form.doctor_id"
                            :invalid-text="errors.doctor_id"
                            label="Направивший врач"
                            @selected="selectedDoctor = $event"
                        />

                        <new-doctor-modal @success="setDoctor" />
                    </div>

                    <div class="form-group">
                        <form-select-medical-clinics
                            label-required
                            ref="selectMedicalClinics"
                            v-model="form.medical_clinic_id"
                            :invalid-text="errors.medical_clinic_id"
                            label="Направившее учреждение"
                            @selected="selectedMedicalClinic = $event"
                        />

                        <new-medical-clinic-modal @success="setMedicalClinic" />
                    </div>

                    <div class="form-group">
                        <label>Фото</label>

                        <div class="custom-file">
                            <input type="file" :class="{'is-invalid': errors.photos}"
                                   multiple
                                   @input="resizeImages($event.target.files)"
                                   class="custom-file-input">
                            <label class="custom-file-label">
                                {{
                                    form.photos.length ? `Выбранных файлов ${form.photos.length}` : 'Выбрать файлы'
                                }}
                            </label>
                        </div>

                        <div v-if="errors.photos" class="error invalid-feedback">
                            {{ errors.photos }}
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    <form-save-button
                        type="button"
                        @click="patient?.id ? submit() : null"
                        :data-toggle="!patient?.id ? 'modal' : ''"
                        :data-target="!patient?.id ? '#confirm-modal' : ''" :disabled="form.processing"
                        :is-processing="form.processing"
                        :is-editing="patient?.id"
                    />

                    <form-cancel-button
                        :url="route('patients.index')"
                        :is-processing="form.processing"
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Проверьте корректность данных</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <td width="270">Ф.И.О</td>
                            <td>{{ form.name }}</td>
                        </tr>
                        <tr>
                            <td>Дата рождения</td>
                            <td>{{ form.birthday }}</td>
                        </tr>
                        <tr>
                            <td>Место проживания</td>
                            <td>{{ selectedLocation?.text || form.location_id }}</td>
                        </tr>
                        <tr>
                            <td>Пол</td>
                            <td>{{ form.gender ? 'Мужской' : 'Женский' }}</td>
                        </tr>
                        <tr>
                            <td>Дата/время забора образца</td>
                            <td>{{ form.sampling_date }}</td>
                        </tr>
                        <tr>
                            <td>Дата/время получения образца</td>
                            <td>{{ form.sample_receipt_date }}</td>
                        </tr>
                        <tr>
                            <td>Анамнез</td>
                            <td>{{ form.anamnes }}</td>
                        </tr>
                        <tr>
                            <td>Тип/Место забора образца</td>
                            <td>
                                <div v-for="category in form.categories">{{ category.code }}
                                    ({{category.biopsy ? (category?.biopsyCustom ? category.biopsyCustomValue : category.biopsy) + ',' : ''}} {{ category.description }})
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Направивший врач</td>
                            <td>{{ selectedDoctor?.text || selectedDoctor?.name || form.doctor_id }}</td>
                        </tr>
                        <tr>
                            <td>Направившее учреждение</td>
                            <td>{{ selectedMedicalClinic?.text || selectedMedicalClinic?.name || form.medical_clinic_id }}</td>
                        </tr>
                        <tr>
                            <td>Количество прикрепленных фото</td>
                            <td>{{ form.photos.length }}</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Изменить данные</button>

                    <button type="button"
                            @click="submit"
                            class="btn btn-primary"
                            :disabled="form.processing">
                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin"></i> Сохранение...
                        </span>

                        <span v-else>{{ patient?.id ? 'Сохранить' : 'Добавить' }}</span>
                    </button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</template>
<script>
import {Head, Link, useForm} from "@inertiajs/vue3";
import { vMaska } from "maska"
import resizeImage from "../../utils/resizeImage";
import FormInput from "../../Shared/Form/FormInput.vue";
import FormTextarea from "../../Shared/Form/FormTextarea.vue";
import FormCancelButton from "../../Shared/Form/FormCancelButton.vue";
import FormSaveButton from "../../Shared/Form/FormSaveButton.vue";
import FormSelectDoctors from "../../Shared/Form/FormSelectDoctors.vue";
import FormSelectRoles from "../../Shared/Form/FormSelectRoles.vue";
import NewDoctorModal from "../../Shared/Modals/NewDoctorModal.vue";
import NewMedicalClinicModal from "../../Shared/Modals/NewMedicalClinicModal.vue";
import FormSelectMedicalClinics from "../../Shared/Form/FormSelectMedicalClinics.vue";
import FormSelectLocations from "../../Shared/Form/FormSelectLocations.vue";
import NewLocationModal from "../../Shared/Modals/NewLocationModal.vue";

export default {
    props: ['doctors', 'medicalClinics', 'patient', 'errors'],
    components: {
        NewLocationModal,
        FormSelectLocations,
        FormSelectMedicalClinics,
        NewMedicalClinicModal,
        NewDoctorModal,
        FormSelectRoles,
        FormSelectDoctors, FormSaveButton, FormCancelButton, FormTextarea, FormInput, Head, Link},
    directives: { maska: vMaska },
    data() {
        return {
            customBiopsyToggle: [],
            selectedDoctor: null,
            selectedMedicalClinic: null,
            selectedLocation: null,
            biopsyTypes: ['шейв-биопсия', 'панч-биопсия', 'свой вариант'],
            form: useForm({
                name: this.patient?.name,
                phone: this.patient?.phone,
                birthday: this.patient?.birthday || '',
                gender: this.patient?.gender !== undefined ? this.patient?.gender : 1,
                sampling_date: this.patient?.sampling_date,
                sample_receipt_date: this.patient?.sample_receipt_date,
                anamnes: this.patient?.anamnes,
                doctor_id: this.patient?.doctor_id || null,
                categories: this.patient?.categories || [{code: 'A1', biopsy: 'шейв-биопсия', biopsyCustomValue: null, biopsyCustom: false, description: ''}],
                photos: this.patient?.photos || [],
                location_id: this.patient?.location?.id,
                medical_clinic_id: this.patient?.medical_clinic_id || null,
            }),
        }
    },
    methods: {
        submit() {
            if (!this.patient?.id) {
                $('#confirm-modal').modal('hide');
                this.form.post('/patients', {forceFormData: true});
                return;
            }

            this.form
                .transform(data => {
                    data['_method'] = 'put';
                    return data;
                })
                .post(`/patients/${this.patient.id}`, {forceFormData: true})
        },
        addCategory() {
            this.form.categories.push({code: '', biopsy: null, biopsyCustom: false, biopsyCustomValue: null, description: ''})
        },
        biopsyCustomToggle(category, isCustom) {
            category.biopsyCustom = isCustom

            if(isCustom) {
                category.biopcy = null
            } else {
                category.biopsyCustomValue = null
            }
        },
        removeCategory(index) {
            this.form.categories.splice(index, 1);
        },
        setDoctor(doctor) {
            this.$refs.selectDoctors.refreshData()

            this.form.doctor_id = doctor.id

            this.selectedDoctor = doctor

            this.form.clearErrors()
        },
        setMedicalClinic(medicalClinic) {
            this.$refs.selectMedicalClinics.refreshData()

            this.form.medical_clinic_id = medicalClinic.id

            this.selectedMedicalClinic = medicalClinic

            this.form.clearErrors()
        },
        setLocation(location) {
            this.$refs.selectLocations.refreshData()

            this.form.location_id = location.id

            this.selectedLocation = location

            this.form.clearErrors()
        },
        resizeImages: async function (values) {
            let resizedImages = [];

            for (let i = 0; i < values.length; i++) {

                const config = {
                    file: values[i],
                    maxSize: 1800
                };

                resizedImages.push(await resizeImage(config))
            }

            this.form.photos = resizedImages;
        }
    }
}
</script>
