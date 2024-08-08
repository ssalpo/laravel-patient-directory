<template>
    <FormSelect
        ref="selectUsers"
        v-bind="$attrs"
        searchable
        prefetch
        remote
        :remote-url="route('autocomplete.users')"
        full
        placeholder="Выберите пользователя"
    />
</template>

<script>
import FormSelect from "./FormSelect.vue";

export default {
    name: "FormSelectUsers",
    components: {FormSelect},
    methods: {
        refreshData() {
            this.$refs.selectUsers.refreshData()
        }
    }
}
</script>
