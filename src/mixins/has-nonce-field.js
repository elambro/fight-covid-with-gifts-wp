
 // <input v-if="$options.isWordpress" type="hidden" :name="nonceField" :value="nonceData" />

export default {

    isWordpress: typeof ajax_object !== 'undefined',

    wp: typeof ajax_object !== 'undefined' ? ajax_object : {},

    computed: {
        nonceData() {
            return this.$options.wp.nonce_data;
        },
        nonceField() {
            return this.$options.wp.nonce_field;
        },
        hasNonce() {
            return this.$options.isWordpress
        }
    },
    methods: {
        addNonce(form)
        {
            form.append(this.nonceField, this.nonceData);
            return form;
        }
    }
}