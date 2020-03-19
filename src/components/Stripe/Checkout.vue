
<template>
    <div>
        <StripeButtonPayment 
            v-if="stripe"
            :stripe="stripe"
            :country="country"
            :currency="currency"
            :amount="amount"
            :client-secret="clientSecret"
            @paid="onPaid"
            @error="onError">
        </StripeButtonPayment>

        <StripeCardPayment
            v-if="stripe"
            :stripe="stripe"
            :email-required="emailRequired"
            :client-secret="clientSecret"
            @paid="onPaid"
            @error="onError">
        </StripeCardPayment>
    </div>

</template>

<script>
    
    import loadStripeApi       from './stripe-loader';
    import StripeCardPayment   from './StripeCardPayment';
    import StripeButtonPayment from './StripeButtonPayment';

    export default {

        name: 'StripeCheckout',
        components: {StripeCardPayment, StripeButtonPayment},

        props: {
            emailRequired: {
                type    : Boolean,
                required: false,
                default : false,
            },
            stripeApiKey: {
                type: String,
                required: true,
            },
            amount: {
                type: [Number,String],
                required: true
            },
            currency: {
                type: String,
                required: true
            },
            country: {
                type: String,
                required: true
            },
            clientSecret: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                stripe      : null,
            };
        },
        mounted()
        {
            this.loadStripe()
                .then(stripe => {
                    this.stripe = stripe;
                });
        },
        methods: {
            onPaid(payload)
            {
                this.$emit('payment', payload);
            },
            onError(err)
            {
                this.$emit('error', err);
            },
            async loadStripe()
            {
                if (!this.stripeApiKey) {
                    return Promise.reject(this.$t('errors.missing-key'));
                }
                return loadStripeApi(this.stripeApiKey, 3)
                    .catch(err => Promise.reject( this.$t('errors.external') ))
            },
        },
        beforeDestroy () {
            delete this.stripe;
        }
    };
</script>