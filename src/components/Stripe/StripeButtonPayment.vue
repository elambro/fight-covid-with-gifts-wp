<template>
    <div>
        <div ref="button"></div>
    </div>
</template>

<script>

    export default {

        name: 'StripeButtonPayment',

        props: {
            stripe: {
                type: Object,
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
                paymentRequest: null,
            };
        },
        mounted()
        {
            this.createPaymentRequest();
            this.createButton();
            this.paymentRequest.on('paymentmethod', async (ev) => this.handlePaymentEvent(ev) );
        },
        methods: {
            
            async createPaymentRequest()
            {
                this.paymentRequest = this.stripe.paymentRequest({
                    country : this.country,
                    currency: this.currency.toLowerCase(),
                    total: {
                        label: this.$t('product'),
                        amount: this.amount * 100,
                    },
                    requestPayerName: true,
                    requestPayerEmail: true,
                    requestPayerPhone: true,
                })
            },
            async handlePaymentEvent(ev)
            {
                // Confirm the PaymentIntent without handling potential next actions (yet).
                const {error: confirmError} = await this.stripe.confirmCardPayment(
                    this.clientSecret,
                    {payment_method: ev.paymentMethod.id},
                    {handleActions: false}
                );

                if (confirmError) {
                    ev.complete('fail');
                } else {
                    ev.complete('success');
                    const {error, paymentIntent} = await this.stripe.confirmCardPayment(this.clientSecret);
                    if (error) {
                        this.$emit('error', error);
                    } else {
                        // ev -- https://stripe.com/docs/js/appendix/payment_response
                        this.$emit('paid', {
                            intent_id : paymentIntent.id,
                            payment_id: ev.paymentMethod.id,
                            method    : 'stripe-button',
                            name      : ev.payerName, 
                            email     : ev.payerEmail,
                            phone     : ev.payerPhone,
                            amount    : paymentIntent.amount / 100,
                            currency  : paymentIntent.currency,
                            status    : paymentIntent.status
                        })
                    }
                }
            },
            async createButton()
            {
                const elements = this.stripe.elements(this.$options.elementOptions);
                const button = elements.create('paymentRequestButton', {paymentRequest: this.paymentRequest});
                
                if (this.paymentRequest.canMakePayment()) {
                    // createButton
                    try {
                        button.mount(this.$refs.button);
                        return true;    
                    } catch (err) {
                        // silent
                    }
                }

                this.hidden = true;
                return false;
            }
        }
    };
</script>