
<template>
    <div>
        <div class="alert" :class="`alert-${messageType}`" v-if="messageTran" role="alert">
          {{ messageTran }}
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
            };
        },
        methods: {
            showMessage(type, message)
            {
                this.messageType = type;
                this.messageTran = message;
                return false;
            }
        }
    };
</script>