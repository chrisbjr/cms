<template>

    <div>
    <stack name="inline-editor"
        :before-close="shouldClose"
        @closed="close"
    >
    <div class="h-full overflow-auto p-3 bg-grey-30">

        <div v-if="loading" class="absolute pin z-200 flex items-center justify-center text-center">
            <loading-graphic />
        </div>

        <component
            :is="component"
            v-if="!loading"
            v-bind="componentPropValues"
            :method="method"
            :is-creating="creating"
            :publish-container="publishContainer"
            @saved="saved"
        >
            <template slot="action-buttons-right">
                <slot name="action-buttons-right" />
                <button
                    type="button"
                    class="btn-close"
                    @click="confirmClose"
                    v-html="'&times'" />
            </template>
        </component>

    </div>
    </stack>
    </div>

</template>

<script>
export default {

    props: {
        component: String,
        componentProps: Object,
    },

    data() {
        return {
            loading: true,
            readOnly: false,
            componentPropValues: {},
        }
    },

    computed: {

        publishContainer() {
            return `relate-fieldtype-inline-${this._uid}`;
        }

    },

    created() {
        this.getItem();
    },

    methods: {

        getItem() {
            this.$axios.get(this.itemUrl).then(response => {
                for (const prop in this.componentProps) {
                    const value = data_get(response.data, this.componentProps[prop]);
                    this.$set(this.componentPropValues, prop, value);
                }

                this.loading = false;
            });
        },

        close() {
            this.$emit('closed');
        },

        confirmClose() {
            if (this.shouldClose()) this.close();
        },

        shouldClose() {
            if (this.$dirty.has(this.publishContainer)) {
                if (! confirm('Are you sure? Unsaved changes will be lost.')) {
                    return false;
                }
            }

            return true;
        }
    }

}
</script>
