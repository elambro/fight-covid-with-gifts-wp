
<template>
    <div>
        <span v-if="$options.shareable" @click="share">Share</span>

        <IntentForm
            v-if="!clientSecret"
            v-model="clientSecret"
            :endpoint="compEndpointIntent"
            :amount.sync="amount"
            :currency="currency"
            :integers-only="integersOnly"
            :symbol="symbol"
            :nonce="addNonce"
            @error="onError">
        </IntentForm>

        <div v-else>

            <div class="form-row mb-3">
                {{ symbol || currency }} {{ amount }}
            </div>

            <PaymentButton 
                :country="userCountry"
                :currency="currency"
                :amount="amount"
                :client-secret="clientSecret"
                @success="onPaid"
                @error="onError">
            </PaymentButton>

            <CheckoutForm
                :endpoint="compEndpointSave">
            </CheckoutForm>

        </div>

        <Messages ref="msg"></Messages>

    </div>

</template>

<script>
    
    import CheckoutForm  from './CheckoutForm';
    import IntentForm    from './IntentForm';
    import PaymentButton from './PaymentButton';
    import Messages      from './Messages'
    import Nonce         from './../mixins/has-nonce-field';

    const DEBUG = false;

    export default {

        name: 'Covid',
        components: {CheckoutForm, IntentForm, PaymentButton, Messages},
        mixins: [Nonce],

        shareable: navigator.share,

        props: {
            endpointSave: {
                type    : String,
                required: false,
            },
            endpointIntent: {
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
                clientSecret: null
            };
        },

        computed: {
            compEndpointIntent() {
                return (this.$options.isWordpress ? this.$options.wp.endpoint_intent : this.endpointIntent) || this.endpointIntent
            },
            compEndpointSave() {
                return (this.$options.isWordpress ? this.$options.wp.endpoint_save : this.endpointSave) || this.endpointSave
            }
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
            onPaid(payment_id, name, email, phone)
            {
                console.log('paid', payment_id, name, email, phone);
                alert('paid');
                // ... @todo
            },
            onError(err)
            {
                let msg = this.$t('errors.whoops');
                if (typeof err === 'string') {
                    msg = err;
                } else if ((err||{}).response) {
                    let data = err.response.data.data;
                    console.warn('Response data:', data);
                    if (data.trans) {
                        msg = this.$t(data.trans, [data.other]);
                    }
                } else if ((err||{}).message) {
                    msg = err.message;
                }

                msg && this.showMessage('warning', msg);
            },
            showMessage(type, message)
            {
                return this.$refs.msg.showMessage(type,message)
            },
        },
        beforeDestroy () {

        }
    };
</script>