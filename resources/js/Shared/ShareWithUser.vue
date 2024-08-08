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
        }
    }
}
</script>

<template>
    <form-select-users
        v-model="form.user_id"
        @selected="onSelected"
    />
</template>

<style scoped>

</style>
