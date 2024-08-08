<template>
    <Head>
        <title>Данные пациента</title>
    </Head>

    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Данные пациента</h1>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <Link :href="route('patients.print', {patient: patient.id})" class="btn btn-warning mr-2">
                        <i class="fa fa-print"></i>
                    </Link>

                    <Link v-if="$page.props.shared.userPermissions.includes('edit_patients')"
                          :href="route('patients.edit', {patient: patient.id})" class="btn btn-primary">
                        <i class="fa fa-pencil-alt"></i>
                    </Link>
                </div><!-- /.col -->
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <h4 class="mt-2 mb-3">Общие данные</h4>

                    <div class="table-responsive">

                        <table class="table table-bordered">
                        <tbody>
                        <tr v-if="$page.props.shared.userPermissions.includes('share_patients')">
                            <td width="400">Поделиться</td>
                            <td> <share-with-user :patient-id="patient.id" :shared-to-id="patient.shared_to_id" /></td>
                        </tr>
                        <tr>
                            <td width="400">Уникальный код пациента</td>
                            <td>{{ patient.uniq_code }}</td>
                        </tr>
                        <tr>
                            <td width="400">ФИО пациента</td>
                            <td>{{ patient.name }}</td>
                        </tr>
                        <tr>
                            <td width="400">Место проживания</td>
                            <td>{{ patient.location?.full_address || patient.place_of_residence}}</td>
                        </tr>
                        <tr>
                            <td width="400">Номер телефона</td>
                            <td>{{ patient.phone }}</td>
                        </tr>
                        <tr>
                            <td>Дата рождения</td>
                            <td>{{ patient.birthday }}</td>
                        </tr>
                        <tr>
                            <td>Пол</td>
                            <td>{{ patient.gender ? 'М' : 'Ж' }}</td>
                        </tr>
                        <tr>
                            <td>Номер медицинской записи</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Дата/время забора образца</td>
                            <td>{{ patient.sampling_date }}</td>
                        </tr>
                        <tr>
                            <td>Дата/время получения образца</td>
                            <td>{{ patient.sample_receipt_date }}</td>
                        </tr>
                        <tr :class="{'animate-background': $page.props.flash.isCreated === true}">
                            <td>Номер кейса</td>
                            <td>
                                <div v-for="case_number in patient.case_numbers">{{ case_number.formatted }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Направивший врач</td>
                            <td>
                                {{ patient.doctor?.name }}
                            </td>
                        </tr>
                        <tr>
                            <td>Направившее учреждение</td>
                            <td>
                                {{ patient.medical_clinic?.name }}
                            </td>
                        </tr>
                        <tr>
                            <td>Анамнез</td>
                            <td>
                                {{ patient.anamnes }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    </div>

                    <h4 class="mt-5 mb-3">Гистопатологическое заключение</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <td width="400">Тип/место забора образца</td>
                            <td>
                                {{ patient.categories_formatted }}
                            </td>
                        </tr>
                        <tr>
                            <td>Микроскопическое описание</td>
                            <td>

                                <div v-if="$page.props.shared.userPermissions.includes('add_report')">
                                    <QuillEditor theme="snow"
                                                 @blur="saveReport"
                                                 @ready="focusOnReady"
                                                 v-if="editBlock === 'microscopic_description' || !patient.microscopic_description"
                                                 contentType="html"
                                                 :toolbar="['bold', 'italic', 'underline']"
                                                 v-model:content="form.microscopic_description"/>

                                    <div v-else>
                                        <div class="editor-content" v-html="form.microscopic_description"></div>

                                        <a href="" @click.prevent="editBlock = 'microscopic_description'"><small>Редактировать</small></a>
                                    </div>
                                </div>
                                <div v-else>
                                    <span v-if="patient.status === 2">{{ patient.microscopic_description }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <b>Диагноз</b>
                            </td>
                            <td>
                                <div v-if="$page.props.shared.userPermissions.includes('add_report')">
                                    <QuillEditor theme="snow"
                                                 @blur="saveReport"
                                                 @ready="focusOnReady"
                                                 v-if="editBlock === 'diagnosis' || !patient.diagnosis"
                                                 contentType="html"
                                                 :toolbar="['bold', 'italic', 'underline']"
                                                 v-model:content="form.diagnosis"/>

                                    <div v-else>
                                        <div class="editor-content" v-html="patient.diagnosis"></div>
                                        <a href="" @click.prevent="editBlock = 'diagnosis'"><small>Редактировать</small></a>
                                    </div>
                                </div>
                                <div v-else>
                                    <span v-if="patient.status === 2" v-html="patient.diagnosis"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Заметка</td>
                            <td>
                                <div v-if="$page.props.shared.userPermissions.includes('add_report')">
                                    <QuillEditor theme="snow"
                                                 @blur="saveReport"
                                                 @ready="focusOnReady"
                                                 v-if="editBlock === 'note' || !patient.note"
                                                 contentType="html"
                                                 :toolbar="['bold', 'italic', 'underline']"
                                                 v-model:content="form.note"/>

                                    <div v-else>
                                        <div class="editor-content" v-html="patient.note"></div>
                                        <a href="" @click.prevent="editBlock = 'note'"><small>Редактировать</small></a>
                                    </div>
                                </div>
                                <div v-else>
                                    <span v-if="patient.status === 2">{{ patient.note }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="$page.props.shared.userPermissions.includes('add_report') && patient.status === 1">
                            <td>
                                <b>Статус проверки</b>
                            </td>
                            <td>
                                <Link :href="route('patients.mark_as_checked', patient.id)" preserve-scroll
                                      class="btn btn-primary" method="post" as="button">Submit
                                </Link>
                            </td>
                        </tr>
                        <tr>
                            <td rowspan="2" class="align-middle text-bold">Ссылка карточки пациента</td>
                            <td>
                                {{ route('public.patients.show', patient.hashid) }}
                            </td>
                        </tr>
                        <tr>
                            <td v-html="qrCode"/>
                        </tr>
                        </tbody>
                    </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <td width="400">
                                    <b>Комментарий</b>
                                </td>
                                <td>
                                    <div v-if="$page.props.shared.userPermissions.includes('add_comment')">
                                        <QuillEditor theme="snow"
                                                     @blur="saveComment"
                                                     @ready="focusOnReady"
                                                     v-if="editBlock === 'comment' || !patient.comment"
                                                     contentType="html"
                                                     :toolbar="['bold', 'italic', 'underline']"
                                                     v-model:content="formComment.comment" />

                                        <div v-else>
                                            <div class="editor-content" v-html="patient.comment"></div>
                                            <a href="" @click.prevent="editBlock = 'comment'"><small>Редактировать</small></a>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <span v-html="patient.comment"></span>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4 class="mt-5 mb-3" v-show="patient.photos.length > 0">Прикрепленные фотография</h4>

                    <div v-for="(photo, index) in patient.photos"
                         class="btn-group btn-group-sm" role="group">
                        <button type="button"
                                @click="selectPhoto(photo.url, index)"
                                data-toggle="modal" data-target="#photo-view-modal"
                                class="btn btn-default btn-sm mr-1">Фото {{ index + 1 }}
                        </button>
                        <button type="button"
                                v-if="$page.props.shared.userPermissions.includes('edit_patients')"

                                class="btn btn-danger btn-sm mr-1" @click="deletePhoto(photo.id)">
                            <span class="fa fa-trash"></span>
                        </button>
                    </div>

                    <div class="modal fade" id="photo-view-modal">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-center">

                                    <div class="row">
                                        <div class="col-6 text-sm-left text-md-right">
                                            <a :href="selectedPhoto" target="_blank"
                                               class="btn btn-sm btn-info mb-3 mr-3">
                                                оригинал
                                            </a>
                                        </div>
                                        <div class="col-6 text-sm-right text-md-left" v-if="patient.photos.length > 1">
                                            <a href="#" @click.prevent="slidePhoto('prev')" class="btn btn-default btn-sm mr-2">
                                                <i class="fa fa-arrow-left"></i>
                                            </a>
                                            <a href="#" @click.prevent="slidePhoto('next')" class="btn btn-default btn-sm">
                                                <i class="fa fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <span class="mt-2" v-if="photoLoading">Фотография загружается...</span>
                                    <span class="mt-2" v-if="photoLoadingError">
                                        Ошибка загрузки фотографии, попробуйте еще раз.
                                    </span>

                                    <div>
                                        <img v-lazy="selectedPhoto" style="width: auto; max-width: 100%; max-height: 600px; display: block; margin: 0 auto;">
                                    </div>
                                </div>
                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                    </div>
                    <!-- /.modal -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {Head, Link, useForm} from "@inertiajs/vue3";
import {QuillEditor} from '@vueup/vue-quill';
import ShareWithUser from "../../Shared/ShareWithUser.vue";

export default {
    components: {ShareWithUser, Head, Link, QuillEditor},
    props: ['patient', 'qrCode'],
    data: function () {
        return {
            originalPhotoShowed: false,
            photoLoading: false,
            photoLoadingError: false,
            selectedPhoto: '',
            selectedPhotoIndex: null,
            editBlock: '',
            form: useForm({
                microscopic_description: this.patient.microscopic_description || '',
                diagnosis: this.patient.diagnosis || '',
                note: this.patient.note || ''
            }),
            formComment: useForm({
                comment: this.patient?.comment || ''
            })
        }
    },
    created() {
        this.$Lazyload.$on('loading', (listener) => {
            this.photoLoading = true
        });

        this.$Lazyload.$on('loaded', (listener) => {
            this.photoLoading = false
        });

        this.$Lazyload.$on('error', (listener) => {
            this.photoLoading = false
            this.photoLoadingError = true
        });

        $(document).on('hide.bs.modal', '#photo-view-modal', () => {
            this.originalPhotoShowed = false
        });
    },
    methods: {
        saveReport() {
            this.form.post(route('patients.save.report', this.patient.id), {preserveState: true, preserveScroll: true})

            this.editBlock = ''
        },
        saveComment() {
            this.formComment.post(route('patients.save.comment', this.patient.id), {preserveState: true, preserveScroll: true})

            this.editBlock = ''
        },
        deletePhoto(photo) {
            if(!confirm('Вы уверены что хотите удалить фотографию?')) return;

            this.form.delete(route('patients.photos.delete', {patient: this.patient.id, photo}), {preserveState: true, preserveScroll: true})
        },
        showOriginalPhoto() {
            this.selectedPhoto = this.selectedPhoto.replace('/thumb', '');
            this.originalPhotoShowed = true;
        },
        focusOnReady(editor) {
            if (this.editBlock) editor.focus();
        },
        selectPhoto(url, index){
            this.selectedPhoto = `/storage/${url}`
            this.selectedPhotoIndex = index
        },
        slidePhoto(type) {
            if(type === 'next') {
                this.selectedPhotoIndex += 1;

                if(this.selectedPhotoIndex > this.patient.photos.length - 1) {
                    this.selectedPhotoIndex = 0;
                }
            }

            if(type === 'prev') {
                this.selectedPhotoIndex -= 1;

                if(this.selectedPhotoIndex < 0) {
                    this.selectedPhotoIndex = this.patient.photos.length - 1;
                }
            }

            this.selectedPhoto = `/storage/${this.patient.photos.find((_, i) => i === this.selectedPhotoIndex).url}`;
        }
    }
}
</script>
