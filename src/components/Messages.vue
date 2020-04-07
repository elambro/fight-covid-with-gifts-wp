
<template>
    <div class="covid-coupons-messages">
        <div class="alert  alert-dismissible fade show" :class="`alert-${messageType}`" v-show="messageTran" role="alert">
          <span v-html="messageTran"></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="hide">
              <span aria-hidden="true">&times;</span>
          </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Messages',
        data() {
            return {
                messageTran  : null,
                messageType  : null,
                timeout: null
            };
        },
        methods: {
            hide()
            {
                this.messageTran = null;
                this.timeout && clearTimeout(this.timeout);
            },
            showMessage(type, message, time = 7)
            {
                this.timeout && clearTimeout(this.timeout);
                this.messageType = type;
                this.messageTran = message;
                this.timeout = setTimeout(()=>this.hide(), time*1000);
                return false;
            }
        }
    };
</script>
<style>
    .covid-coupons-messages .alert {
        position: relative;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: .25rem;
    }

    .covid-coupons-messages .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .covid-coupons-messages .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .covid-coupons-messages .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeeba;
    }

    .covid-coupons-messages .alert-link {
        font-weight: 700;
        color: #0b2e13;
    }

    .covid-coupons-messages .close:not(:disabled):not(.disabled) {
        cursor: pointer;
    }


    .covid-coupons-messages .alert-dismissible .close {
        position: absolute;
        top: 0;
        right: 0;
        padding: .75rem 1.25rem;
        color: inherit;
        padding: 0;
        background-color: transparent;
        border: 0;
        -webkit-appearance: none;
        float: right;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 1px 0 #fff;
        opacity: .5;
    }

</style>