
<template>
    <div>

        <span v-if="$options.shareable" @click="share">Share</span>

        <PaymentIntent
            v-if="!clientSecret"
            v-model="clientSecret"
            :endpoint="endpointIntent"
            :amount.sync="amount"
            :currency="currency"
            :country="country"
            :symbol="symbol"
            @error="onError">
        </PaymentIntent>

        <div v-else-if="!paid">

            <div class="form-row mb-3">
                {{ symbol || currency }} {{ amount }}
            </div>

            <StripeCheckout
                :country="country"
                :currency="currency"
                :amount="amount"
                :client-secret="clientSecret"
                :endpoint="endpointSave"
                :email-required="emailRequired"
                :stripe-api-key="stripeApiKey"
                @payment="onPaid"
                @error="onError"
            ></StripeCheckout>

        </div>

        <div v-else-if="saving">
            Loading...
        </div>

        <div v-else-if="paid">
            Done!
        </div>

        <Messages ref="msg"></Messages>

    </div>

</template>

<script>
    
    import StripeCheckout from './Stripe/Checkout';
    import PaymentIntent  from './PaymentIntent';
    import Messages       from './Messages'

    const WP = typeof ajax_object !== 'undefined' ? ajax_object : {};

    const DEBUG = false;

    export default {

        name: 'Covid',
        components: {StripeCheckout, PaymentIntent, Messages},

        shareable: navigator.share,

        props: {
            endpointSave: {
                type    : String,
                required: false,
                default: WP.endpoint_save
            },
            endpointIntent: {
                type    : String,
                required: false,
                default: WP.endpoint_intent
            },
            defaultAmount: {
                type: [Number,String],
                default: process.env.MIX_PAYMENT_DEFAULT
            },
            /**
             * The seller's country code
             * @type {String}
             */
            country: {
                type: String,
                default: 'US'
            },
            /**
             * The currency code
             * @type {String}
             */
            currency: {
                type: String,
                default: process.env.MIX_PAYMENT_CURRENCY || 'USD'
            },
            /**
             * The currency symbol
             * @type {String}
             */
            symbol: {
                type: String,
                default: process.env.MIX_CURRENCY_SYMBOL
            },

            stripeApiKey: {
                type: String,
                required: false,
                default: process.env.MIX_STRIPE_API_KEY || WP.stripe_public_key
            },

            emailRequired: {
                type    : Boolean,
                required: false,
                default : false,
            },

        },

        data() {
            return {
                amount      : this.defaultAmount,
                clientSecret: null,
                saving      : false,
                paid        : false,
                payload     : null, // Dev only!
            };
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
            retry()
            {
                this.onPaid(this.payload);
            },
            onPaid(payload)
            {
                this.paid = true;

                if (this.saving) {
                    return
                }
    
                // intent_id : paymentIntent.id,
                // payment_id: ev.paymentMethod.id,
                // method    : 'stripe-button',
                // name      : ev.payerName, 
                // email     : ev.payerEmail,
                // phone     : ev.payerPhone,
                // amount    : paymentIntent.amount,
                // currency  : paymentIntent.currency,
                // status    : paymentIntent.status

                this.$api.post(this.endpointSave, payload)
                .then( data => {

                    alert('done!');
                    console.log('Received data from back end.', data);

                })
                .catch( err => {
                    this.onError(err);
                    
                })
                .finally( () => this.saving = false );
            },
            onError(err)
            {
                console.warn('App.vue received err', err);

                let msg = this.$t('errors.whoops');
                if (typeof err === 'string') {
                    msg = this.$t(err);
                } else if ((err||{}).trans) {
                    msg = this.$t(err.trans, err.other || []);
                } else if ((err||{}).message) {
                    msg = this.$t(err.message);
                }

                msg && this.$refs.msg.showMessage('warning',msg)
            }
        },
        beforeDestroy () {

        }
    };
</script>