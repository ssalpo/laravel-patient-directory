<template>
    <Head>
        <title>{{user?.id ? 'Обновление данных пользователя' : 'Новый пользователя'}}</title>
    </Head>

    <div class="content-header">
        <div class="container">
            <h1 class="m-0">{{ user?.id ? 'Обновление данных пользователя' : 'Новый пользователя' }}</h1>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="card card-primary">
                <!-- form start -->
                <form @submit.prevent="submit">
                    <div class="card-body">

                        <form-input
                            label="Имя"
                            required
                            v-model.trim="form.name"
                            :validation-error="errors.name"
                        />

                        <form-input
                            label="Имя на странице печати"
                            v-model.trim="form.print_name"
                            :validation-error="errors.print_name"
                        />

                        <form-input
                            label="Логин для входа"
                            required
                            v-model.trim="form.username"
                            :validation-error="errors.username"
                        />

                        <div class="form-group">
                            <form-select-roles
                                ref="selectRoles"
                                v-model="form.role"
                                :invalid-text="errors.role"
                                label="Роль"
                            />

                            <new-role-modal @success="setRole"/>
                        </div>

                        <div class="form-group">
                            <form-select-doctors
                                v-model="form.doctor_id"
                                :invalid-text="errors.doctor_id"
                                label="Связать пользователя со списком врачей"
                            />
                        </div>

                        <form-input
                            label="Пароль"
                            :required="!user?.id"
                            v-model.trim="form.password"
                            :validation-error="errors.password"
                        />
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        <form-save-button
                            :is-processing="form.processing"
                            :is-editing="user?.id"
                        />

                        <form-cancel-button
                            :url="route('users.index')"
                            :is-processing="form.processing"
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import {Head, Link, useForm} from "@inertiajs/vue3";
import FormInput from "../../Shared/Form/FormInput.vue";
import FormSelectRoles from "../../Shared/Form/FormSelectRoles.vue";
import NewRoleModal from "../../Shared/Modals/NewRoleModal.vue";
import FormSaveButton from "../../Shared/Form/FormSaveButton.vue";
import FormCancelButton from "../../Shared/Form/FormCancelButton.vue";
import FormSelectDoctors from "../../Shared/Form/FormSelectDoctors.vue";

export default {
    props: ['user', 'errors'],
    components: {
        FormSelectDoctors,
        FormCancelButton, FormSaveButton, NewRoleModal, FormSelectRoles, FormInput, Head, Link},
    data() {
        return {
            form: useForm({
                name: this.user?.name,
                print_name: this.user?.print_name,
                username: this.user?.username,
                doctor_id: this.user?.doctor_id,
                password: null,
                role: this.user?.roles[0].name || 'doctor'
            }),
        }
    },
    methods: {
        submit() {
            if (!this.user?.id) {
                this.form.post('/users');
                return;
            }

            this.form.put(`/users/${this.user.id}`)
        },
        setRole(role) {
            this.$refs.selectRoles.refreshData()

            this.form.role = role.name

            this.form.clearErrors()
        }
    }
}
</script>
