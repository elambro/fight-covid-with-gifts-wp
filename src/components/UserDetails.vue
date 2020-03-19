
<template>
    <div>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.name') }}</label>
            <div class="col-md-9">
                <input type="text" autocomplete="cc-name" name="u_name" :value="name" @input="e => emit(e,'name')" class="form-control" :placeholder="$t('form.placeholders.name')" />
            </div>
        </div>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.email') }}</label>
            <div class="col-md-9">
                <input type="email" autocomplete="email" name="u_email" :value="email" @input="e => emit(e,'email')" class="form-control" :required="emailRequired" :placeholder="$t('form.placeholders.email')" />
                <!-- <small class="form-text text-muted"></small> -->
            </div>
        </div>

        <div class="form-row mb-3">
            <label class="col-md-3">{{ $t('form.labels.phone') }}</label>
            <div class="col-md-9">
                <input type="tel" autocomplete="tel" name="u_phone" :value="phone" @input="e => emit(e,'phone')" class="form-control" :placeholder="$t('form.placeholders.phone')" />
                <!-- <small class="form-text text-muted"></small> -->
            </div>
        </div>

    </div>
</template>

<script>

    export default {

        name: 'UserDetails',

        props: {
            emailRequired: {
                type    : Boolean,
                required: false,
                default : false,
            },
            name: {
                type: String,
                required: false,
                default: ''
            },
            email: {
                type: String,
                required: false,
                default: ''
            },
            phone: {
                type: String,
                required: false,
                default: ''
            }
        },

        validation: {
            nameLength: 4,
        },

        methods: {
            validate()
            {
                if (!this.name || this.name.length < this.$options.validation.nameLength) {
                    return this.error($t('form.validation.name.required'))
                }
                if (this.emailRequired && !this.email) {
                    return this.error($t('form.validation.email.required'))
                }
                if (!this.email && !this.phone) {
                    return this.error($t('form.validation.phone_or_email.required'))
                }
                return true;
            },
            error(message)
            {
                this.$emit('error', message);
                return false;
            },
            emit(evt, key)
            {
                this.$emit(`update:${key}`, evt.target.value)
            }
        },
        beforeDestroy () {

        }
    };
</script>