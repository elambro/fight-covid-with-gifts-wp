
<template>
    <form v-else ref="form" method="post" :action="endpointSave" @submit.prevent="submit">

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.name') }}</label>
            <div class="col-md-9">
                <input type="text" autocomplete="cc-name" name="u_name" v-model="userName" class="form-control" :placeholder="$t('form.placeholders.name')" />
            </div>
        </div>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.email') }}</label>
            <div class="col-md-9">
                <input type="email" autocomplete="email" name="u_email" v-model="userEmail" class="form-control" :required="emailRequired" :placeholder="$t('form.placeholders.email')" />
                <!-- <small class="form-text text-muted"></small> -->
            </div>
        </div>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.phone') }}</label>
            <div class="col-md-9">
                <input type="tel" autocomplete="tel" name="u_phone" v-model="userPhone" class="form-control" :placeholder="phoneExample || $t('form.placeholders.phone')" />
                <!-- <small class="form-text text-muted"></small> -->
            </div>
        </div>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.cc') }}</label>
            <div class="col-md-9">
                <InputCard ref="card" 
                    :name="userName" 
                    :currency="currency" 
                    :stripe-api-key="stripeApiKey" 
                    @error="cardError"
                    :completed.sync="cardCompleted"
                ></InputCard>
            </div>
        </div>

        <messages ref="msg"></messages>

        <div class="d-sm-flex mt-5">
            <p class="flex-shrink-0">
                <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="!canSubmit">
                    <span v-if="saving">{{ $t('form.buttons.saving') }}</span>
                    <span v-else>{{ $t('form.buttons.save') }}</span>
                </button>
            </p>
        </div>
    </form>
</template>

<script>

    import axios         from 'axios';
    import loadStripeApi from './../stripe-loader'
    import Messages      from './Messages'
    import InputCard     from './InputCard'

    const DEBUG = false;

    export default {

        name: 'CheckoutForm',
        components: {InputCard, Messages},

        props: {
            endpoint: {
                type    : String,
                required: false,
            },
            emailRequired: {
                type    : Boolean,
                required: false,
                default : false,
            },
            stripeApiKey: {
                type: String,
                default: process.env.MIX_STRIPE_API_KEY
            },
            phoneExample: {
                type: String,
                default: process.env.MIX_TEL_STRUCTURE
            },
            nonce: {
                type: Function,
                required: true
            }
        },

        validation: {
            nameLength: 4,
        },

        data() {
            return {
                userName     : '',
                userEmail    : '',
                userPhone    : '',
                saving       : false,
                cardCompleted: false,
                amount       : this.defaultAmount
            };
        },

        computed: {
            stepSize() {
                return this.integersOnly ? 1 : 0.01;
            },
            endpointSave() {
                return this.$options.isWordpress ? this.$options.wp.endpoint_save : this.endpoint
            },
            canSubmit() {
                return !this.saving && this.cardCompleted && this.userName && (this.userEmail || this.userPhone);
            }
        },
        mounted() {

        },
        methods: {
            validate()
            {
                if (!this.userName || this.userName.length < this.$options.validation.nameLength) {
                    return this.showMessage('warning', $t('form.validation.name.required'))
                }
                if (this.emailRequired && !this.userEmail) {
                    return this.showMessage('warning', $t('form.validation.email.required'))
                }
                if (!this.userEmail && !this.userPhone) {
                    return this.showMessage('warning', $t('form.validation.phone_or_email.required'))
                }
                return true;
            },
            async submit()
            {
                if (this.saving || !this.validate()) {
                    return;
                }

                this.saving = true;

                let token = await this.getToken();
                if (!token) {
                    return;
                }

                axios.post( this.endpointSave ,this.getFormData(token))
                .then( response => {

                    console.log('Response:', response.data);

                })                
                .catch( error => {
                    let res = ((((error||{}).response)||{}).data||{}).data;
                    let msg = res || error.message || $t('errors.whoops');
                    this.showMessage('danger', msg);
                })
                .finally( () => this.saving = false )
            },
            showMessage(type, message)
            {
                this.messageType = type;
                this.messageTran = message;
                return false;
            },
            async getToken()
            {
                return this.$refs.card.getCardToken()
                    .catch( err => this.showMessage(err.message) );
            },
            getFormData(token)
            {
                let data = new FormData;
                // data.append('action', 'myAction');
                data.append('u_name', this.userName);
                data.append('u_email', this.userEmail);
                data.append('u_phone', this.userPhone);
                data.append('u_token', token);
                data.append('u_amount', this.amount);

                return this.nonce(data);
            },
            cardError(err)
            {
                let m = typeof err === 'string' ? err : (err||{}).message;
                m && this.showMessage('warning', m)
            }
        },
        beforeDestroy () {

        }
    };
</script>
<style>
    textarea.form-control {
        color: #232323;
        border: 1px solid #777;
    }
    label .required { color: red; }
    label.section-label { font-weight: bold; }
</style>