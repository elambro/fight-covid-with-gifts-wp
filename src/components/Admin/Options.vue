
<template>
    <div>

        <Messages ref="msg"></Messages>

        <div style="position:absolute">
            <Spinner v-show="saving"></Spinner>
        </div>

        <form ref="form" method="post" :action="endpointSave" @submit.prevent="submit">

            <div class="d-sm-flex mt-5">
                <p class="flex-shrink-0">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="saving">
                        <span v-if="saving">{{ $t('admin.buttons.saving') }}</span>
                        <span v-else>{{ $t('admin.buttons.save') }}</span>
                    </button>
                </p>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.seller_company_name') }}</label>
                <div class="col-md-9">
                    <input type="text" name="seller_company_name" v-model="seller_company_name" class="form-control" />
                    <small class="form-text text-muted">{{ $t('admin.help.seller_company_name') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.seller_email') }}</label>
                <div class="col-md-9">
                    <input type="email" name="seller_email" v-model="settings.seller_email" class="form-control" />
                    <small class="form-text text-muted">{{ $t('admin.help.seller_email') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.seller_country') }}</label>
                <div class="col-md-9">
                    <input type="text" name="seller_country" v-model="settings.seller_country" class="form-control" placeholder="US" />
                    <small class="form-text text-muted">{{ $t('admin.help.seller_country') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.locale') }}</label>
                <div class="col-md-9">
                    <input type="text" name="locale" v-model="settings.locale" size="2" class="form-control" placeholder="en" />
                    <small class="form-text text-muted">{{ $t('admin.help.locale') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.currency') }}</label>
                <div class="col-md-9">
                    <input type="text" name="currency" v-model="settings.currency" class="form-control" size="3" placeholder="USD" />
                    <small class="form-text text-muted">{{ $t('admin.help.currency') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.currency_symbol') }}</label>
                <div class="col-md-9">
                    <input type="text" name="currency_symbol" v-model="settings.currency_symbol" class="form-control" maxlength="3" placeholder="$" />
                    <small class="form-text text-muted">{{ $t('admin.help.currency_symbol') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.default_gift_amount') }}</label>
                <div class="col-md-9">
                    <input type="number" step=1 name="default_gift_amount" v-model="settings.default_gift_amount" class="form-control" placeholder="50" />
                    <small class="form-text text-muted">{{ $t('admin.help.default_gift_amount') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.min_payment') }}</label>
                <div class="col-md-9">
                    <input type="number" step=1 name="min_payment" v-model="settings.min_payment" class="form-control" placeholder="1" />
                    <small class="form-text text-muted">{{ $t('admin.help.min_payment') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.max_payment') }}</label>
                <div class="col-md-9">
                    <input type="number" step=1 name="max_payment" v-model="settings.max_payment" class="form-control" placeholder="1000" />
                    <small class="form-text text-muted">{{ $t('admin.help.max_payment') }}</small>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.email_required') }}</label>
                <div class="col-md-9 form-check">
                    <input type="checkbox" name="email_required" v-model="settings.email_required" id="email-required" class="form-check-input" style="margin-top: 2px">
                    <label class="form-check-label" for="email-required">
                        <small class="form-text text-muted" style="padding-left: 30px;">
                            {{ $t('admin.help.email_required') }}
                        </small>
                    </label>
                </div>
            </div>

            <div class="form-row mb-3">
                <label class="col-md-3">{{ $t('admin.labels.gateway_name') }}</label>
                <div class="col-md-9">
                    <select name="gateway_name" v-model="settings.gateway_name" class="form-control">
                        <option value="stripe">Stripe</option>
                    </select>
                        <!-- <label class="form-check-label" for="exampleCheck1">Check me out</label> -->
                    <small class="form-text text-muted">{{ $t('admin.help.gateway_name') }}</small>
                </div>
            </div>

            <template v-if="settings.gateway_name === 'stripe'">
                <div class="form-row mb-3">
                    <label class="col-md-3">{{ $t('admin.labels.stripe_public') }}</label>
                    <div class="col-md-9">
                        <input type="text" name="stripe_public" v-model="settings.stripe_public" class="form-control" placeholder="pk_test_..." />
                        <small class="form-text text-muted">{{ $t('admin.help.stripe_public') }}</small>
                    </div>
                </div>

                <div class="form-row mb-3">
                    <label class="col-md-3">{{ $t('admin.labels.stripe_secret') }}</label>
                    <div class="col-md-9">
                        <input type="text" name="stripe_secret" v-model="settings.stripe_secret" class="form-control" placeholder="sk_test_..." />
                        <small class="form-text text-muted">{{ $t('admin.help.stripe_secret') }}</small>
                    </div>
                </div>
            </template>

            <template v-if="settings.gateway_name === 'liqpay'">
                <div class="form-row mb-3">
                    <label class="col-md-3">{{ $t('admin.labels.liqpay_public') }}</label>
                    <div class="col-md-9">
                        <input type="text" name="liqpay_public" v-model="settings.liqpay_public" class="form-control" required />
                        <small class="form-text text-muted">{{ $t('admin.help.liqpay_public') }}</small>
                    </div>
                </div>

                <div class="form-row mb-3">
                    <label class="col-md-3">{{ $t('admin.labels.liqpay_secret') }}</label>
                    <div class="col-md-9">
                        <input type="text" name="liqpay_secret" v-model="settings.liqpay_secret" class="form-control" required />
                        <small class="form-text text-muted">{{ $t('admin.help.liqpay_secret') }}</small>
                    </div>
                </div>
            </template>

            <div class="d-sm-flex mt-5">
                <p class="flex-shrink-0">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="saving">
                        <span v-if="saving">{{ $t('admin.buttons.saving') }}</span>
                        <span v-else>{{ $t('admin.buttons.save') }}</span>
                    </button>
                </p>
            </div>

        </form>

    </div>

</template>

<script>
    
    import Messages       from './../Messages'
    import Spinner        from './../Spinner';

    const CONFIG = typeof ajax_object !== 'undefined' ? ajax_object : {};

    export default {

        name: 'AdminOptions',
        components: {Messages, Spinner},

        props: {
            endpointSave: {
                type    : String,
                required: false,
                default: CONFIG.save_options
            },
            endpointFetch: {
                type    : String,
                required: false,
                default: CONFIG.fetch_options
            },
        },

        mounted() {
            this.fetch()
        },

        computed: {
            seller_company_name: {
                get() { return this.settings.seller_company_name;},
                set(v) { this.settings.seller_company_name = v; }
            }
        },

        data() {
            return {
                settings: {},
                saving: false
            };
        },

        methods: {
            submit()
            {
                return this.save();
            },
            fetch() 
            {
                this.saving = true;
                this.$api.get(this.endpointFetch)
                .then(data => this.setSettings(data))
                .catch(err => this.onError(err))
                .finally(() => this.saving = false)
            },
            save()
            {
                this.saving = true;
                this.$api.post(this.endpointSave, this.settings)
                .then(data => {
                    this.setSettings(data.options)
                    this.$refs.msg.showMessage('success',this.$t('admin.saved'));
                })
                .catch(err => this.onError(err))
                .finally(() => this.saving = false);
            },
            setSettings(data)
            {
                Object.keys(data).forEach(k => {
                    let v = data[k];
                    if ('false' === v) {
                        data[k] = false;
                    } else if ('0' === v) {
                        data[k] = 0;
                    } else if ('null' === v) {
                        data[k] = null;
                    }
                });
                this.settings = data;
            },
            onError(err)
            {
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