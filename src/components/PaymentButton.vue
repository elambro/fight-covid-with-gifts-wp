
<template>
    <div>
        <div ref="button" v-if="!hidden"></div>
        <messages ref="msg"></messages>
    </div>
</template>

<script>

    import axios from 'axios';
    import Messages from './Messages'
    import LoadsStripe from './../mixins/loads-stripe';

    export default {

        name: 'PaymentButton',
        components: {Messages},
        mixins: [LoadsStripe],

        props: ['country', 'currency', 'amount', 'clientSecret'],

        data() {
            return {
                button: null,
                hidden: false
            };
        },

        computed: {

        },
        mounted() {

        },
        methods: {
            showButton()
            {
                return this.loadStripe()
                .then( stripe => this.getPaymentRequest(stripe)
                .then( ({stripe, paymentRequest}) => {
                    var elements = stripe.elements(this.$options.elementOptions);
                    this.button  = elements.create('paymentRequestButton', {paymentRequest});
                    // Check the availability of the Payment Request API first.
                    return paymentRequest.canMakePayment()
                })
                .then( result => {
                    if (result) {
                        this.button.mount(this.$refs.button);
                    } else {
                        this.hidden = true;
                    }
                })
                .catch( err => {
                    (console||{}).error && console.error(err);
                })
            },
            async getPaymentRequest(stripe)
            {
                return stripe.paymentRequest({
                    country: this.country,
                    currency: this.currency,
                    total: {
                        label: this.$t('product'),
                        amount: this.amount,
                    },
                    requestPayerName: true,
                    requestPayerEmail: true,
                })
                .then( paymentRequest => {
                    return {stripe, paymentRequest};
                })
            },


            showMessage(type, message)
            {
                return this.$refs.messages.showMessage(type,message)
            }
        },

        elementOptions : {},
    };
</script>