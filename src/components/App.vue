
<template>
    <div>

        <Messages ref="msg"></Messages>

        <span v-if="$options.shareable" @click="share">Share</span>

        <Spinner v-show="saving"></Spinner>

        <PaymentIntent
            v-if="!token"
            v-model="token"
            :endpoint="endpointIntent"
            :amount.sync="amount"
            :currency="currency.toLowerCase()"
            :country="country"
            :symbol="symbol"
            @error="onError">
        </PaymentIntent>

        <div v-else>

            <div class="form-row mb-3">
                {{ symbol || currency }} {{ amount }}
            </div>

            <StripeCheckout
                v-show="!paid"
                :country="country"
                :currency="currency.toLowerCase()"
                :amount="amount"
                :client-secret="token"
                :endpoint="endpointSave"
                :email-required="emailRequired"
                :stripe-api-key="stripeApiKey"
                @payment="onPaid"
                @error="onError"
            ></StripeCheckout>

            <div v-if="paid">

                <span class="completed">{{ $t('completed', trans_result) }}</span>

<!--            <span v-if="result.email_sent">{{ $t('emailed', result) }}</span> -->

                <div v-if="gift_code">
                    {{ $t('gift_code', {gift_code}) }}

                    <div class="gift_code">
                        {{gift_code}}

                        <QrCode :content="gift_code"></QrCode>

                    </div>

                </div>
            
            </div>
        
        </div>

    </div>

</template>

<script>
    
    import StripeCheckout from './Stripe/Checkout';
    import PaymentIntent  from './PaymentIntent';
    import Messages       from './Messages'
    import Spinner        from './Spinner';
    import QrCode         from './QrCode';

    const CONFIG = typeof ajax_object !== 'undefined' ? ajax_object : {};

    const DEBUG = false;

    export default {

        name: 'Covid',
        components: {StripeCheckout, PaymentIntent, Messages, Spinner, QrCode},

        shareable: navigator.share,

        props: {
            endpointSave: {
                type    : String,
                required: false,
                default: CONFIG.endpoint_save
            },
            endpointIntent: {
                type    : String,
                required: false,
                default: CONFIG.endpoint_intent
            },
            defaultAmount: {
                type: [Number,String],
                default: CONFIG.default_amount,
            },
            /**
             * The seller's country code
             * @type {String}
             */
            country: {
                type: String,
                default: CONFIG.seller_country || 'US'
            },
            /**
             * The currency code
             * @type {String}
             */
            currency: {
                type: String,
                default: CONFIG.currency || 'USD'
            },
            /**
             * The company nbame
             * @type {String}
             */
            company: {
                type: String,
                default: CONFIG.company || 'this company'
            },
            /**
             * The currency symbol
             * @type {String}
             */
            symbol: {
                type: String,
                default: CONFIG.currency_symbol
            },

            stripeApiKey: {
                type: String,
                required: false,
                default: CONFIG.stripe_public_key
            },

            emailRequired: {
                type    : Boolean,
                required: false,
                default : CONFIG.email_required?true:false,
            },

        },

        data() {
            return {
                amount : this.defaultAmount,
                token  : null,
                saving : false,
                paid   : false,
                payload: null, // Dev only!
                result : null,
            };
        },
        computed: {
            gift_code() {
                return (this.result||{}).gift_code;
            },
            trans_result() {
                return {
                    amount: this.amount,
                    currency: this.currency,
                    symbol: this.symbol,
                    ... this.result||{}
                }
            }
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

                this.payload = payload;

                this.$api.post(this.endpointSave, payload)
                .then( data => {
                    this.result = data;
                })
                .catch( err => {
                    this.onError(err);
                })
                .finally( () => this.saving = false );
            },
            onError(err)
            {
                // console.warn('App.vue received err', err);

                let msg = this.$t('errors.whoops');
                if (typeof err === 'string') {
                    msg = this.$t(err);
                } else if ((err||{}).trans) {
                    msg = this.$t(err.trans, err.other || []);
                } else if ( typeof (err||{}).message === 'string') {
                    msg = this.$t(err.message);
                }

                msg && this.$refs.msg.showMessage('warning',msg)
            }
        },
        beforeDestroy () {

        }
    };
</script>
<style scoped>
    .gift_code {
        display    : block;
        margin     : 20px auto;
        text-align : center;
        border     : 2px dashed #0073aa;
        padding    : 20px;
        font-size  : 1.5em;
        font-weight: bold;
    }
</style>