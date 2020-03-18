<template>
    <div ref="card"></div>
</template>

<script>

import loadStripeApi from './../stripe-loader';

export default {
    name: 'InputCard',

    props: {
        stripeApiKey: {
            type    : String,
            required: true
        },
        currency: {
            type: String,
            default: 'USD'
        },
        name: {
            type: String
        }
    },
    data() {
        return {
            card: null
        }
    },
    computed: {
        meta() {
            return {
                name    : this.name,
                currency: this.currency
            }
        }
    },
    mounted() {
        this.loadCardInput();
    },
    methods: {
        async loadStripe()
        {
            return loadStripeApi(this.stripeApiKey, 3)
                .catch(err => Promise.reject( this.$t('errors.external') ))
        },
        async loadCardInput()
        {
            return this.loadStripe()
                .then( stripe => {
                    var elements = stripe.elements(this.$options.elementOptions);
                    this.card = elements.create('card', this.$options.cardFormOptions);
                    this.card.mount(this.$refs.card);
                    this.card.on('change', e => {
                        e.complete && this.$emit('complete');
                        e.error && this.$emit('error', e.error);
                    })
                })
                .catch( Err => this.$emit('error', Err));
        },
        async getCardToken()
        {
            return this.loadStripe()
            .then( stripe => stripe.createToken(this.card, this.meta))
            .then( result => {
                if (result.error) {
                    return Promise.reject(result.error);
                } else {
                    return Promise.resolve(result.token.id);
                }
            });
        },
    },

    beforeDestroy()
    {
        this.card.destroy();
    },

    elementOptions : {},
    cardFormOptions: {},
};
</script>