
<template>
    <div>
        <div v-if="svg" v-html="svg"></div>
    </div>
</template>

<script>

var QRCode = require("qrcode-svg");

    export default {

        name: 'QRCode',

        qr: {
            join     : true,
            container: 'svg-viewbox' // responsive
        },

        props: {
            content: {
                type    : String,
                required: true
            }
        },

        data() {
            return {
                svg: null
            };
        },
        watch: 
        {
            content: {
                handler(val) {
                    this.build();
                },
                immediate: true
            }
        },
        methods: {
            build()
            {
                this.svg = new QRCode(this.content, this.$options.qr).svg();
            }
        },
        beforeDestroy () {

        }
    };
</script>