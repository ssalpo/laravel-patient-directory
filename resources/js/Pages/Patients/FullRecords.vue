<template>
    <Head>
        <title>Дерматопатология</title>
    </Head>

    <div class="content">
        <div class="container-fluid p-sm-1 p-md-5">
            <Link :href="route('patients.all')" class="mt-2 mt-sm-0 mb-3 d-block">Назад к списку</Link>

            <div class="form-group row align-items-center">
                <label for="per-page" class="col-auto col-form-label">Записей на странице:</label>
                <div class="col-auto">
                    <select id="per-page" class="form-control" :value="patients.meta.per_page" @change="changePerPage">
                        <option :value="100">100</option>
                        <option :value="250">250</option>
                        <option :value="500">500</option>
                        <option :value="1000">1000</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-bordered" style="width: 3000px">
                    <thead>
                    <tr>
                        <th width="150">Номер кейса</th>
                        <th width="250">Ф.И.О</th>
                        <th width="250">Место проживания</th>
                        <th width="500">Микроскопическое описание</th>
                        <th width="500">Диагноз</th>
                        <th width="500">Заметка</th>
                        <th width="500">Комментарий</th>
                        <th>Номер телефона</th>
                        <th>Дата рождения</th>
                        <th>Возраст</th>
                        <th>Пол</th>
                        <th>Направивший врач</th>
                        <th>Направившее учреждение</th>
                        <th>Анамнез</th>
                        <th>Тип/место забора образца</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="patient in patients.data" :key="patient.id">
                        <td>{{ patient.case_numbers_joined }}</td>
                        <td>{{ patient.name }}</td>
                        <td>{{ patient.location?.full_address || patient.place_of_residence }}</td>
                        <td class="diagnosis-table" v-html="patient.microscopic_description"></td>
                        <td class="diagnosis-table" v-html="patient.diagnosis"></td>
                        <td class="diagnosis-table" v-html="patient.note"></td>
                        <td class="diagnosis-table" v-html="patient.comment"></td>
                        <td>{{ patient.phone }}</td>
                        <td>{{ patient.birthday }}</td>
                        <td>{{ patient.age }}</td>
                        <td>{{ patient.gender ? 'М' : 'Ж' }}</td>
                        <td>{{ patient.doctor?.name }}</td>
                        <td>{{ patient.medical_clinic?.name }}</td>
                        <td>{{ patient.anamnes }}</td>
                        <td>{{ patient.categories_formatted }}</td>
                    </tr>

                    </tbody>
                </table>
            </div>

            <div class="card-footer clearfix" v-if="patients.meta.last_page > 1">
                <pagination :links="patients.meta.links"/>
            </div>
        </div>
    </div>
</template>
<script>
import {Head, Link} from "@inertiajs/vue3";
import AuthLayout from "../../Layouts/AuthLayout.vue";
import Pagination from "../../Shared/Pagination.vue";

export default {
    components: {Pagination, AuthLayout, Head, Link},
    props: ['patients'],
    layout: AuthLayout,
    methods: {
        changePerPage(event) {
            this.$inertia.get(route('patients.full_records'), {
                per_page: event.target.value,
            }, {
                preserveScroll: true,
            });
        },
    },
}
</script>
