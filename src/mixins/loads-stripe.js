
 // <input v-if="$options.isWordpress" type="hidden" :name="nonceField" :value="nonceData" />

import loadStripeApi from './../stripe-loader';

export default {

    props: {
        stripeApiKey: {
            type    : String,
            required: true
        },
    },

    methods: {
        async loadStripe()
        {
            let k = ( this.$options.isWordpress ? this.$options.wp.stripe_key : this.stripeApiKey ) || this.stripeApiKey;
            if (!k) {
                return Promise.reject(this.$t('errors.missing-key'));
            }
            return loadStripeApi(k, 3)
                .catch(err => Promise.reject( this.$t('errors.external') ))
        },
    }
}