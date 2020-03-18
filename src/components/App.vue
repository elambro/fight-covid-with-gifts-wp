
<template>
    <div>
        <span v-if="$options.shareable" @click="share">Share</span>

        <IntentForm
            v-if="!clientSecret"
            v-model="clientSecret"
            :endpoint="endpointIntent"
            :amount.sync="amount"
            :currency="currency"
            :integers-only="integersOnly"
            :symbol="symbol"
            :nonce="addNonce">
        </IntentForm>

        <div v-else>

            <div class="form-row mb-3">
                {{ symbol || currency }} {{ amount }}
            </div>
            <CheckoutForm
                v-else
                :endpoint="endpoint">
            </CheckoutForm>

        </div>

    </div>

</template>

<script>
    
    import CheckoutForm from './CheckoutForm';
    import IntentForm from './IntentForm';
    import Nonce from './../mixins/has-nonce-field';

    const DEBUG = false;

    export default {

        name: 'Covid',
        components: {CheckoutForm, IntentForm},
        mixins: [Nonce],

        shareable: navigator.share,

        props: {
            endpoint: {
                type    : String,
                required: false,
            },
            defaultAmount: {
                type: [Number,String],
                default: process.env.MIX_PAYMENT_DEFAULT
            },
            integersOnly: {
                type: [Boolean,String],
                default: process.env.MIX_INTEGERS_ONLY || false
            },
            currency: {
                type: String,
                default: process.env.MIX_PAYMENT_CURRENCY || 'USD'
            },
            symbol: {
                type: String,
                default: process.env.MIX_CURRENCY_SYMBOL
            }
        },

        data() {
            return {
                amount      : this.defaultAmount,
                clientSecret: null,
            };
        },

        computed: {
            endpointIntent() {
                return this.$options.isWordpress ? this.$options.wp.endpoint_save : this.endpoint
            },
        },

        mounted() {

        },
        methods: {
            share()
            {
                navigator.share({
                    title: this.$t('share.title', [this.company]),
                    url  : window.location.href
                })
                .catch(e => {})
            },
        },
        beforeDestroy () {

        }
    };
</script>