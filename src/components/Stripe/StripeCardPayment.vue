
<template>
    <form ref="form" method="post" @submit.prevent="submit">

        <UserDetails
            ref="user"
            :email-required="emailRequired"
            :name.sync="userName"
            :email.sync="userEmail"
            :phone.sync="userPhone"
            @error="err => $emit('error', err)">
        </UserDetails>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.cc') }}</label>
            <div class="col-md-9">
                
                <div ref="card"></div>

            </div>
        </div>

        <div class="d-sm-flex mt-5">
            <p class="flex-shrink-0">
                <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="!canSubmit">
                    <span v-if="busy">{{ $t('form.buttons.busy') }}</span>
                    <span v-else>{{ $t('form.buttons.save') }}</span>
                </button>
            </p>
        </div>

    </form>
</template>

<script>

    import UserDetails from './../UserDetails'

    export default {

        name: 'StripeCardPayment',

        components: {UserDetails},

        props: {
            emailRequired: {
                type    : Boolean,
                required: false,
                default : false,
            },
            stripe: {
                type: Object,
                required: true,
            },
            clientSecret: {
                type: String,
                required: true
            }
        },

        validation: {
            nameLength: 4,
        },

        data() {
            return {
                card         : null,
                userName     : '',
                userEmail    : '',
                userPhone    : '',
                busy         : false,
                cardCompleted: false,
            };
        },

        computed: {
            canSubmit()
            {
                return !this.busy && this.cardCompleted && this.userName && (this.userEmail || this.userPhone) ? true : false;
            }
        },
        mounted()
        {
            this.createCard();
        },
        methods: {
            async submit()
            {
                if (this.busy || !this.cardCompleted || !this.$refs.user.validate()) {
                    return;
                }

                this.completePayment()
                .then( ({error, paymentIntent}) => {

                    if (error) {
                        throw error;
                    }

                    console.log('Payment result:', paymentIntent);
    
                    this.$emit('paid', {
                        payment_id: paymentIntent.payment_method,
                        intent_id : paymentIntent.id,
                        method    : 'stripe-card',
                        name      : this.userName,
                        email     : this.userEmail, 
                        phone     : this.userPhone,
                        amount    : paymentIntent.amount / 100,
                        currency  : paymentIntent.currency,
                        status    : paymentIntent.status
                    });
                })
                .catch(err => {
                    this.busy = false;
                    this.$emit('error', err);
                })
            },
            async completePayment()
            {
                return this.stripe.confirmCardPayment(this.clientSecret, {
                    payment_method: {card: this.card }
                });
            },
            createCard()
            {
                var elements = this.stripe.elements(this.$options.elementOptions);
                this.card = elements.create('card', this.$options.cardFormOptions);
                this.card.mount(this.$refs.card);
                this.card.on('change', e => {
                    this.cardCompleted = e.complete;
                    e.error && this.$emit('error', e.error);
                })
            }
        },
        beforeDestroy()
        {
            this.card.destroy();
        },

        elementOptions : {},
        cardFormOptions: {},
    };
</script>