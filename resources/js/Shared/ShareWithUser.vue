<script>
import FormSelectUsers from "./Form/FormSelectUsers.vue";
import Form from 'vform'

export default {
    name: "ShareWithUser",
    components: {FormSelectUsers},
    props: ['sharedToId', 'patientId'],
    data() {
        return {
            form: new Form({
                user_id: this.sharedToId || null
            }),
        }
    },
    methods: {
        onSelected({id}) {
            try {
                this.form.post(route('patients.share', {patient: this.patientId, user: id}));
            } catch (e) {
                alert('Невозможно поделиться пациентом.');
            }
        },
        clear() {
            try {
                this.form.post(route('patients.share.clear', {patient: this.patientId}));
            } catch (e) {
                alert('Невозможно открепить.');
            }
        }
    }
}
</script>

<template>
    <div class="row">
        <div class="col-10">
            <form-select-users
                v-model="form.user_id"
                @selected="onSelected"
            />
        </div>

        <div class="col-2" v-if="sharedToId">
            <button type="button" @click="clear" class="btn btn-danger">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>
