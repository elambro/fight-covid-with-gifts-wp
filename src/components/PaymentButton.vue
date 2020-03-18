
<template>
    <div>
        <div ref="button" v-if="!hidden"></div>
    </div>
</template>

<script>

    import axios from 'axios';
    import LoadsStripe from './../mixins/loads-stripe';

    export default {

        name: 'PaymentButton',
        mixins: [LoadsStripe],

        props: ['country', 'currency', 'amount', 'clientSecret'],

        data() {
            return {
                button : null,
                request: null,
                hidden : false,
            };
        },
        mounted() {
            this.showButton();
        },
        methods: {
            showButton()
            {
                return this.loadStripe()
                .then( stripe => this.getPaymentRequest(stripe) )
                .then( stripe => {
                    var elements = stripe.elements(this.$options.elementOptions);
                    this.button  = elements.create('paymentRequestButton', {paymentRequest: this.request});
                    // Check the availability of the Payment Request API first.
                    if (this.request.canMakePayment()) {
                        this.mountButton();
                        return stripe;
                    }
                    return this.hideButton()
                })
                .then( stripe => stripe && this.addPaymentEvent(stripe) )
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
                    requestPayerPhone: true,
                })
                .then( paymentRequest => {
                    this.request = paymentRequest;
                    return stripe;
                })
            },
            addPaymentEvent(stripe)
            {
                this.request.on('paymentmethod', async (ev) => {
                    // ev -- https://stripe.com/docs/js/appendix/payment_response
                    // Confirm the PaymentIntent without handling potential next actions (yet).
                    const {error: confirmError} = await stripe.confirmCardPayment(
                        this.clientSecret,
                        {payment_method: ev.paymentMethod.id},
                        {handleActions: false}
                    );

                    if (confirmError) {
                        ev.complete('fail');
                    } else {
                        ev.complete('success');
                        const {error, paymentIntent} = await stripe.confirmCardPayment(this.clientSecret);
                        if (error) {
                            this.$emit('error', error);
                        } else {
                            this.$emit('success', ev.paymentMethod.id, ev.payerName, ev.payerEmail, ev.payerPhone)
                        }
                    }
                });
            },
            mountButton()
            {
                this.button.mount(this.$refs.button);
                return true;
            },
            hideButton()
            {
                this.hidden = true;
                return false;
            }
        },

        elementOptions : {},
    };
</script>