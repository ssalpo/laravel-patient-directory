<template>
    <Head>
        <title>Дерматопатология</title>
    </Head>

    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-12 col-sm-6">
                    <h1 class="m-0">Дерматопатология</h1>
                </div>

                <div class="col-12 col-sm-6 text-left text-sm-right mt-3 mt-sm-0">
                    <Link class="btn btn-outline-primary mr-2"
                          v-if="$page.props.shared.userPermissions.includes('read_all_patients')"
                          :href="route('patients.full_records')">
                        <i class="fa fa-list"></i> Все пациенты
                    </Link>

                    <Link class="btn btn-info mr-2"
                          v-if="$page.props.shared.userPermissions.includes('share_patients') || $page.props.shared.userPermissions.includes('read_shared_patients')"
                          :href="route('patients.daily-statistics')">
                        <i class="fa fa-chart-line"></i>
                    </Link>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <input type="text"
                                   @blur="doSearch"
                                   @keydown.enter="doSearch"
                                   v-model="search.query"
                                   class="form-control form-control-sm"
                                   placeholder="Ф.И.О, код, диагноз"/>
                        </div>
                        <div class="col-6">
                            <select v-model="search.status" @change="doSearch" class="form-control form-control-sm">
                                <option :value="null">Выберите статус</option>
                                <option :value="1">На проверке</option>
                                <option :value="2">Проверено</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th width="150">Номер кейса</th>
                                <th width="250">Ф.И.О</th>
                                <th>Диагноз</th>
                                <th width="130">Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="patient in patients.data">
                                <td>{{ patient.case_numbers_joined }}</td>
                                <td>
                                    <Link :href="route('patients.show', patient.id)">{{ patient.name }}</Link>
                                </td>
                                <td>
                                    <div class="diagnosis-table" v-html="patient.diagnosis"></div>
                                </td>
                                <td :class="[patient.status === 1 ? 'text-danger' : 'text-success']">
                                    {{ patient.status === 1 ? 'На проверке' : 'Проверено' }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer clearfix" v-if="patients.meta.last_page > 1">
                    <pagination :links="patients.meta.links"/>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {Head, Link} from "@inertiajs/vue3";
import Pagination from "../../Shared/Pagination.vue";
import throttle from 'lodash/debounce'
import pickBy from 'lodash/pickBy'

export default {
    components: {Pagination, Head, Link},
    props: ['patients'],
    data: () => ({
        search: {
            query: '',
            status: null
        },
    }),
    methods: {
        doSearch() {
            this.$inertia.get('/patients/all', pickBy(this.search), {preserveState: true})
        }
    },
}
</script>
