
<template>
    <div>
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