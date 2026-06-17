<template>
    <Head>
        <title>Пациенты: {{doctor.name}}</title>
    </Head>

    <div class="content-header">
        <div class="container">
            <h1 class="m-0">Пациенты: {{doctor.name}}</h1>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-9 col-sm-3 mb-2 mb-sm-0">
                            <input type="text"
                                   v-model="search.query"
                                   @keydown.enter="doSearch"
                                   class="form-control form-control-sm"
                                   placeholder="Ф.И.О, код"/>
                        </div>
                        <div class="col-3 col-sm-3">
                            <button class="btn btn-sm btn-primary mr-1" type="button" @click="doSearch">
                                <span class="fa fa-search"></span>
                            </button>
                            <button v-if="isFiltered" type="button" class="btn btn-sm btn-danger" @click="reset">
                                <span class="fa fa-times"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Номер кейса</th>
                                <th>Ф.И.О</th>
                                <th>Диагноз</th>
                                <th>Статус</th>
                                <th v-if="$page.props.shared.userPermissions.includes('read_doctors')"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(patient, index) in patients.data">
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
                                <td v-if="$page.props.shared.userPermissions.includes('read_doctors')"
                                    class="text-center">
                                    <Link :href="route('patients.edit', patient.id)">
                                        <i class="fa fa-pencil-alt"></i>
                                    </Link>
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
import size from "lodash/size";
import pickBy from 'lodash/pickBy'

export default {
    components: {Pagination, Head, Link},
    props: ['filterParams', 'patients', 'doctor'],
    data() {
        return {
            search: {
                query: this.filterParams?.query || null
            }
        }
    },
    computed: {
        isFiltered() {
            return size(this.filterParams);
        }
    },
    methods: {
        doSearch() {
            this.$inertia.get(route('doctors.patients', {doctor: this.doctor.id}), pickBy(this.search), {preserveState: true})
        },
        reset() {
            this.$inertia.visit(route('doctors.patients', {doctor: this.doctor.id}));
        }
    },
}
</script>
