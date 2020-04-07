
<template>
    <form ref="form" method="post" :action="endpoint" @submit.prevent="submit" class="covid-coupons-intent">

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('intent.labels.amount') }}</label>
            <div class="col-md-9 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">{{ symbol || currency }}</span>
                </div>
                <input type="number" :step="stepSize" name="u_amount" v-model="modAmount" class="form-control" :placeholder="amount" />
            </div>
        </div>

        <div class="d-sm-flex mt-5">
            <p class="flex-shrink-0">
                <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="!canSubmit">
                    <span v-if="saving">{{ $t('intent.buttons.saving') }}</span>
                    <span v-else>{{ $t('intent.buttons.save') }}</span>
                </button>
            </p>
        </div>

    </form>
</template>

<script>

    export default {

        name: 'IntentForm',

        props: {
            value: {
                // only for use with v-model
            },
            endpoint: {
                type    : String,
                required: true,
            },
            amount: {
                type: [Number,String],
                required: false,
                default: 10
            },
            integersOnly: {
                type: [Boolean,String],
                default: true
            },
            currency: {
                type: String,
                required: true
            },
            symbol: {
                type: String,
            }
        },

        data() {
            return {
                saving   : false,
                modAmount: this.amount,
                meta     : {}
            };
        },

        computed: {
            stepSize() {
                return this.integersOnly ? 1 : 0.01;
            },
            canSubmit() {
                return !this.saving && this.modAmount;
            }
        },
        mounted() {

        },
        methods: {
            validate()
            {
                if (!this.modAmount) {
                    this.$emit('error', $t('intent.validation.amount.required'))
                }
                return true;
            },
            async submit()
            {
                if (this.saving || !this.validate()) {
                    return;
                }
                this.saving = true;

                let data = {
                    u_amount  :  this.modAmount,
                    u_currency:  this.currency,
                    u_meta    :  this.meta
                }

                this.$api.post(this.endpoint, data)
                .then( ({token}) => {
                    this.$emit('update:amount', this.modAmount);
                    this.$emit('input', token)
                })                
                .catch( err => this.$emit('error', err))
                .finally( () => this.saving = false )
            }
        }
    };
</script>
<style>
.covid-coupons-intent .input-group {
    display: flex;
}

.covid-coupons-intent .input-group-text {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding: .375rem .75rem;
    margin-bottom: 0;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    text-align: center;
    white-space: nowrap;
    background-color: #e9ecef;
    border: 1px solid #ced4da;
}

.covid-coupons-intent .input-group-prepend {
    display: flex;
}
</style>