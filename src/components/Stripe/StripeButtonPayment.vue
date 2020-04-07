<template>
    <div :style="style">
        <div ref="button"></div>
    </div>
</template>

<script>

    const DEBUG = false;

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
            },
            description: {
                type: String,
                required: false,
                default: 'Covid Coupon'
            },
        },
        data() {
            return {
                paymentRequest: null,
                canMakePayment: null,
                button        : null,
                hidden        : null,
            };
        },
        mounted()
        {
            this.createPaymentRequest()
            .then( () => this.createButton() )
            .then( mounted => {
                if (mounted) {
                    this.paymentRequest.on('paymentmethod', async (ev) => this.handlePaymentEvent(ev) );
                }
            })
            .catch( err => {
                DEBUG && (console||{}).warn && console.warn('Failed to mount payment button.', err);
            })
        },
        computed: {
            style() {
                return this.canMakePayment ? {margin: '20px 0'} : null;
            }
        },
        methods: {
            
            async createPaymentRequest()
            {
                this.paymentRequest = await this.stripe.paymentRequest({
                    country : this.country,
                    currency: this.currency.toLowerCase(),
                    total: {
                        label: this.$t('product'),
                        amount: this.amount * 100,
                    },
                    requestPayerName : true,
                    requestPayerEmail: true,
                    requestPayerPhone: true,
                })
                return;
            },
            async handlePaymentEvent(ev)
            {
                // FYI: const {shippingOption, shippingAddress, payerEmail, payerName, methodName, paymentMethod} = ev
                // See https://stripe.com/docs/api/payment_methods/object
                // Confirm the PaymentIntent without handling potential next actions (yet).
                DEBUG && (console||{}).log && console.log('Button Payment event:', ev);

                // Confirm the PaymentIntent without handling potential next actions (yet).
                // https://stripe.com/docs/js/payment_intents/confirm_card_payment
                return this.stripe.confirmCardPayment(this.clientSecret,
                    {payment_method: ev.paymentMethod.id},
                    {handleActions: false}
                )
                .then( confirmResult1 => this.handleFirstConfirmation(ev, confirmResult1))
                .then( confirmResult2 => this.handleSecondConfirmation(confirmResult2))
                .then( paymentIntent => this.paymentIsSuccessful(ev, paymentIntent))
                .catch( err => {
                    DEBUG && (console||{}).warn && console.warn('Payment event failed', err);
                })
            },
            async handleFirstConfirmation(ev, {error, paymentIntent})
            {
                // See https://stripe.com/docs/api/payment_intents
                DEBUG && (console||{}).log && console.log('Button confirm result:', {error, paymentIntent});

                if (error) {
                    paymentIntent = this.isAlreadyPaidError(error);
                    if (paymentIntent) {
                        // The payment already went through successfully
                        ev.complete('success');
                        return {error:null, paymentIntent};
                    }

                    // Report to the browser that the payment failed, prompting it to
                    // re-show the payment interface, or show an error message and close
                    // the payment interface.
                    ev.complete('fail');
                    throw false;
                }
                // Report to the browser that the confirmation was successful, prompting
                // it to close the browser payment method collection interface.
                ev.complete('success');

                // Let Stripe.js handle the rest of the payment flow.
                // https://github.com/stripe/stripe-payments-demo/issues/101
                // This may return a 400 error because it's already confirmed
                // That's okay. Just continue on. We need to do it twice in case
                // the first step required further action. Stripe would have
                // prompted the user to complete the bank confirmation on the screen.
                return this.stripe.confirmCardPayment(this.clientSecret);
            },
            handleSecondConfirmation({error, paymentIntent})
            {
                // See https://stripe.com/docs/api/payment_intents
                DEBUG && (console||{}).log && console.log('Button confirm 2', {error, paymentIntent});

                if (error) {
                    paymentIntent = this.isAlreadyPaidError(error);
                    if (paymentIntent) {
                        return paymentIntent;
                    }
                    throw error;
                }

                return paymentIntent;
            },
            paymentIsSuccessful(ev, paymentIntent)
            {
                // The payment has succeeded
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
            },
            isAlreadyPaidError(error)
            {
                const paymentIntent = error.payment_intent;

                if (error.code === 'payment_intent_unexpected_state' 
                    && paymentIntent.status === 'succeeded') {
                    return paymentIntent;
                }

                return false;
            },
            async createButton()
            {
                const elements = this.stripe.elements(this.$options.elementOptions);
                this.button    = elements.create('paymentRequestButton', {paymentRequest: this.paymentRequest});

                return this.paymentRequest.canMakePayment()
                .then(result => {
                    this.canMakePayment = result;
                    try { 
                        this.button.mount(this.$refs.button);
                    } catch ( err ) {
                        DEBUG && (console||{}).warn && console.warn(err);
                    }
                    return true;
                });
            }
        },
        beforeDestroy()
        {
            this.button.destroy();
            delete this.paymentRequest;
            delete this.stripe;
            delete this.canMakePayment;
            delete this.button;
        },
        elementOptions: {
            // https://stripe.com/docs/stripe-js/elements/payment-request-button#html-js-styling-the-element
        }
    };
</script>